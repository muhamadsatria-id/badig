<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('units')) {
            return;
        }

        foreach ([
            'nama_unit' => fn (Blueprint $table) => $table->string('nama_unit')->nullable(),
            'kode' => fn (Blueprint $table) => $table->string('kode', 20)->nullable(),
            'kode_unit' => fn (Blueprint $table) => $table->string('kode_unit', 50)->nullable(),
            'slug' => fn (Blueprint $table) => $table->string('slug', 80)->nullable(),
            'deskripsi' => fn (Blueprint $table) => $table->text('deskripsi')->nullable(),
            'is_active' => fn (Blueprint $table) => $table->boolean('is_active')->default(true)->index(),
            'created_at' => fn (Blueprint $table) => $table->timestamp('created_at')->nullable(),
            'updated_at' => fn (Blueprint $table) => $table->timestamp('updated_at')->nullable(),
        ] as $column => $callback) {
            if (! Schema::hasColumn('units', $column)) {
                Schema::table('units', fn (Blueprint $table) => $callback($table));
            }
        }
    }

    public function down(): void
    {
        // Non-destructive repair migration.
    }
};
