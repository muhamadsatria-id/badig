<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kode unit resmi yang dipakai sebagai prefix nomor rekam dokumen.
     * Sebelumnya IP/EKO, kini lebih deskriptif: IDPOL/EKON.
     */
    private const CODES = [
        1 => 'IDPOL',
        2 => 'EKON',
    ];

    private const PREVIOUS_CODES = [
        1 => 'IP',
        2 => 'EKO',
    ];

    public function up(): void
    {
        $this->syncCodes(self::CODES);
    }

    public function down(): void
    {
        $this->syncCodes(self::PREVIOUS_CODES);
    }

    /**
     * Perbarui kode unit pada kolom kode/kode_unit (sesuai kolom yang tersedia).
     */
    private function syncCodes(array $codes): void
    {
        if (! Schema::hasTable('units')) {
            return;
        }

        foreach ($codes as $id => $kode) {
            if (! DB::table('units')->where('id', $id)->exists()) {
                continue;
            }

            $row = array_filter(
                ['kode' => $kode, 'kode_unit' => $kode],
                static fn (string $column): bool => Schema::hasColumn('units', $column),
                ARRAY_FILTER_USE_KEY
            );

            if ($row !== []) {
                DB::table('units')->where('id', $id)->update($row);
            }
        }
    }
};
