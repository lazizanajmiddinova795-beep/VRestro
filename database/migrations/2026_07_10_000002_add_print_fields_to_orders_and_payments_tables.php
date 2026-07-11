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
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_printed')->default(false);
            $table->timestamp('printed_at')->nullable();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('is_printed')->default(false);
            $table->timestamp('printed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_printed', 'printed_at']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['is_printed', 'printed_at']);
        });
    }
};
