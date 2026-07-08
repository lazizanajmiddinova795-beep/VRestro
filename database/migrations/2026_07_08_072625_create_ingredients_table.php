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
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('sku')->unique();
            $table->decimal('quantity', 12, 3)->default(0.000);
            $table->string('unit'); // kg, g, l, ml, dona, pachka
            $table->decimal('cost_price', 12, 2)->default(0.00);
            $table->decimal('low_stock_threshold', 12, 3)->default(5.000);
            $table->timestamps();

            $table->index('sku');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
