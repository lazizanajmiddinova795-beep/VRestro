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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('restrict');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('restrict');
            $table->decimal('total_amount', 12, 2);
            $table->string('payment_method'); // cash, card, qr, mixed
            $table->decimal('cash_amount', 12, 2)->default(0.00);
            $table->decimal('card_amount', 12, 2)->default(0.00);
            $table->decimal('qr_amount', 12, 2)->default(0.00);
            $table->decimal('bonus_used', 12, 2)->default(0.00);
            $table->string('status')->default('completed'); // completed, refunded
            $table->timestamps();

            $table->index('order_id');
            $table->index('customer_id');
            $table->index('payment_method');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
