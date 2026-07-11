<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Food;
use App\Models\Ingredient;
use App\Models\Table;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Discount;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionItem;
use App\Services\WarehouseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QAAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $waiterUser;
    protected User $cashierUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Roles
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $waiterRole = Role::firstOrCreate(['name' => 'Waiter']);
        $cashierRole = Role::firstOrCreate(['name' => 'Cashier']);

        // 2. Create Permissions
        $viewWaiterPanel = Permission::firstOrCreate(['name' => 'view waiter panel']);
        $viewCashierDashboard = Permission::firstOrCreate(['name' => 'view cashier dashboard']);
        $manageDiscounts = Permission::firstOrCreate(['name' => 'manage discounts']);

        $adminRole->givePermissionTo([$viewWaiterPanel, $viewCashierDashboard, $manageDiscounts]);
        $waiterRole->givePermissionTo($viewWaiterPanel);
        $cashierRole->givePermissionTo([$viewCashierDashboard, $manageDiscounts]);

        // 3. Create Users
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'login' => 'admin_qa',
            'password' => bcrypt('admin123'),
            'phone' => '+998901111111',
            'status' => 'active',
        ]);
        $this->adminUser->assignRole($adminRole);

        $this->waiterUser = User::create([
            'name' => 'Waiter User',
            'login' => 'waiter_qa',
            'password' => bcrypt('waiter123'),
            'phone' => '+998901111112',
            'status' => 'active',
        ]);
        $this->waiterUser->assignRole($waiterRole);

        $this->cashierUser = User::create([
            'name' => 'Cashier User',
            'login' => 'cashier_qa',
            'password' => bcrypt('cashier123'),
            'phone' => '+998901111113',
            'status' => 'active',
        ]);
        $this->cashierUser->assignRole($cashierRole);
    }

    /**
     * Test 1.1: Spatie RBAC Isolation
     * Attempt to bypass the admin dashboard login using valid credentials of a Waiter or Cashier.
     */
    public function test_1_1_spatie_rbac_isolation(): void
    {
        // 1. Waiter Attempt to access Admin Dashboard
        $responseWaiter = $this->actingAs($this->waiterUser, 'sanctum')
            ->getJson('/api/admin/dashboard');

        // 2. Cashier Attempt to access Admin Dashboard
        $responseCashier = $this->actingAs($this->cashierUser, 'sanctum')
            ->getJson('/api/admin/dashboard');

        // Asserting 403 Forbidden as expected
        $this->assertEquals(403, $responseWaiter->getStatusCode(), "Waiter was able to access the admin dashboard!");
        $this->assertEquals(403, $responseCashier->getStatusCode(), "Cashier was able to access the admin dashboard!");
    }

    /**
     * Test 1.2: Validation Injection
     * Submit login and user creation forms with XSS or SQL injection.
     */
    public function test_1_2_validation_injection(): void
    {
        // 1. Login form injection attempt
        $loginPayload = [
            'login' => "' OR '1'='1",
            'password' => '<script>alert("XSS")</script>'
        ];

        $responseLogin = $this->postJson('/api/auth/login', $loginPayload);
        // Assert that validation catches it or authentication fails with 422/401/403 or credentials error
        $this->assertTrue(
            in_array($responseLogin->getStatusCode(), [422, 401, 302]),
            "Login injection bypass! Status code: " . $responseLogin->getStatusCode()
        );

        // 2. Staff creation injection attempt
        $staffPayload = [
            'name' => 'Hacker <script>alert("xss")</script>',
            'phone' => '+998901111119',
            'login' => "hacker' OR 1=1;--",
            'password' => 'secret123',
            'role' => 'Waiter',
            'shift_hours' => '09:00-18:00',
            'status' => 'active'
        ];

        $responseStaff = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/staff', $staffPayload);

        // The request validator should fail if rules are strict, or sanitize it.
        // Let's assert database stores sanitized or rejected data.
        $user = User::where('login', $staffPayload['login'])->first();
        $this->assertNull($user, "SQL Injection payload succeeded in creating a user with raw sql characters in login name!");
    }

    /**
     * Test 2.1: Staff & Shift Logs Module CRUD
     */
    public function test_2_1_staff_crud_operations(): void
    {
        // 1. Create Staff
        $payload = [
            'name' => 'Dummy Employee',
            'phone' => '+998909998877',
            'login' => 'dummy_emp',
            'password' => 'pass1234',
            'role' => 'Waiter',
            'shift_hours' => '10:00-22:00',
            'status' => 'active'
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/staff', $payload);

        $response->assertStatus(201);
        $userId = $response->json('user.id');
        $this->assertDatabaseHas('users', ['login' => 'dummy_emp']);

        // 2. Update parameters
        $updatePayload = array_merge($payload, [
            'name' => 'Updated Dummy Employee',
            'phone' => '+998909998878' // changing phone
        ]);

        $responseUpdate = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson("/api/staff/{$userId}", $updatePayload);

        $responseUpdate->assertStatus(200);
        $this->assertDatabaseHas('users', ['name' => 'Updated Dummy Employee']);

        // 3. Delete Dummy Profile
        $responseDelete = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/staff/{$userId}");

        $responseDelete->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    /**
     * Test 2.2: Menu, Variants & Modifiers
     */
    public function test_2_2_menu_variants_and_modifiers(): void
    {
        // 1. Create parent category
        $category = Category::create([
            'name' => 'Hot Dishes',
            'is_active' => true
        ]);

        // 2. Create food item with portion variations
        $sizes = [
            ['name' => '0.5 portion', 'price' => 15000],
            ['name' => '1.0 portion', 'price' => 30000]
        ];

        $foodPayload = [
            'name' => 'QA Osh',
            'price' => 30000,
            'category_id' => $category->id,
            'description' => 'Test Osh with modifiers',
            'is_available' => true,
            'sizes' => json_encode($sizes)
        ];

        $responseFood = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/menu/foods', $foodPayload);

        $responseFood->assertStatus(201);
        $foodId = $responseFood->json('food.id');

        // Check if portion variations attached successfully
        $this->assertDatabaseHas('foods', [
            'id' => $foodId,
            'name' => 'QA Osh'
        ]);

        // 3. Toggle Parent Food availability to inactive
        $responseToggle = $this->actingAs($this->adminUser, 'sanctum')
            ->patchJson("/api/menu/foods/{$foodId}/toggle");

        $responseToggle->assertStatus(200);
        $this->assertFalse((bool)$responseToggle->json('food.is_available'), "Food remains active after toggling to inactive!");
    }

    /**
     * Test 2.3: Inventory & Supplier Controls
     */
    public function test_2_3_inventory_and_supplier_controls(): void
    {
        // 1. Setup Ingredient
        $ingredient = Ingredient::create([
            'name' => 'Guruch',
            'sku' => 'GURUCH-QA',
            'unit' => 'kg',
            'quantity' => 10.000,
            'cost_price' => 12000.00,
            'low_stock_threshold' => 2.000,
        ]);

        // 2. Execute raw material intake operation (Ombor kirimi)
        $kirimPayload = [
            'notes' => 'QA Import',
            'items' => [
                [
                    'ingredient_id' => $ingredient->id,
                    'quantity' => 5.500,
                    'unit_price' => 13000.00
                ]
            ]
        ];

        $responseKirim = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/warehouse/kirim', $kirimPayload);

        $responseKirim->assertStatus(201);

        // Verify inventory_stocks counts increment accurately
        $ingredient->refresh();
        $this->assertEquals(15.500, (float)$ingredient->quantity, "Inventory quantity did not increment correctly!");

        // 3. Attempt to record a raw intake with a negative quantity
        $negativePayload = [
            'notes' => 'QA Negative',
            'items' => [
                [
                    'ingredient_id' => $ingredient->id,
                    'quantity' => -5.000,
                    'unit_price' => 12000.00
                ]
            ]
        ];

        $responseNegative = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/warehouse/kirim', $negativePayload);

        // Expected result: rejected via request validation or database constraint.
        $this->assertTrue(
            in_array($responseNegative->getStatusCode(), [422, 500]),
            "Negative quantity intake bypass allowed! Status code: " . $responseNegative->getStatusCode()
        );

        // 4. Attempt to record a raw intake with a zero price directly in service to test DB rollback/exception
        $warehouseService = app(WarehouseService::class);
        $exceptionThrown = false;
        try {
            DB::transaction(function () use ($warehouseService, $ingredient) {
                // If service allows it, but database schema should theoretically raise constraint
                // or code logic throws ValidationException
                $warehouseService->stockIn($this->adminUser->id, [
                    'notes' => 'Zero Price QA',
                    'items' => [
                        [
                            'ingredient_id' => $ingredient->id,
                            'quantity' => 2.000,
                            'unit_price' => -100.00 // negative price
                        ]
                    ]
                ]);
            });
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }

        // Verify rollback worked or validation exception triggered
        $ingredient->refresh();
        $this->assertEquals(15.500, (float)$ingredient->quantity, "Rollback failed! Quantity updated despite zero/negative price transaction error.");
    }

    /**
     * Test 3.1: Tables Architecture
     */
    public function test_3_1_tables_architecture(): void
    {
        // 1. Create table
        $table = Table::create([
            'table_number' => 'QA Table 1',
            'capacity' => 4,
            'status' => 'occupied',
            'qr_code_token' => 'qa_token_1'
        ]);

        // 2. Bind active order to table
        $order = Order::create([
            'order_number' => 'ORD-QA-001',
            'table_id' => $table->id,
            'waiter_id' => $this->waiterUser->id,
            'total_amount' => 50000.00,
            'status' => 'new' // Active status
        ]);

        // 3. Attempt to delete Table
        $responseDelete = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/tables/{$table->id}");

        // Expected result: Blocked deletion (422, 403 or 400 status)
        $this->assertTrue(
            in_array($responseDelete->getStatusCode(), [422, 400, 403]),
            "Table deletion succeeded even with active order bound to it! Status code: " . $responseDelete->getStatusCode()
        );

        $this->assertDatabaseHas('tables', ['id' => $table->id]);
    }

    /**
     * Test 3.2: Loyalty CRM & Discount Engines
     */
    public function test_3_2_loyalty_crm_and_discount_engines(): void
    {
        // 1. Setup expired coupon
        $expiredDiscount = Discount::create([
            'name' => 'Expired Coupon',
            'type' => 'percentage',
            'value' => 15.00,
            'code' => 'EXPIRED15',
            'min_order_amount' => 10000.00,
            'starts_at' => now()->subDays(10),
            'expires_at' => now()->subDays(1), // Expiration in the past
            'is_active' => true
        ]);

        // 2. Create active table and order
        $table = Table::create([
            'table_number' => 'QA Table 2',
            'capacity' => 4,
            'status' => 'occupied',
            'qr_code_token' => 'qa_token_2'
        ]);

        $order = Order::create([
            'order_number' => 'ORD-QA-002',
            'table_id' => $table->id,
            'waiter_id' => $this->waiterUser->id,
            'total_amount' => 50000.00,
            'status' => 'new'
        ]);

        $category = Category::create(['name' => 'Salad']);
        $food = Food::create([
            'name' => 'Salad',
            'price' => 50000.00,
            'category_id' => $category->id,
            'is_available' => true
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'food_id' => $food->id,
            'quantity' => 1,
            'price' => 50000.00
        ]);

        // 3. Attempt to validate coupon via calculation engine
        $responseValidate = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/discounts/validate-code', [
                'order_id' => $order->id,
                'code' => 'EXPIRED15'
            ]);

        // Expected result: flags as expired / validation error (422)
        $responseValidate->assertStatus(422);
        $this->assertStringContainsString('tugagan', json_encode($responseValidate->json()), "Incorrect expiration error message: " . json_encode($responseValidate->json()));
    }

    /**
     * Test 3.3: Dynamic Statistics & Analytics
     */
    public function test_3_3_dynamic_statistics_and_analytics(): void
    {
        // 1. Trigger aggregation queries calculating total today sales
        $table = Table::create([
            'table_number' => 'QA Table 3',
            'capacity' => 4,
            'status' => 'occupied',
            'qr_code_token' => 'qa_token_3'
        ]);

        $order = Order::create([
            'order_number' => 'ORD-QA-003',
            'table_id' => $table->id,
            'waiter_id' => $this->waiterUser->id,
            'total_amount' => 85000.00,
            'status' => 'delivered' // Completed / Delivered order count towards sales revenue
        ]);

        // Force clear cache so dashboard calculates fresh stats
        $dashboardService = app(\App\Services\DashboardService::class);
        $dashboardService->clearCache();

        // 2. Fetch dashboard statistics
        // Note: As discovered in Test 1.1, accessing dashboard needs admin permission, so we call actingAs adminUser
        $responseDashboard = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/admin/dashboard');

        $responseDashboard->assertStatus(200);
        $revenueVal = (float)$responseDashboard->json('widgets.revenue.value');

        // 3. Compare with direct database sum record
        $dbRevenueSum = (float)Order::where('status', 'delivered')->sum('total_amount');

        $this->assertEquals($dbRevenueSum, $revenueVal, "Dashboard statistics numerical mismatch!");
    }
}
