<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\Food;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class FoodSizeTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $waiterUser;
    protected Table $table;
    protected Category $category;
    protected Ingredient $meat;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles & Permissions
        $adminRole = Role::create(['name' => 'Admin']);
        $managePayments = Permission::create(['name' => 'manage payments']);
        $adminRole->givePermissionTo($managePayments);

        // Admin User
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'login' => 'sizeadmin',
            'password' => bcrypt('password123'),
            'phone' => '+998901111123',
            'status' => 'active',
        ]);
        $this->adminUser->assignRole($adminRole);

        // Waiter User
        $this->waiterUser = User::create([
            'name' => 'Waiter User',
            'login' => 'sizewaiter',
            'password' => bcrypt('password123'),
            'phone' => '+998901111124',
            'status' => 'active',
        ]);

        $this->table = Table::create([
            'table_number' => 'Stol 10',
            'capacity' => 4,
            'status' => 'empty',
            'qr_code_token' => 't10',
        ]);

        $this->category = Category::create(['name' => 'Miliy Taomlar']);

        $this->meat = Ingredient::create([
            'name' => 'Go\'sht',
            'sku' => 'ING-MEAT-002',
            'unit' => 'kg',
            'quantity' => 10.00,
            'low_stock_threshold' => 1.00,
        ]);
    }

    public function test_can_create_food_with_sizes(): void
    {
        $sizes = [
            ['name' => 'Yarim', 'price' => 15000, 'recipe_multiplier' => 0.5],
            ['name' => 'Butun', 'price' => 28000, 'recipe_multiplier' => 1.0]
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/menu/foods', [
                'name' => 'Osh',
                'price' => 28000,
                'category_id' => $this->category->id,
                'is_available' => true,
                'sizes' => json_encode($sizes)
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('foods', [
            'name' => 'Osh',
            'price' => 28000
        ]);

        $food = Food::where('name', 'Osh')->first();
        $this->assertNotEmpty($food->sizes);
        $this->assertEquals('Yarim', $food->sizes[0]['name']);
    }

    public function test_order_creates_with_specific_size_and_calculates_price(): void
    {
        $sizes = [
            ['name' => 'Yarim porsiya', 'price' => 12000, 'recipe_multiplier' => 0.5],
            ['name' => 'Bir porsiya', 'price' => 20000, 'recipe_multiplier' => 1.0]
        ];

        $food = Food::create([
            'name' => 'Lag\'mon',
            'price' => 20000.00,
            'category_id' => $this->category->id,
            'is_available' => true,
            'sizes' => $sizes
        ]);

        // Place order for 2 half portions (Yarim porsiya)
        $response = $this->actingAs($this->waiterUser, 'sanctum')
            ->postJson('/api/orders', [
                'table_id' => $this->table->id,
                'waiter_id' => $this->waiterUser->id,
                'items' => [
                    [
                        'food_id' => $food->id,
                        'quantity' => 2,
                        'size_name' => 'Yarim porsiya',
                        'notes' => 'Qora murchsiz'
                    ]
                ]
            ]);

        $response->assertStatus(201);
        
        // 2 portions * 12000 UZS = 24000 UZS
        $order = Order::first();
        $this->assertEquals(24000.00, (float) $order->total_amount);

        $orderItem = OrderItem::first();
        $this->assertEquals('Yarim porsiya', $orderItem->size_name);
        $this->assertEquals(12000.00, (float) $orderItem->price);
    }

    public function test_recipe_stock_deduction_scales_by_multiplier(): void
    {
        $sizes = [
            ['name' => 'Yarim', 'price' => 15000, 'recipe_multiplier' => 0.5],
            ['name' => 'Butun', 'price' => 28000, 'recipe_multiplier' => 1.0]
        ];

        $food = Food::create([
            'name' => 'Manti',
            'price' => 28000.00,
            'category_id' => $this->category->id,
            'is_available' => true,
            'sizes' => $sizes
        ]);

        Recipe::create([
            'food_id' => $food->id,
            'ingredient_id' => $this->meat->id,
            'quantity_required' => 0.200 // 200 grams required per standard portion
        ]);

        // Order 3 portions of 'Yarim' size
        // Expected meat deduction: 3 * 0.200 * 0.5 = 0.300 kg
        $order = Order::create([
            'order_number' => 'ORD-SIZE-100',
            'table_id' => $this->table->id,
            'waiter_id' => $this->waiterUser->id,
            'total_amount' => 45000.00,
            'status' => 'new'
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'food_id' => $food->id,
            'quantity' => 3,
            'price' => 15000.00,
            'size_name' => 'Yarim'
        ]);

        // Transition order status to 'cooking' to trigger deduction
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->patchJson("/api/orders/{$order->id}/status", [
                'status' => 'cooking'
            ]);

        $response->assertStatus(200);

        // Initial meat quantity = 10.00 kg. Deducted: 0.30 kg. Remaining: 9.70 kg
        $this->meat->refresh();
        $this->assertEquals(9.70, (float) $this->meat->quantity);
    }
}
