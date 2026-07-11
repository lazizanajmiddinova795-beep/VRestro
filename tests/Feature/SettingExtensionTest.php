<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Setting;
use App\Models\Food;
use App\Models\Table;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SettingExtensionTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles & Permissions
        $adminRole = Role::create(['name' => 'Admin']);
        $manageSettings = Permission::create(['name' => 'manage settings']);
        $adminRole->givePermissionTo($manageSettings);

        // Admin User
        $this->adminUser = User::create([
            'name' => 'Settings Extension Admin',
            'login' => 'extadmin',
            'password' => bcrypt('password123'),
            'phone' => '+998901111128',
            'status' => 'active',
        ]);
        $this->adminUser->assignRole($adminRole);

        // Seed settings
        Setting::create(['key' => 'restaurant_name', 'value' => 'VRestro Ext', 'type' => 'string']);
        Setting::create(['key' => 'service_charge_rate', 'value' => '10', 'type' => 'number']); // 10% Service Fee
        Setting::create(['key' => 'telegram_notifications_enabled', 'value' => 'false', 'type' => 'boolean']);

        $this->orderService = app(OrderService::class);
    }

    public function test_order_creation_calculates_and_adds_service_charge(): void
    {
        // Create table
        $table = Table::create(['table_number' => '12', 'capacity' => 4, 'status' => 'empty']);

        // Create category
        $category = \App\Models\Category::create(['name' => 'Taomlar']);

        // Create food
        $food = Food::create([
            'name' => 'Palov',
            'category_id' => $category->id,
            'price' => 40000.00,
            'status' => 'available',
            'sizes' => []
        ]);

        $order = $this->orderService->createOrder([
            'table_id' => $table->id,
            'items' => [
                [
                    'food_id' => $food->id,
                    'quantity' => 2, // 2 * 40000 = 80 000
                    'size_name' => null,
                ]
            ]
        ], $this->adminUser);

        // Total should be 80 000 + 10% service fee = 88 000 UZS
        $this->assertEquals(88000.00, (float) $order->total_amount);
    }

    public function test_admin_can_clear_cache(): void
    {
        Cache::put('test_cache_key', 'some_cached_value', 10);
        $this->assertEquals('some_cached_value', Cache::get('test_cache_key'));

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/settings/clear-cache');

        $response->assertStatus(200);
        $this->assertNull(Cache::get('test_cache_key'));
    }
}
