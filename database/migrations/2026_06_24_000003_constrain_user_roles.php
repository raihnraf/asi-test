<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNotIn('role', ['admin', 'staff'])
            ->update(['role' => 'staff']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'staff'])
                ->default('staff')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')
                ->default('staff')
                ->change();
        });
    }
};
