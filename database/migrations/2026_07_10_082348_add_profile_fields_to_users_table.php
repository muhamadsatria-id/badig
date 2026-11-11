<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        foreach ([
            'nrp' => fn (Blueprint $table) => $table->string('nrp', 80)->nullable(),
            'phone' => fn (Blueprint $table) => $table->string('phone', 50)->nullable(),
            'address' => fn (Blueprint $table) => $table->text('address')->nullable(),
            'photo' => fn (Blueprint $table) => $table->string('photo')->nullable(),
        ] as $column => $callback) {
            if (! Schema::hasColumn('users', $column)) {
                Schema::table('users', fn (Blueprint $table) => $callback($table));
            }
        }
    }

    public function down(): void
    {
        // Do not remove shared profile columns on rollback.
    }
};
