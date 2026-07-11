<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Food;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class QAChefKDSAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $chefUser;
    protected User $waiterUser;
    protected Food $food;
    protected Table $table;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles & Permissions
        $chefRole = Role::firstOrCreate(['name' => 'Chef']);
        $waiterRole = Role::firstOrCreate(['name' => 'Waiter']);

        $viewKitchenPanel = Permission::firstOrCreate(['name' => 'view kitchen panel']);
        $viewWaiterPanel = Permission::firstOrCreate(['name' => 'view waiter panel']);

        $chefRole->givePermissionTo($viewKitchenPanel);
        $waiterRole->givePermissionTo($viewWaiterPanel);

        // Users
        $this->chefUser = User::create([
            'name' => 'Chef Asilbek',
            'login' => 'chef_qa',
            'password' => bcrypt('chef123'),
            'phone' => '+998901111141',
            'status' => 'active',
        ]);
        $this->chefUser->assignRole($chefRole);

        $this->waiterUser = User::create([
            'name' => 'Waiter Davron',
            'login' => 'waiter_qa',
            'password' => bcrypt('waiter123'),
            'phone' => '+998901111142',
            'status' => 'active',
        ]);
        $this->waiterUser->assignRole($waiterRole);

        // Seed Data
        $category = Category::create(['name' => 'Hot Dishes']);
        $this->food = Food::create([
            'name' => 'Palov',
            'price' => 45000.00,
            'category_id' => $category->id,
            'is_available' => true
        ]);

        $this->table = Table::create([
            'table_number' => 'KDS Stol 1',
            'capacity' => 4,
            'status' => 'empty',
            'qr_code_token' => 'kds_token_1',
        ]);
    }

    /**
     * Test 1.1: Out-of-Order State Transitions
     * Attempt to bypass normal workflow from pending directly to ready, skipping cooking.
     */
    public function test_1_1_out_of_order_state_transitions(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-KDS-100',
            'table_id' => $this->table->id,
            'waiter_id' => $this->waiterUser->id,
            'total_amount' => 45000.00,
            'status' => 'new'
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'food_id' => $this->food->id,
            'quantity' => 1,
            'price' => 45000.00,
            'status' => 'pending'
        ]);

        // Attempting to bypass cooking and set status directly to ready
        $response = $this->actingAs($this->chefUser, 'sanctum')
            ->patchJson("/api/chef/items/{$item->id}/status", [
                'status' => 'ready'
            ]);

        // Expected result: state transition validation fails (returns 400 Bad Request or similar error code)
        $this->assertTrue(
            in_array($response->getStatusCode(), [400, 422]),
            "Transition bypass allowed! Order item status moved directly from pending to ready. Status: " . $response->getStatusCode()
        );
    }

    /**
     * Test 1.2: Timer Precision under Load
     * Create 50 concurrent active orders and fetch KDS active items list
     */
    public function test_1_2_timer_precision_under_load(): void
    {
        for ($i = 1; $i <= 50; $i++) {
            $order = Order::create([
                'order_number' => 'ORD-LOAD-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'table_id' => $this->table->id,
                'waiter_id' => $this->waiterUser->id,
                'total_amount' => 45000.00,
                'status' => 'new'
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'food_id' => $this->food->id,
                'quantity' => 1,
                'price' => 45000.00,
                'status' => 'pending'
            ]);
        }

        $response = $this->actingAs($this->chefUser, 'sanctum')
            ->getJson('/api/chef/items');

        $response->assertStatus(200);
        $this->assertCount(50, $response->json(), "KDS failed to retrieve all 50 active items under load!");
    }

    /**
     * Test 2.1: Stop-List Propagation Speed
     */
    public function test_2_1_stop_list_propagation_speed(): void
    {
        // Toggle food availability to false (stop-listed)
        $responseToggle = $this->actingAs($this->chefUser, 'sanctum')
            ->postJson("/api/kitchen/foods/{$this->food->id}/toggle", [
                'is_available' => false
            ]);

        $responseToggle->assertStatus(200);

        // Verify cache keys menu_categories is cleared
        $this->assertFalse(Cache::has('menu_categories'), "Cache key 'menu_categories' was not cleared upon stop-listing!");

        // Assert that new order submissions for this food fail
        $responseOrder = $this->actingAs($this->waiterUser, 'sanctum')
            ->postJson('/api/orders', [
                'table_id' => $this->table->id,
                'items' => [
                    ['food_id' => $this->food->id, 'quantity' => 1]
                ]
            ]);

        // Expected result: rejected since food is not available (422)
        $responseOrder->assertStatus(422);
    }

    /**
     * Test 2.2: Active Order Stop-List Conflict
     * Stop-list food item while there are active pending orders.
     */
    public function test_2_2_active_order_stop_list_conflict(): void
    {
        // 1. Create a pending order with Palov
        $order = Order::create([
            'order_number' => 'ORD-KDS-300',
            'table_id' => $this->table->id,
            'waiter_id' => $this->waiterUser->id,
            'total_amount' => 45000.00,
            'status' => 'new'
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'food_id' => $this->food->id,
            'quantity' => 1,
            'price' => 45000.00,
            'status' => 'pending'
        ]);

        // 2. Put item on Stop-List
        $this->actingAs($this->chefUser, 'sanctum')
            ->postJson("/api/kitchen/foods/{$this->food->id}/toggle", [
                'is_available' => false
            ]);

        // 3. Chef must still be able to cook and complete the pending item
        $responseStatus = $this->actingAs($this->chefUser, 'sanctum')
            ->patchJson("/api/chef/items/{$item->id}/status", [
                'status' => 'cooking'
            ]);

        // Note: As found in Test 1.1, if state machine is not enforced, direct transition to ready might bypass.
        // We assert status update works for preparation-eligible active order items.
        $responseStatus->assertStatus(200);

        // 4. Any new orders for this item must be blocked
        $responseNewOrder = $this->actingAs($this->waiterUser, 'sanctum')
            ->postJson('/api/orders', [
                'table_id' => $this->table->id,
                'items' => [
                    ['food_id' => $this->food->id, 'quantity' => 1]
                ]
            ]);

        $responseNewOrder->assertStatus(422);
    }
}
