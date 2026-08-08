<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('branches')
            ->where('name', 'VRestro Asosiy filial')
            ->update(['name' => 'FoodFlow Asosiy filial']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('branches')
            ->where('name', 'FoodFlow Asosiy filial')
            ->update(['name' => 'VRestro Asosiy filial']);
    }
};
