<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $legacyUnitTables = [
        'unit1_ideology' => 1,
        'unit2_economy' => 2,
        'unit3_sosbud' => 3,
        'unit4_security_crim' => 4,
    ];

    public function up(): void
    {
        $this->ensureUnitsTable();
        $this->seedDefaultUnits();
        $this->ensureUserProfileColumns();
        $this->ensureLegacyUnitColumns();
    }

    private function ensureUnitsTable(): void
    {
        if (! Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->tinyIncrements('id');
                $table->string('nama_unit')->nullable()->unique();
                $table->string('kode', 20)->nullable()->unique();
                $table->string('kode_unit', 50)->nullable()->unique();
                $table->string('slug', 80)->nullable()->unique();
                $table->text('deskripsi')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });

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

    private function seedDefaultUnits(): void
    {
        if (! Schema::hasTable('units') || ! Schema::hasColumn('units', 'id')) {
            return;
        }

        $now = now();
        $defaults = [
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

        foreach ($defaults as $id => $unit) {
            $payload = [];

            foreach ($unit as $column => $value) {
                if (Schema::hasColumn('units', $column)) {
                    $payload[$column] = $value;
                }
            }

            if (Schema::hasColumn('units', 'is_active')) {
                $payload['is_active'] = true;
            }
            if (Schema::hasColumn('units', 'created_at')) {
                $payload['created_at'] = $now;
            }
            if (Schema::hasColumn('units', 'updated_at')) {
                $payload['updated_at'] = $now;
            }

            DB::table('units')->updateOrInsert(['id' => $id], $payload);
        }
    }

    private function ensureUserProfileColumns(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        foreach ([
            'nrp' => fn (Blueprint $table) => $table->string('nrp', 80)->nullable()->index(),
            'phone' => fn (Blueprint $table) => $table->string('phone', 50)->nullable(),
            'address' => fn (Blueprint $table) => $table->text('address')->nullable(),
            'photo' => fn (Blueprint $table) => $table->string('photo')->nullable(),
        ] as $column => $callback) {
            if (! Schema::hasColumn('users', $column)) {
                Schema::table('users', fn (Blueprint $table) => $callback($table));
            }
        }
    }

    private function ensureLegacyUnitColumns(): void
    {
        foreach ($this->legacyUnitTables as $tableName => $unitId) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            if (! Schema::hasColumn($tableName, 'unit_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedTinyInteger('unit_id')->nullable()->index();
                });
            }

            DB::table($tableName)->whereNull('unit_id')->update(['unit_id' => $unitId]);
        }
    }

    public function down(): void
    {
        // Non-destructive compatibility migration.
    }
};
