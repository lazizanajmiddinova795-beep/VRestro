<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Food;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class QAWaiterPWAUnitTest extends TestCase
{
    use RefreshDatabase;

    protected User $waiterUser;
    protected User $cashierUser;
    protected Food $food;
    protected Table $table;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles & Permissions
        $waiterRole = Role::firstOrCreate(['name' => 'Waiter']);
        $cashierRole = Role::firstOrCreate(['name' => 'Cashier']);

        $viewWaiterPanel = Permission::firstOrCreate(['name' => 'view waiter panel']);
        $viewCashierDashboard = Permission::firstOrCreate(['name' => 'view cashier dashboard']);
        $managePayments = Permission::firstOrCreate(['name' => 'manage payments']);

        $waiterRole->givePermissionTo($viewWaiterPanel);
        $cashierRole->givePermissionTo([$viewCashierDashboard, $managePayments]);

        // Users
        $this->waiterUser = User::create([
            'name' => 'Waiter Davron',
            'login' => 'waiter_qa',
            'password' => bcrypt('waiter123'),
            'phone' => '+998901111151',
            'status' => 'active',
        ]);
        $this->waiterUser->assignRole($waiterRole);

        $this->cashierUser = User::create([
            'name' => 'Cashier Lobar',
            'login' => 'cashier_qa',
            'password' => bcrypt('cashier123'),
            'phone' => '+998901111152',
            'status' => 'active',
        ]);
        $this->cashierUser->assignRole($cashierRole);

        // Seed Data
        $category = Category::create(['name' => 'Hot Dishes']);
        $this->food = Food::create([
            'name' => 'Palov',
            'price' => 45000.00,
            'category_id' => $category->id,
            'is_available' => true
        ]);

        $this->table = Table::create([
            'table_number' => 'PWA Stol 1',
            'capacity' => 4,
            'status' => 'empty',
            'qr_code_token' => 'pwa_token_1',
        ]);

        // Mock setting for service charge and waiter bonus
        Setting::create(['key' => 'service_charge_rate', 'value' => '10', 'type' => 'number']);
        Setting::create(['key' => 'waiter_bonus_percentage', 'value' => '5', 'type' => 'number']);
    }

    /**
     * Test 1.2: Stop-List Validation at Dispatch
     * Waiter submits order containing a stop-listed item.
     */
    public function test_1_2_stop_list_validation_at_dispatch(): void
    {
        // Stop-list the food item
        $this->food->update(['is_available' => false]);

        $payload = [
            'table_id' => $this->table->id,
            'items' => [
                [
                    'food_id' => $this->food->id,
                    'quantity' => 2,
                    'price' => 45000.00
                ]
            ]
        ];

        $response = $this->actingAs($this->waiterUser, 'sanctum')
            ->postJson('/api/waiter/orders/submit', $payload);

        // Expected result: 422 with Uzbek error message
        $response->assertStatus(422);
        $this->assertStringContainsString('Ushbu taom oshxonada tugagan!', $response->json('message'));
    }

    /**
     * Test 2.1: Order Item Level Cancellation Rules
     * Waiter attempts to cancel a cooking or ready item directly via API.
     */
    public function test_2_1_order_item_level_cancellation_rules(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-PWA-100',
            'table_id' => $this->table->id,
            'waiter_id' => $this->waiterUser->id,
            'total_amount' => 45000.00,
            'status' => 'cooking'
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'food_id' => $this->food->id,
            'quantity' => 1,
            'price' => 45000.00,
            'status' => 'cooking' // Cooking status
        ]);

        // Attempt deletion via cancelItem API endpoint
        $response = $this->actingAs($this->waiterUser, 'sanctum')
            ->deleteJson("/api/waiter/order-item/{$item->id}");

        // Expected result: 403 Forbidden
        $response->assertStatus(403);
    }

    /**
     * Test 3.1: KPI Balance Recalculation
     * Verify closing a bill assigned to this waiter recalculates metrics instantly.
     */
    public function test_3_1_kpi_balance_recalculation(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-PWA-200',
            'table_id' => $this->table->id,
            'waiter_id' => $this->waiterUser->id,
            'total_amount' => 1000000.00, // 1,000,000 UZS bill
            'status' => 'ready'
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'food_id' => $this->food->id,
            'quantity' => 20,
            'price' => 50000.00
        ]);

        // Cashier processes payment and closes/delivers the order
        $paymentPayload = [
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'cash_amount' => 1000000.00,
            'card_amount' => 0.00,
            'qr_amount' => 0.00,
            'bonus_used' => 0.00
        ];

        $responsePayment = $this->actingAs($this->cashierUser, 'sanctum')
            ->postJson('/api/payments', $paymentPayload);

        $responsePayment->assertStatus(201);

        // Instantly get daily stats for the waiter
        $responseStats = $this->actingAs($this->waiterUser, 'sanctum')
            ->getJson('/api/waiter/profile/daily-stats');

        $responseStats->assertStatus(200);

        // Daily total sales must update to 1,000,000 UZS
        $this->assertEquals(1000000.00, (float)$responseStats->json('total_sales_amount'));

        // Daily total orders count must update to 1
        $this->assertEquals(1, $responseStats->json('total_orders_count'));

        // Bonus must update to 5% of 1,000,000 = 50,000 UZS
        $this->assertEquals(50000.00, (float)$responseStats->json('earned_bonus'));
    }
}
