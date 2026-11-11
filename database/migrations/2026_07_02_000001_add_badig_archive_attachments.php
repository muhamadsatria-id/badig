<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $unitTables = [
        'unit1_ideology',
        'unit2_economy',
        'unit3_sosbud',
        'unit4_security_crim',
    ];

    public function up(): void
    {
        foreach ($this->unitTables as $tableName) {
            if (Schema::hasTable($tableName)) {
                if (! Schema::hasColumn($tableName, 'tanggal_pembuatan')) {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->date('tanggal_pembuatan')->nullable()->index();
                    });
                }
                if (! Schema::hasColumn($tableName, 'pelapor_nama')) {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->string('pelapor_nama')->nullable()->index();
                    });
                }
                if (! Schema::hasColumn($tableName, 'pelapor_nrp')) {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->string('pelapor_nrp', 80)->nullable()->index();
                    });
                }
            }
        }

        if (! Schema::hasTable('data_attachments')) {
            Schema::create('data_attachments', function (Blueprint $table) {
                $table->id();
                $table->string('unit', 20)->index();
                $table->unsignedTinyInteger('unit_id')->index();
                $table->string('record_table', 80)->index();
                $table->unsignedBigInteger('record_id')->index();
                $table->string('original_name');
                $table->string('stored_name');
                $table->string('file_path');
                $table->string('extension', 20)->nullable()->index();
                $table->string('mime_type', 150)->nullable();
                $table->unsignedBigInteger('file_size')->default(0);
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['unit', 'record_table', 'record_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('data_attachments');

        foreach (array_reverse($this->unitTables) as $tableName) {
            if (Schema::hasTable($tableName)) {
                foreach (['pelapor_nrp', 'pelapor_nama', 'tanggal_pembuatan'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        Schema::table($tableName, function (Blueprint $table) use ($column) {
                            $table->dropColumn($column);
                        });
                    }
                }
            }
        }
    }
};
