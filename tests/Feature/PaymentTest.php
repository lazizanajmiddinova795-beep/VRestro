<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\Table;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashierUser;
    protected Order $order;
    protected Table $table;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Roles and Permissions
        $cashierRole = Role::create(['name' => 'Cashier']);
        $managePayments = Permission::create(['name' => 'manage payments']);
        $cashierRole->givePermissionTo($managePayments);

        // Setup Cashier User
        $this->cashierUser = User::create([
            'name' => 'Test Cashier',
            'login' => 'testcashier',
            'password' => bcrypt('password123'),
            'phone' => '+998901111111',
            'status' => 'active',
        ]);
        $this->cashierUser->assignRole($cashierRole);

        // Setup Table
        $this->table = Table::create([
            'table_number' => 'Stol 5',
            'capacity' => 4,
            'status' => 'occupied',
            'qr_code_token' => 'test_token',
        ]);

        // Setup Order
        $this->order = Order::create([
            'order_number' => 'ORD-TEST-001',
            'table_id' => $this->table->id,
            'waiter_id' => $this->cashierUser->id,
            'total_amount' => 100000.00,
            'status' => 'ready',
        ]);

        // Setup Customer
        $this->customer = Customer::create([
            'name' => 'Loyal Client',
            'phone' => '+998902222222',
            'bonus_balance' => 20000.00,
            'total_orders_count' => 5,
            'total_spent_amount' => 500000.00,
        ]);
    }

    /**
     * Test processing a cash payment.
     */
    public function test_can_process_cash_payment(): void
    {
        $response = $this->actingAs($this->cashierUser, 'sanctum')
            ->postJson('/api/payments', [
                'order_id' => $this->order->id,
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('payment.payment_method', 'cash');
        $response->assertJsonPath('payment.total_amount', '100000.00');

        // Check if Order was delivered
        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'delivered',
        ]);

        // Check if Table is empty
        $this->assertDatabaseHas('tables', [
            'id' => $this->table->id,
            'status' => 'empty',
        ]);
    }

    /**
     * Test payment processing with loyalty integration.
     */
    public function test_can_process_payment_with_loyalty(): void
    {
        // Pay 100,000 UZS, using 15,000 UZS loyalty bonus points
        $response = $this->actingAs($this->cashierUser, 'sanctum')
            ->postJson('/api/payments', [
                'order_id' => $this->order->id,
                'customer_id' => $this->customer->id,
                'payment_method' => 'cash',
                'bonus_used' => 15000.00,
            ]);

        $response->assertStatus(201);

        // Client paid (100,000 - 15,000) = 85,000 UZS
        // 5% cashback on 85,000 is 4,250 UZS
        // Final bonus balance = 20,000 (initial) - 15,000 (used) + 4,250 (earned) = 9,250 UZS
        $this->assertDatabaseHas('customers', [
            'id' => $this->customer->id,
            'bonus_balance' => '9250.00',
            'total_orders_count' => 6,
            'total_spent_amount' => '585000.00',
        ]);
    }

    /**
     * Test refunding a completed payment.
     */
    public function test_can_refund_payment(): void
    {
        // 1. Process payment first
        $payment = Payment::create([
            'order_id' => $this->order->id,
            'customer_id' => $this->customer->id,
            'total_amount' => 100000.00,
            'payment_method' => 'cash',
            'cash_amount' => 85000.00,
            'bonus_used' => 15000.00,
            'status' => 'completed',
        ]);

        // Adjust customer balance to match the processed payment
        $this->customer->update([
            'bonus_balance' => 9250.00,
            'total_orders_count' => 6,
            'total_spent_amount' => 585000.00,
        ]);

        // 2. Perform refund request
        $response = $this->actingAs($this->cashierUser, 'sanctum')
            ->postJson("/api/payments/{$payment->id}/refund");

        $response->assertStatus(200);
        $response->assertJsonPath('payment.status', 'refunded');

        // Check if Order was reset to ready
        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'ready',
        ]);

        // Check if Customer balance was rolled back
        // 9,250 - 4,250 (refunded cashback) + 15,000 (restored bonuses) = 20,000 UZS
        $this->assertDatabaseHas('customers', [
            'id' => $this->customer->id,
            'bonus_balance' => '20000.00',
            'total_orders_count' => 5,
            'total_spent_amount' => '500000.00',
        ]);
    }
}
