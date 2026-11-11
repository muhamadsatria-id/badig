<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('units')) {
            return;
        }

        foreach ([
            'kode' => fn (Blueprint $table) => $table->string('kode', 50)->nullable(),
            'kode_unit' => fn (Blueprint $table) => $table->string('kode_unit', 50)->nullable(),
            'slug' => fn (Blueprint $table) => $table->string('slug', 120)->nullable(),
            'deskripsi' => fn (Blueprint $table) => $table->text('deskripsi')->nullable(),
            'is_active' => fn (Blueprint $table) => $table->boolean('is_active')->default(true),
        ] as $column => $callback) {
            if (! Schema::hasColumn('units', $column)) {
                Schema::table('units', fn (Blueprint $table) => $callback($table));
            }
        }

        if (Schema::hasColumn('units', 'is_active')) {
            DB::table('units')->whereNull('is_active')->update(['is_active' => true]);
        }

        $this->syncWithoutUniqueCollisions();
    }

    private function syncWithoutUniqueCollisions(): void
    {
        if (! Schema::hasColumn('units', 'kode') || ! Schema::hasColumn('units', 'kode_unit')) {
            return;
        }

        DB::table('units')->orderBy('id')->get(['id', 'kode', 'kode_unit'])->each(function ($row) {
            $kode = trim((string) ($row->kode ?? ''));
            $kodeUnit = trim((string) ($row->kode_unit ?? ''));

            if ($kode === '' && $kodeUnit !== '' && ! DB::table('units')->where('kode', $kodeUnit)->where('id', '<>', $row->id)->exists()) {
                DB::table('units')->where('id', $row->id)->update(['kode' => $kodeUnit]);
            }

            if ($kodeUnit === '' && $kode !== '' && ! DB::table('units')->where('kode_unit', $kode)->where('id', '<>', $row->id)->exists()) {
                DB::table('units')->where('id', $row->id)->update(['kode_unit' => $kode]);
            }
        });
    }

    public function down(): void
    {
        // Non-destructive compatibility repair.
    }
};
