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
use App\Models\SystemNotification;
use App\Jobs\ProcessNotificationJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $waiterUser;
    protected Order $order;
    protected Food $food;
    protected Ingredient $ingredient;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Roles and Permissions
        $adminRole = Role::create(['name' => 'Admin']);
        $managePayments = Permission::create(['name' => 'manage payments']);
        $adminRole->givePermissionTo($managePayments);

        // Users
        $this->adminUser = User::create([
            'name' => 'System Admin',
            'login' => 'sysadmin',
            'password' => bcrypt('password123'),
            'phone' => '+998901111119',
            'status' => 'active',
        ]);
        $this->adminUser->assignRole($adminRole);

        $this->waiterUser = User::create([
            'name' => 'Waiter Ofitsiant',
            'login' => 'waiter2',
            'password' => bcrypt('password123'),
            'phone' => '+998901111120',
            'status' => 'active',
        ]);

        // Table
        $table = Table::create([
            'table_number' => 'Stol 1',
            'capacity' => 4,
            'status' => 'empty',
            'qr_code_token' => 't1',
        ]);

        $category = Category::create(['name' => 'Taomlar']);
        $this->food = Food::create([
            'name' => 'Somsa',
            'price' => 15000.00,
            'category_id' => $category->id,
            'is_available' => true,
        ]);

        $this->ingredient = Ingredient::create([
            'name' => 'Go\'sht',
            'sku' => 'ING-MEAT-001',
            'unit' => 'kg',
            'quantity' => 10.00,
            'low_stock_threshold' => 5.00,
        ]);

        Recipe::create([
            'food_id' => $this->food->id,
            'ingredient_id' => $this->ingredient->id,
            'quantity_required' => 0.100, // 100 grams
        ]);
    }

    public function test_can_fetch_notifications(): void
    {
        SystemNotification::create([
            'type' => 'new_order',
            'title' => 'Test Notification',
            'message' => 'Detail test alert message',
            'is_read' => false
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/notifications');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.title', 'Test Notification');
    }

    public function test_can_mark_notification_as_read(): void
    {
        $notification = SystemNotification::create([
            'type' => 'low_stock',
            'title' => 'Low Stock',
            'message' => 'Flour is running out',
            'is_read' => false
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->patchJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200);
        $response->assertJsonPath('notification.is_read', true);
        $this->assertDatabaseHas('system_notifications', [
            'id' => $notification->id,
            'is_read' => true
        ]);
    }

    public function test_can_mark_all_notifications_as_read(): void
    {
        SystemNotification::create([
            'type' => 'low_stock',
            'title' => 'Alert 1',
            'message' => 'Msg 1',
            'is_read' => false
        ]);
        SystemNotification::create([
            'type' => 'new_order',
            'title' => 'Alert 2',
            'message' => 'Msg 2',
            'is_read' => false
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/notifications/read-all');

        $response->assertStatus(200);
        $this->assertEquals(0, SystemNotification::where('is_read', false)->count());
    }

    public function test_can_delete_notification(): void
    {
        $notification = SystemNotification::create([
            'type' => 'low_stock',
            'title' => 'To Delete',
            'message' => 'Delete msg',
            'is_read' => false
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('system_notifications', ['id' => $notification->id]);
    }

    public function test_new_order_triggers_async_notification_job(): void
    {
        Queue::fake();

        $table = Table::create([
            'table_number' => 'Stol 2',
            'capacity' => 2,
            'status' => 'empty',
            'qr_code_token' => 't2',
        ]);

        $response = $this->actingAs($this->waiterUser, 'sanctum')
            ->postJson('/api/orders', [
                'table_id' => $table->id,
                'waiter_id' => $this->waiterUser->id,
                'items' => [
                    ['food_id' => $this->food->id, 'quantity' => 2, 'notes' => 'Tuzsiz']
                ]
            ]);

        $response->assertStatus(201);
        Queue::assertPushed(ProcessNotificationJob::class);
    }

    public function test_cancelled_order_triggers_async_notification_job(): void
    {
        Queue::fake();

        $order = Order::create([
            'order_number' => 'ORD-REP-102',
            'total_amount' => 30000.00,
            'status' => 'new',
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/orders/{$order->id}/cancel", [
                'cancellation_reason' => 'Customer changed mind'
            ]);

        $response->assertStatus(200);
        Queue::assertPushed(ProcessNotificationJob::class);
    }

    public function test_low_stock_during_cooking_triggers_async_notification_job(): void
    {
        Queue::fake();

        $order = Order::create([
            'order_number' => 'ORD-REP-103',
            'total_amount' => 15000.00,
            'status' => 'new',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'food_id' => $this->food->id,
            'quantity' => 60, // 60 portions * 0.1kg = 6kg, remaining: 4kg. Drops below threshold (5kg).
            'price' => 15000.00,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->patchJson("/api/orders/{$order->id}/status", [
                'status' => 'cooking'
            ]);

        $response->assertStatus(200);
        Queue::assertPushed(ProcessNotificationJob::class);
    }
}
