<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('units') || ! Schema::hasColumn('units', 'kode') || ! Schema::hasColumn('units', 'kode_unit')) {
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
        // Non-destructive compatibility migration.
    }
};
