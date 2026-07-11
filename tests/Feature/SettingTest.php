<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $waiterUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles & Permissions
        $adminRole = Role::create(['name' => 'Admin']);
        $manageSettings = Permission::create(['name' => 'manage settings']);
        $adminRole->givePermissionTo($manageSettings);

        // Admin User
        $this->adminUser = User::create([
            'name' => 'Settings Admin',
            'login' => 'setadmin',
            'password' => bcrypt('password123'),
            'phone' => '+998901111126',
            'status' => 'active',
        ]);
        $this->adminUser->assignRole($adminRole);

        // Waiter User
        $this->waiterUser = User::create([
            'name' => 'Waiter User',
            'login' => 'setwaiter',
            'password' => bcrypt('password123'),
            'phone' => '+998901111127',
            'status' => 'active',
        ]);

        // Seed settings
        Setting::create(['key' => 'restaurant_name', 'value' => 'Initial Restro', 'type' => 'string']);
        Setting::create(['key' => 'tax_rate', 'value' => '12', 'type' => 'number']);
        Setting::create(['key' => 'currency', 'value' => 'UZS', 'type' => 'string']);
    }

    public function test_can_retrieve_settings(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/settings');

        $response->assertStatus(200);
        $response->assertJsonPath('restaurant_name', 'Initial Restro');
        $response->assertJsonPath('tax_rate', 12);
        $response->assertJsonPath('currency', 'UZS');
    }

    public function test_admin_can_update_settings(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/settings', [
                'restaurant_name' => 'Updated Restro Name',
                'tax_rate' => 15,
                'currency' => 'USD'
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('settings.restaurant_name', 'Updated Restro Name');
        $response->assertJsonPath('settings.tax_rate', 15);
        $response->assertJsonPath('settings.currency', 'USD');

        $this->assertDatabaseHas('settings', [
            'key' => 'restaurant_name',
            'value' => 'Updated Restro Name'
        ]);
    }

    public function test_waiter_cannot_update_settings(): void
    {
        $response = $this->actingAs($this->waiterUser, 'sanctum')
            ->postJson('/api/settings', [
                'restaurant_name' => 'Hacked Restro'
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_change_password_with_valid_credentials(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/settings/password', [
                'old_password' => 'password123',
                'new_password' => 'newpassword123',
                'new_password_confirmation' => 'newpassword123'
            ]);

        $response->assertStatus(200);
        
        $this->adminUser->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->adminUser->password));
    }

    public function test_user_cannot_change_password_with_invalid_credentials(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/settings/password', [
                'old_password' => 'wrongpassword',
                'new_password' => 'newpassword123',
                'new_password_confirmation' => 'newpassword123'
            ]);

        $response->assertStatus(422);
    }
}
