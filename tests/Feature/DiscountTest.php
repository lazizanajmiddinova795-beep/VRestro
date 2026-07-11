<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\Food;
use App\Models\Category;
use App\Models\Discount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DiscountTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $waiterUser;
    protected Order $order;
    protected Discount $discountPercentage;
    protected Discount $discountFixed;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Roles and Permissions
        $adminRole = Role::create(['name' => 'Admin']);
        $manageDiscounts = Permission::create(['name' => 'manage discounts']);
        $adminRole->givePermissionTo($manageDiscounts);

        // Setup Admin User
        $this->adminUser = User::create([
            'name' => 'Test Admin',
            'login' => 'testadmin',
            'password' => bcrypt('password123'),
            'phone' => '+998901111111',
            'status' => 'active',
        ]);
        $this->adminUser->assignRole($adminRole);

        // Setup Regular Waiter
        $this->waiterUser = User::create([
            'name' => 'Test Waiter',
            'login' => 'testwaiter',
            'password' => bcrypt('password123'),
            'phone' => '+998901111112',
            'status' => 'active',
        ]);

        // Setup table & order
        $table = Table::create([
            'table_number' => 'Stol 1',
            'capacity' => 4,
            'status' => 'occupied',
            'qr_code_token' => 'token_1',
        ]);

        $this->order = Order::create([
            'order_number' => 'ORD-TEST-001',
            'table_id' => $table->id,
            'waiter_id' => $this->waiterUser->id,
            'total_amount' => 100000.00,
            'status' => 'new',
        ]);

        $category = Category::create(['name' => 'Taomlar']);
        $food = Food::create([
            'name' => 'Osh',
            'price' => 50000.00,
            'category_id' => $category->id,
            'is_available' => true,
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'food_id' => $food->id,
            'quantity' => 2,
            'price' => 50000.00,
        ]);

        // Create sample discounts
        $this->discountPercentage = Discount::create([
            'name' => 'Yangi yil 10%',
            'type' => 'percentage',
            'value' => 10.00,
            'code' => 'YANGIYIL10',
            'min_order_amount' => 50000.00,
            'is_active' => true,
        ]);

        $this->discountFixed = Discount::create([
            'name' => 'Omadli kun 20000',
            'type' => 'fixed',
            'value' => 20000.00,
            'code' => 'LUCKY20',
            'min_order_amount' => 80000.00,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_discount(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/discounts', [
                'name' => 'Bahoriy chegirma',
                'type' => 'percentage',
                'value' => 15,
                'code' => 'BAHOR15',
                'min_order_amount' => 60000,
                'starts_at' => now()->toDateTimeString(),
                'expires_at' => now()->addDays(5)->toDateTimeString(),
                'is_active' => true
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('discounts', ['code' => 'BAHOR15']);
    }

    public function test_non_admin_cannot_create_discount(): void
    {
        $response = $this->actingAs($this->waiterUser, 'sanctum')
            ->postJson('/api/discounts', [
                'name' => 'Bahoriy chegirma',
                'type' => 'percentage',
                'value' => 15,
                'code' => 'BAHOR15',
            ]);

        $response->assertStatus(403);
    }

    public function test_can_toggle_discount_status(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->patchJson("/api/discounts/{$this->discountPercentage->id}/toggle");

        $response->assertStatus(200);
        $this->assertDatabaseHas('discounts', [
            'id' => $this->discountPercentage->id,
            'is_active' => false
        ]);
    }

    public function test_can_apply_percentage_promocode(): void
    {
        $response = $this->actingAs($this->waiterUser, 'sanctum')
            ->postJson('/api/discounts/validate-code', [
                'order_id' => $this->order->id,
                'code' => 'YANGIYIL10',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('order.discount_amount', '10000.00'); // 10% of 100,000 UZS
        $response->assertJsonPath('order.total_amount', '90000.00'); // 100k - 10k
    }

    public function test_can_apply_fixed_promocode(): void
    {
        $response = $this->actingAs($this->waiterUser, 'sanctum')
            ->postJson('/api/discounts/validate-code', [
                'order_id' => $this->order->id,
                'code' => 'LUCKY20',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('order.discount_amount', '20000.00'); // Fixed 20,000 UZS
        $response->assertJsonPath('order.total_amount', '80000.00'); // 100k - 20k
    }

    public function test_cannot_apply_promocode_below_minimum_amount(): void
    {
        // Set higher min amount
        $this->discountPercentage->update(['min_order_amount' => 150000.00]);

        $response = $this->actingAs($this->waiterUser, 'sanctum')
            ->postJson('/api/discounts/validate-code', [
                'order_id' => $this->order->id,
                'code' => 'YANGIYIL10',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('code');
    }

    public function test_cannot_apply_expired_promocode(): void
    {
        $this->discountPercentage->update([
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->waiterUser, 'sanctum')
            ->postJson('/api/discounts/validate-code', [
                'order_id' => $this->order->id,
                'code' => 'YANGIYIL10',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('code');
    }
}
