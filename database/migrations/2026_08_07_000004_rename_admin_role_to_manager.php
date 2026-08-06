<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')
            ->where('name', 'Admin')
            ->update(['name' => 'Manager']);
    }

    public function down(): void
    {
        DB::table('roles')
            ->where('name', 'Manager')
            ->update(['name' => 'Admin']);
    }
};
