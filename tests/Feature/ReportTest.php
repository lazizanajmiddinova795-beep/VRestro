<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\Food;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $waiterUser;
    protected User $chefUser;
    protected Order $order;
    protected Food $food;
    protected Ingredient $rice;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles and permissions
        $adminRole = Role::create(['name' => 'Admin']);
        $chefRole = Role::create(['name' => 'Chef']);
        $viewReports = Permission::create(['name' => 'view reports']);
        $adminRole->givePermissionTo($viewReports);

        // Users
        $this->adminUser = User::create([
            'name' => 'Admin Reporter',
            'login' => 'adminrep',
            'password' => bcrypt('password123'),
            'phone' => '+998901111115',
            'status' => 'active',
        ]);
        $this->adminUser->assignRole($adminRole);

        $this->chefUser = User::create([
            'name' => 'Chef Oshpaz',
            'login' => 'chef1',
            'password' => bcrypt('password123'),
            'phone' => '+998901111116',
            'status' => 'active',
        ]);
        $this->chefUser->assignRole($chefRole);

        $this->waiterUser = User::create([
            'name' => 'Waiter Xizmatchi',
            'login' => 'waiter1',
            'password' => bcrypt('password123'),
            'phone' => '+998901111117',
            'status' => 'active',
        ]);

        // Table
        $table = Table::create([
            'table_number' => 'Stol 1',
            'capacity' => 4,
            'status' => 'empty',
            'qr_code_token' => 't1',
        ]);

        // Order
        $this->order = Order::create([
            'order_number' => 'ORD-REP-001',
            'table_id' => $table->id,
            'waiter_id' => $this->waiterUser->id,
            'total_amount' => 120000.00,
            'status' => 'delivered',
        ]);

        // Category & Food
        $category = Category::create(['name' => 'Shashliklar']);
        $this->food = Food::create([
            'name' => 'Gijduvon Shashlik',
            'price' => 60000.00,
            'category_id' => $category->id,
            'is_available' => true,
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'food_id' => $this->food->id,
            'quantity' => 2,
            'price' => 60000.00,
        ]);

        // Payment
        Payment::create([
            'order_id' => $this->order->id,
            'customer_id' => null,
            'total_amount' => 120000.00,
            'payment_method' => 'card',
            'card_amount' => 120000.00,
            'status' => 'completed',
        ]);

        // Ingredient and Recipe
        $this->rice = Ingredient::create([
            'name' => 'Guruch',
            'sku' => 'ING-RICE-001',
            'unit' => 'kg',
            'quantity' => 50.00,
            'low_stock_threshold' => 10.00,
        ]);

        Recipe::create([
            'food_id' => $this->food->id,
            'ingredient_id' => $this->rice->id,
            'quantity_required' => 0.150, // 150 grams per serving
        ]);
    }

    public function test_admin_can_access_sales_report(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/reports/sales');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'summary' => [
                'grand_invoiced_income',
                'cash_total',
                'card_total',
                'qr_total',
                'disbursed_discounts_total',
                'cashback_bonuses_used',
                'net_income'
            ],
            'daily_charts'
        ]);
        
        $response->assertJsonPath('summary.grand_invoiced_income', 120000);
    }

    public function test_unauthorized_cannot_access_reports(): void
    {
        $response = $this->actingAs($this->waiterUser, 'sanctum')
            ->getJson('/api/reports/sales');

        $response->assertStatus(403);
    }

    public function test_menu_performance_ranking(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/reports/menu');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'top_selling',
            'least_selling'
        ]);

        $this->assertNotEmpty($response->json('top_selling'));
        $this->assertEquals('Gijduvon Shashlik', $response->json('top_selling.0.name'));
        $this->assertEquals(2, $response->json('top_selling.0.units_sold'));
    }

    public function test_warehouse_consumption_calculation(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/reports/inventory');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'depletions'
        ]);

        // Expected depletion is 2 servings * 0.150 kg = 0.30 kg of Guruch
        $this->assertNotEmpty($response->json('depletions'));
        $this->assertEquals('Guruch', $response->json('depletions.0.name'));
        $this->assertEquals(0.30, $response->json('depletions.0.total_consumed'));
    }

    public function test_staff_kpi_metrics(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/reports/staff');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'waiters',
            'chefs'
        ]);

        $this->assertNotEmpty($response->json('waiters'));
        $this->assertEquals('Waiter Xizmatchi', $response->json('waiters.0.name'));
        $this->assertEquals(1, $response->json('waiters.0.total_orders_taken'));
    }
}
