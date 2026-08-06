<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // tables: table_number unique per branch
        Schema::table('tables', function (Blueprint $table) {
            $table->dropUnique(['table_number']);
            $table->unique(['table_number', 'branch_id']);
        });

        // ingredients: name and sku unique per branch
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropUnique(['sku']);
            $table->unique(['name', 'branch_id']);
            $table->unique(['sku', 'branch_id']);
        });

        // settings: key unique per branch
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['key']);
            $table->unique(['key', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropUnique(['table_number', 'branch_id']);
            $table->unique(['table_number']);
        });

        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropUnique(['name', 'branch_id']);
            $table->dropUnique(['sku', 'branch_id']);
            $table->unique(['name']);
            $table->unique(['sku']);
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['key', 'branch_id']);
            $table->unique(['key']);
        });
    }
};
