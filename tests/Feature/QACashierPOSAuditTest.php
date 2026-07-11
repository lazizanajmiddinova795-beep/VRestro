<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Table;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Food;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QACashierPOSAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashierUser;
    protected User $waiterUser;
    protected Table $table;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Roles and Permissions
        $cashierRole = Role::firstOrCreate(['name' => 'Cashier']);
        $waiterRole = Role::firstOrCreate(['name' => 'Waiter']);

        $viewCashierDashboard = Permission::firstOrCreate(['name' => 'view cashier dashboard']);
        $managePayments = Permission::firstOrCreate(['name' => 'manage payments']);
        $viewWaiterPanel = Permission::firstOrCreate(['name' => 'view waiter panel']);

        $cashierRole->givePermissionTo([$viewCashierDashboard, $managePayments]);
        $waiterRole->givePermissionTo($viewWaiterPanel);

        // Setup Users
        $this->cashierUser = User::create([
            'name' => 'Kassir Lobar',
            'login' => 'cashier_qa',
            'password' => bcrypt('cashier123'),
            'phone' => '+998901111131',
            'status' => 'active',
        ]);
        $this->cashierUser->assignRole($cashierRole);

        $this->waiterUser = User::create([
            'name' => 'Waiter Davron',
            'login' => 'waiter_qa',
            'password' => bcrypt('waiter123'),
            'phone' => '+998901111132',
            'status' => 'active',
        ]);
        $this->waiterUser->assignRole($waiterRole);

        // Setup Table
        $this->table = Table::create([
            'table_number' => 'POS Stol 1',
            'capacity' => 4,
            'status' => 'empty',
            'qr_code_token' => 'pos_token_1',
        ]);
    }

    /**
     * Test 1.1: Multi-Operator Concurrency
     * Simulate two operators trying to open a new checkout bill on the exact same empty Table simultaneously.
     */
    public function test_1_1_multi_operator_concurrency(): void
    {
        // Operator A opens order on table
        $responseA = $this->actingAs($this->waiterUser, 'sanctum')
            ->postJson('/api/orders', [
                'table_id' => $this->table->id,
                'items' => [
                    ['food_id' => 1, 'quantity' => 1] // mock item structure
                ]
            ]);

        // Operator B tries to open order on the same table simultaneously
        $responseB = $this->actingAs($this->waiterUser, 'sanctum')
            ->postJson('/api/orders', [
                'table_id' => $this->table->id,
                'items' => [
                    ['food_id' => 1, 'quantity' => 1]
                ]
            ]);

        // Expected result: Second request should be rejected if the system validates occupied table status or applies database lock.
        // Let's assert status code of the second request to verify protection.
        $this->assertTrue(
            in_array($responseB->getStatusCode(), [422, 403, 400]),
            "Concurrency bypass! Duplicate orders allowed on the same table. Status: " . $responseB->getStatusCode()
        );
    }

    /**
     * Test 2.1: Mathematical Precision on Multi-Payment
     * Process checkout of 500,000 UZS. Apply 10% discount (-50,000 UZS).
     * Split remaining 450,000 UZS: 250,000 Cash, 200,000 Terminal.
     */
    public function test_2_1_multi_payment_precision(): void
    {
        $category = Category::create(['name' => 'Dishes']);
        $food = Food::create([
            'name' => 'Osh Large',
            'price' => 250000.00,
            'category_id' => $category->id,
            'is_available' => true
        ]);

        $order = Order::create([
            'order_number' => 'ORD-POS-100',
            'table_id' => $this->table->id,
            'waiter_id' => $this->waiterUser->id,
            'total_amount' => 500000.00, // 2 Osh items
            'status' => 'ready'
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'food_id' => $food->id,
            'quantity' => 2,
            'price' => 250000.00
        ]);

        $discount = Discount::create([
            'name' => 'CRM 10%',
            'type' => 'percentage',
            'value' => 10.00,
            'code' => 'CRM10',
            'min_order_amount' => 10000.00,
            'is_active' => true
        ]);

        // Apply discount code to order
        $this->actingAs($this->cashierUser, 'sanctum')
            ->postJson('/api/discounts/validate-code', [
                'order_id' => $order->id,
                'code' => 'CRM10'
            ]);

        $order->refresh();
        $this->assertEquals(450000.00, (float)$order->total_amount, "Loyalty discount math incorrect!");

        // Process payment with split amounts
        $paymentPayload = [
            'order_id' => $order->id,
            'payment_method' => 'mixed',
            'cash_amount' => 250000.00,
            'card_amount' => 200000.00,
            'qr_amount' => 0.00,
            'bonus_used' => 0.00
        ];

        $responsePayment = $this->actingAs($this->cashierUser, 'sanctum')
            ->postJson('/api/payments', $paymentPayload);

        $responsePayment->assertStatus(201);

        // Check if unpaid/short payments are rejected
        $shortPayload = [
            'order_id' => $order->id,
            'payment_method' => 'mixed',
            'cash_amount' => 250000.00,
            'card_amount' => 199999.00, // 1 UZS short
            'qr_amount' => 0.00,
            'bonus_used' => 0.00
        ];

        // We need to create another order to test short payment rejection as previous order is closed
        $order2 = Order::create([
            'order_number' => 'ORD-POS-101',
            'table_id' => $this->table->id,
            'waiter_id' => $this->waiterUser->id,
            'total_amount' => 450000.00,
            'status' => 'ready'
        ]);

        $responseShort = $this->actingAs($this->cashierUser, 'sanctum')
            ->postJson('/api/payments', [
                'order_id' => $order2->id,
                'payment_method' => 'mixed',
                'cash_amount' => 250000.00,
                'card_amount' => 199999.00, // 1 UZS short
                'qr_amount' => 0.00,
                'bonus_used' => 0.00
            ]);

        $responseShort->assertStatus(422); // Validation error
    }

    /**
     * Test 2.2: Void/Cancellation Reason Enforcement
     */
    public function test_2_2_void_cancellation_reason_enforcement(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-POS-200',
            'table_id' => $this->table->id,
            'waiter_id' => $this->waiterUser->id,
            'total_amount' => 100000.00,
            'status' => 'ready' // fully cooked order
        ]);

        // Attempt to cancel without cancellation_reason
        $response = $this->actingAs($this->cashierUser, 'sanctum')
            ->postJson("/api/orders/{$order->id}/cancel", []);

        // Assert 422 Unprocessable Entity
        $this->assertEquals(422, $response->getStatusCode(), "Fully cooked order cancelled without cancellation reason validation!");
    }

    /**
     * Test 3.1: Closing Unpaid Shift Restrictions
     */
    public function test_3_1_close_unpaid_shift_restrictions(): void
    {
        // Set table to occupied
        $this->table->update(['status' => 'occupied']);

        Order::create([
            'order_number' => 'ORD-POS-300',
            'table_id' => $this->table->id,
            'waiter_id' => $this->waiterUser->id,
            'total_amount' => 150000.00,
            'status' => 'ready' // unpaid active order
        ]);

        // Check if there are active tables or unpaid orders
        $response = $this->actingAs($this->cashierUser, 'sanctum')
            ->postJson('/api/shift/close');

        // System must prevent close shift and return 400 Bad Request
        $response->assertStatus(400);
        $this->assertStringContainsString("Smenani yopib bo'lmaydi", $response->json('message'));
    }
}
