<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->normaliseUnitsSafely();
        $this->upgradeDatabaseBuronan();
        $this->upgradeAttachments();
    }

    private function normaliseUnitsSafely(): void
    {
        if (! Schema::hasTable('units') || ! Schema::hasColumn('units', 'id')) {
            return;
        }

        $units = [
            1 => [
                'nama_unit' => 'Ideologi Politik',
                'kode' => 'IP',
                'kode_unit' => 'IP',
                'slug' => 'unit1',
                'deskripsi' => 'Unit bidang ideologi dan politik.',
            ],
            2 => [
                'nama_unit' => 'Ekonomi',
                'kode' => 'EKO',
                'kode_unit' => 'EKO',
                'slug' => 'unit2',
                'deskripsi' => 'Unit bidang ekonomi dan objek vital ekonomi.',
            ],
            3 => [
                'nama_unit' => 'Sosial Budaya',
                'kode' => 'SOSBUD',
                'kode_unit' => 'SOSBUD',
                'slug' => 'unit3',
                'deskripsi' => 'Unit bidang sosial dan budaya.',
            ],
            4 => [
                'nama_unit' => 'Keamanan Negara',
                'kode' => 'KAMNEG',
                'kode_unit' => 'KAMNEG',
                'slug' => 'unit4',
                'deskripsi' => 'Unit bidang keamanan negara.',
            ],
        ];

        $identityColumns = array_values(array_filter(
            ['nama_unit', 'kode', 'kode_unit', 'slug'],
            fn (string $column) => Schema::hasColumn('units', $column)
        ));

        DB::transaction(function () use ($units, $identityColumns) {
            // Step 1: free every canonical row from old unique values.
            foreach (array_keys($units) as $id) {
                if (! DB::table('units')->where('id', $id)->exists()) {
                    continue;
                }

                $temporary = [];
                foreach ($identityColumns as $column) {
                    $temporary[$column] = $this->temporaryValue($column, $id);
                }
                if (Schema::hasColumn('units', 'updated_at')) {
                    $temporary['updated_at'] = now();
                }

                DB::table('units')->where('id', $id)->update($temporary);
            }

            // Step 2: rename non-canonical rows that still use a desired unique value.
            foreach ($units as $canonicalId => $payload) {
                foreach ($identityColumns as $column) {
                    $desired = $payload[$column] ?? null;
                    if ($desired === null) {
                        continue;
                    }

                    $conflictingIds = DB::table('units')
                        ->where($column, $desired)
                        ->where('id', '<>', $canonicalId)
                        ->pluck('id');

                    foreach ($conflictingIds as $conflictingId) {
                        DB::table('units')
                            ->where('id', $conflictingId)
                            ->update([
                                $column => $this->legacyValue($column, (int) $conflictingId),
                                ...(Schema::hasColumn('units', 'updated_at') ? ['updated_at' => now()] : []),
                            ]);
                    }
                }
            }

            // Step 3: write the four canonical units.
            foreach ($units as $id => $payload) {
                $update = [];
                foreach ($payload as $column => $value) {
                    if (Schema::hasColumn('units', $column)) {
                        $update[$column] = $value;
                    }
                }
                if (Schema::hasColumn('units', 'is_active')) {
                    $update['is_active'] = true;
                }
                if (Schema::hasColumn('units', 'created_at')) {
                    $update['created_at'] = DB::raw('COALESCE(created_at, CURRENT_TIMESTAMP)');
                }
                if (Schema::hasColumn('units', 'updated_at')) {
                    $update['updated_at'] = now();
                }

                if (DB::table('units')->where('id', $id)->exists()) {
                    DB::table('units')->where('id', $id)->update($update);
                } else {
                    DB::table('units')->insert(['id' => $id] + $this->materialiseInsertValues($update));
                }
            }
        });
    }

    private function temporaryValue(string $column, int $id): string
    {
        $suffix = substr(hash('sha256', "badig-temp-{$column}-{$id}"), 0, 8);

        return match ($column) {
            'nama_unit' => "TEMP UNIT {$id} {$suffix}",
            'kode' => "T{$id}{$suffix}",
            'kode_unit' => "TU{$id}{$suffix}",
            'slug' => "temp-unit-{$id}-{$suffix}",
            default => "TEMP-{$id}-{$suffix}",
        };
    }

    private function legacyValue(string $column, int $id): string
    {
        $suffix = substr(hash('sha256', "badig-legacy-{$column}-{$id}"), 0, 8);

        return match ($column) {
            'nama_unit' => "Legacy Unit {$id} {$suffix}",
            'kode' => "L{$id}{$suffix}",
            'kode_unit' => "LEG{$id}{$suffix}",
            'slug' => "legacy-unit-{$id}-{$suffix}",
            default => "LEGACY-{$id}-{$suffix}",
        };
    }

    private function materialiseInsertValues(array $values): array
    {
        foreach ($values as $key => $value) {
            if ($value instanceof Expression) {
                $values[$key] = now();
            }
        }

        return $values;
    }

    private function upgradeDatabaseBuronan(): void
    {
        if (! Schema::hasTable('database_orang')) {
            return;
        }

        $columns = [
            'foto' => fn (Blueprint $table) => $table->string('foto')->nullable(),
            'kategori' => fn (Blueprint $table) => $table->string('kategori', 120)->nullable()->index(),
            'status_dpo' => fn (Blueprint $table) => $table->string('status_dpo', 80)->nullable()->index(),
            'instansi' => fn (Blueprint $table) => $table->string('instansi')->nullable(),
            'sifat_dokumen' => fn (Blueprint $table) => $table->string('sifat_dokumen', 100)->nullable(),
            'jenis_dokumen' => fn (Blueprint $table) => $table->string('jenis_dokumen', 100)->nullable(),
            'nomor_dpo' => fn (Blueprint $table) => $table->string('nomor_dpo', 120)->nullable()->index(),
            'tanggal_dikeluarkan' => fn (Blueprint $table) => $table->date('tanggal_dikeluarkan')->nullable()->index(),
            'tempat_dikeluarkan' => fn (Blueprint $table) => $table->string('tempat_dikeluarkan')->nullable(),
            'kewarganegaraan' => fn (Blueprint $table) => $table->string('kewarganegaraan', 120)->nullable(),
            'catatan_hukum' => fn (Blueprint $table) => $table->text('catatan_hukum')->nullable(),
            'rumus_dactiloscopy' => fn (Blueprint $table) => $table->string('rumus_dactiloscopy')->nullable(),
            'alamat_alternatif' => fn (Blueprint $table) => $table->text('alamat_alternatif')->nullable(),
            'tinggi_badan' => fn (Blueprint $table) => $table->string('tinggi_badan', 50)->nullable(),
            'berat_badan' => fn (Blueprint $table) => $table->string('berat_badan', 50)->nullable(),
            'rambut' => fn (Blueprint $table) => $table->string('rambut', 120)->nullable(),
            'bentuk_tubuh' => fn (Blueprint $table) => $table->string('bentuk_tubuh', 120)->nullable(),
            'warna_kulit' => fn (Blueprint $table) => $table->string('warna_kulit', 120)->nullable(),
            'warna_mata' => fn (Blueprint $table) => $table->string('warna_mata', 120)->nullable(),
            'hidung' => fn (Blueprint $table) => $table->string('hidung', 120)->nullable(),
            'bibir' => fn (Blueprint $table) => $table->string('bibir', 120)->nullable(),
            'ciri_lainnya' => fn (Blueprint $table) => $table->text('ciri_lainnya')->nullable(),
            'dugaan_tindak_pidana' => fn (Blueprint $table) => $table->text('dugaan_tindak_pidana')->nullable(),
            'melanggar_pasal' => fn (Blueprint $table) => $table->text('melanggar_pasal')->nullable(),
            'no_reg_kejahatan' => fn (Blueprint $table) => $table->string('no_reg_kejahatan', 120)->nullable()->index(),
            'keterangan_lain' => fn (Blueprint $table) => $table->text('keterangan_lain')->nullable(),
            'analisis_deskripsi' => fn (Blueprint $table) => $table->longText('analisis_deskripsi')->nullable(),
            'nama_penyidik' => fn (Blueprint $table) => $table->string('nama_penyidik')->nullable(),
            'jabatan_penyidik' => fn (Blueprint $table) => $table->string('jabatan_penyidik')->nullable(),
            'no_hp_penyidik' => fn (Blueprint $table) => $table->string('no_hp_penyidik', 50)->nullable(),
            'pejabat_penandatangan' => fn (Blueprint $table) => $table->string('pejabat_penandatangan')->nullable(),
            'jabatan_penandatangan' => fn (Blueprint $table) => $table->string('jabatan_penandatangan')->nullable(),
            'nrp_penandatangan' => fn (Blueprint $table) => $table->string('nrp_penandatangan', 80)->nullable(),
        ];

        foreach ($columns as $column => $callback) {
            if (! Schema::hasColumn('database_orang', $column)) {
                Schema::table('database_orang', fn (Blueprint $table) => $callback($table));
            }
        }
    }

    private function upgradeAttachments(): void
    {
        if (! Schema::hasTable('data_attachments')) {
            return;
        }

        if (! Schema::hasColumn('data_attachments', 'attachment_role')) {
            Schema::table('data_attachments', function (Blueprint $table) {
                $table->string('attachment_role', 80)->nullable()->index();
            });
        }

        if (! Schema::hasColumn('data_attachments', 'description')) {
            Schema::table('data_attachments', function (Blueprint $table) {
                $table->string('description')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Deliberately non-destructive.
    }
};
