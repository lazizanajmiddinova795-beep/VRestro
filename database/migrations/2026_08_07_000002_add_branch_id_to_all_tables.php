<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('is_superadmin')
                  ->constrained('branches')->nullOnDelete();
        });

        Schema::table('tables', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('qr_code_token')
                  ->constrained('branches')->nullOnDelete();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('is_active')
                  ->constrained('branches')->nullOnDelete();
        });

        Schema::table('foods', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('sizes')
                  ->constrained('branches')->nullOnDelete();
        });

        Schema::table('ingredients', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('low_stock_threshold')
                  ->constrained('branches')->nullOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('cancellation_reason')
                  ->constrained('branches')->nullOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('status')
                  ->constrained('branches')->nullOnDelete();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('total_spent_amount')
                  ->constrained('branches')->nullOnDelete();
        });

        Schema::table('discounts', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('is_active')
                  ->constrained('branches')->nullOnDelete();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('category')
                  ->constrained('branches')->nullOnDelete();
        });

        Schema::table('system_notifications', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('meta_data')
                  ->constrained('branches')->nullOnDelete();
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('type')
                  ->constrained('branches')->nullOnDelete();
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('ip_address')
                  ->constrained('branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        $tables = ['users', 'tables', 'categories', 'foods', 'ingredients',
                   'orders', 'payments', 'customers', 'discounts', 'expenses',
                   'system_notifications', 'settings', 'activity_logs'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('branch_id');
            });
        }
    }
};
