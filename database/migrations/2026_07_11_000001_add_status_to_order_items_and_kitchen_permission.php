<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('status')->default('pending'); // pending, cooking, ready
            $table->index('status');
        });

        // Register kitchen panel permission and assign it to Chef and Admin roles
        try {
            // Reset cached roles and permissions
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $permission = Permission::firstOrCreate(['name' => 'view kitchen panel']);

            $chefRole = Role::where('name', 'Chef')->first();
            if ($chefRole) {
                $chefRole->givePermissionTo($permission);
            }

            $adminRole = Role::where('name', 'Admin')->first();
            if ($adminRole) {
                $adminRole->givePermissionTo($permission);
            }
        } catch (\Exception $e) {
            // Ignore errors if permission tables are not seeded yet during fresh setups
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });

        try {
            $permission = Permission::where('name', 'view kitchen panel')->first();
            if ($permission) {
                $permission->delete();
            }
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Exception $e) {
            // Ignore errors
        }
    }
};
