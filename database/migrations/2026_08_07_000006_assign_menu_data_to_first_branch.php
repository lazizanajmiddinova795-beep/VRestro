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

        // Assign all categories to the first branch
        DB::table('categories')
            ->whereNull('branch_id')
            ->update(['branch_id' => $branchId]);

        // Assign all foods to the first branch
        DB::table('foods')
            ->whereNull('branch_id')
            ->update(['branch_id' => $branchId]);

        // Assign all discounts to the first branch
        DB::table('discounts')
            ->whereNull('branch_id')
            ->update(['branch_id' => $branchId]);

        // Assign all settings to the first branch
        DB::table('settings')
            ->whereNull('branch_id')
            ->update(['branch_id' => $branchId]);
    }

    public function down(): void
    {
        $tables = ['categories', 'foods', 'discounts', 'settings'];
        foreach ($tables as $tableName) {
            DB::table($tableName)->update(['branch_id' => null]);
        }
    }
};
