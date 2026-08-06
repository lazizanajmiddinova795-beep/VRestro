<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $branch = DB::table('branches')->first();
        if (!$branch) return;

        $branchId = $branch->id;

        // Assign all non-superadmin users to the first branch
        DB::table('users')
            ->where('is_superadmin', false)
            ->whereNull('branch_id')
            ->update(['branch_id' => $branchId]);

        // Assign all data in these tables to the first branch
        $tables = ['tables', 'orders', 'payments', 'customers',
                   'expenses', 'ingredients', 'system_notifications',
                   'activity_logs'];

        foreach ($tables as $tableName) {
            DB::table($tableName)
                ->whereNull('branch_id')
                ->update(['branch_id' => $branchId]);
        }

        // NOTE: categories, foods, discounts, settings stay NULL (global)
        // They can be assigned to branches later if needed
    }

    public function down(): void
    {
        $tables = ['users', 'tables', 'orders', 'payments', 'customers',
                   'expenses', 'ingredients', 'system_notifications',
                   'activity_logs'];

        foreach ($tables as $tableName) {
            DB::table($tableName)->update(['branch_id' => null]);
        }
    }
};
