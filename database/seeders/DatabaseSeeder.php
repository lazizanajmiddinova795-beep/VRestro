<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create the first branch
        $branch = \App\Models\Branch::firstOrCreate(
            ['name' => 'FoodFlow Asosiy filial'],
            ['is_active' => true]
        );

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Roles
        $adminRole = Role::firstOrCreate(['name' => 'Manager']);
        $chefRole = Role::firstOrCreate(['name' => 'Chef']);
        $waiterRole = Role::firstOrCreate(['name' => 'Waiter']);
        $cashierRole = Role::firstOrCreate(['name' => 'Cashier']);

        // Create Permissions
        $managePayments = Permission::firstOrCreate(['name' => 'manage payments']);
        $manageDiscounts = Permission::firstOrCreate(['name' => 'manage discounts']);
        $viewReports = Permission::firstOrCreate(['name' => 'view reports']);
        $manageSettings = Permission::firstOrCreate(['name' => 'manage settings']);
        $viewCashierDashboard = Permission::firstOrCreate(['name' => 'view cashier dashboard']);
        $viewKitchenPanel = Permission::firstOrCreate(['name' => 'view kitchen panel']);
        $viewWaiterPanel = Permission::firstOrCreate(['name' => 'view waiter panel']);
        
        $adminRole->givePermissionTo($managePayments);
        $adminRole->givePermissionTo($manageDiscounts);
        $adminRole->givePermissionTo($viewReports);
        $adminRole->givePermissionTo($manageSettings);
        $adminRole->givePermissionTo($viewCashierDashboard);
        $adminRole->givePermissionTo($viewKitchenPanel);
        $adminRole->givePermissionTo($viewWaiterPanel);
        
        $chefRole->givePermissionTo($viewKitchenPanel);

        $waiterRole->givePermissionTo($viewWaiterPanel);

        $cashierRole->givePermissionTo($managePayments);
        $cashierRole->givePermissionTo($manageDiscounts);
        $cashierRole->givePermissionTo($viewReports);
        $cashierRole->givePermissionTo($viewCashierDashboard);

        // Create or update Super Admin User
        $adminUser = User::firstOrCreate(
            ['login' => 'admin@itcloud.uz'],
            [
                'name' => 'Tizim Administratori',
                'password' => Hash::make('password'),
                'face_registered' => true,
                'phone' => '+998901234567',
                'shift_hours' => '09:00 - 18:00',
                'status' => 'active',
                'branch_id' => null,
                'is_superadmin' => true,
            ]
        );
        $adminUser->assignRole($adminRole);

        // Removed other seeders to keep database clean

        // Seed settings
        \App\Models\Setting::create(['key' => 'restaurant_name', 'value' => 'VRestro Restaurant', 'type' => 'string']);
        \App\Models\Setting::create(['key' => 'restaurant_address', 'value' => 'Toshkent, O\'zbekiston', 'type' => 'string']);
        \App\Models\Setting::create(['key' => 'restaurant_phone', 'value' => '+998901234567', 'type' => 'string']);
        \App\Models\Setting::create(['key' => 'restaurant_hours', 'value' => '09:00 - 23:00', 'type' => 'string']);
        \App\Models\Setting::create(['key' => 'restaurant_logo', 'value' => null, 'type' => 'file']);
        \App\Models\Setting::create(['key' => 'tax_rate', 'value' => '12', 'type' => 'number']);
        \App\Models\Setting::create(['key' => 'currency', 'value' => 'UZS', 'type' => 'string']);
        \App\Models\Setting::create(['key' => 'language', 'value' => 'uz', 'type' => 'string']);
        \App\Models\Setting::create(['key' => 'telegram_bot_token', 'value' => '8846820582:AAEYcOljJoCbDBfGNkuG-dntVw1dFfWdDWw', 'type' => 'string']);
        \App\Models\Setting::create(['key' => 'telegram_chat_id', 'value' => '@VRestro_uz', 'type' => 'string']);
        \App\Models\Setting::create(['key' => 'telegram_notifications_enabled', 'value' => 'true', 'type' => 'boolean']);
        \App\Models\Setting::create(['key' => 'service_charge_rate', 'value' => '10', 'type' => 'number']);
        \App\Models\Setting::create(['key' => 'receipt_header', 'value' => 'VRestro - Xizmatimizdan mamnunmisiz?', 'type' => 'string']);
        \App\Models\Setting::create(['key' => 'receipt_footer', 'value' => 'Xaridingiz uchun rahmat! Yana keling!', 'type' => 'string']);
    }
}
