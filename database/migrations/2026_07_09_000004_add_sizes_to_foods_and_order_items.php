<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            $table->json('sizes')->nullable();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('size_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            $table->dropColumn('sizes');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('size_name');
        });
    }
};
