<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('user_name')->nullable();       // snapshot of name at log time
            $table->string('user_role')->nullable();       // snapshot of role
            $table->string('action_type', 60);             // e.g. login, order_created, payment
            $table->string('module', 60)->nullable();      // e.g. orders, staff, payments
            $table->text('description');                   // human-readable description
            $table->json('meta')->nullable();              // extra data (id, amount, etc.)
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['action_type', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
