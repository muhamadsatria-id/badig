<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modul aktif menggunakan unit tables + data_attachments/archive_files.
        // Sebagian instalasi lama tidak pernah memiliki tabel archives.
        if (! Schema::hasTable('archives')) {
            return;
        }

        foreach ([
            'tanggal_pembuatan_file' => fn (Blueprint $table) => $table->date('tanggal_pembuatan_file')->nullable(),
            'attachment_file' => fn (Blueprint $table) => $table->string('attachment_file')->nullable(),
            'unit_id' => fn (Blueprint $table) => $table->unsignedTinyInteger('unit_id')->nullable()->index(),
        ] as $column => $callback) {
            if (! Schema::hasColumn('archives', $column)) {
                Schema::table('archives', fn (Blueprint $table) => $callback($table));
            }
        }
    }

    public function down(): void
    {
        // Non-destructive legacy compatibility migration.
    }
};
