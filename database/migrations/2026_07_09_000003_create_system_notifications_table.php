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
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['low_stock', 'new_order', 'order_cancelled', 'system']);
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->json('meta_data')->nullable();
            $table->timestamps();

            $table->index('is_read');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_notifications');
    }
};
