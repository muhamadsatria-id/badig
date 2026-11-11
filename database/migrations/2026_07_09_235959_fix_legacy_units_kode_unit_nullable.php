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
            'slug' => fn (Blueprint $table) => $table->string('slug')->nullable(),
            'deskripsi' => fn (Blueprint $table) => $table->text('deskripsi')->nullable(),
            'is_active' => fn (Blueprint $table) => $table->boolean('is_active')->default(true),
            'created_at' => fn (Blueprint $table) => $table->timestamp('created_at')->nullable(),
            'updated_at' => fn (Blueprint $table) => $table->timestamp('updated_at')->nullable(),
        ] as $column => $callback) {
            if (! Schema::hasColumn('units', $column)) {
                Schema::table('units', fn (Blueprint $table) => $callback($table));
            }
        }

        foreach (['kode', 'kode_unit'] as $column) {
            if (! Schema::hasColumn('units', $column)) {
                continue;
            }

            try {
                DB::statement("ALTER TABLE `units` MODIFY `{$column}` VARCHAR(50) NULL");
            } catch (Throwable $e) {
                report($e);
            }
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

            if ($kode === '' && $kodeUnit !== '') {
                $conflict = DB::table('units')->where('kode', $kodeUnit)->where('id', '<>', $row->id)->exists();
                if (! $conflict) {
                    DB::table('units')->where('id', $row->id)->update(['kode' => $kodeUnit]);
                }
            }

            if ($kodeUnit === '' && $kode !== '') {
                $conflict = DB::table('units')->where('kode_unit', $kode)->where('id', '<>', $row->id)->exists();
                if (! $conflict) {
                    DB::table('units')->where('id', $row->id)->update(['kode_unit' => $kode]);
                }
            }
        });
    }

    public function down(): void
    {
        // Non-destructive compatibility repair.
    }
};
