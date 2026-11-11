<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')
            || ! Schema::hasColumn('users', 'role_id')
            || ! Schema::hasColumn('users', 'unit_id')) {
            return;
        }

        DB::transaction(function (): void {
            $this->ensureFixedUnits();

            foreach ([1, 2, 3, 4] as $roleId) {
                DB::table('users')
                    ->where('role_id', $roleId)
                    ->update(['unit_id' => $roleId]);
            }

            DB::table('users')
                ->where('role_id', 5)
                ->update(['unit_id' => null]);

            $invalid = ['unit_id' => null];
            if (Schema::hasColumn('users', 'is_locked')) {
                $invalid['is_locked'] = true;
            }

            DB::table('users')
                ->whereNotIn('role_id', [1, 2, 3, 4, 5])
                ->update($invalid);
        });
    }

    private function ensureFixedUnits(): void
    {
        if (! Schema::hasTable('units')) {
            return;
        }

        foreach ([1, 2, 3, 4] as $id) {
            $insert = [];
            $insertValues = [
                'id' => $id,
                'nama_unit' => 'Unit '.$id,
                'kode' => 'UNIT-'.$id,
                'kode_unit' => 'UNIT-'.$id,
                'slug' => 'unit'.$id,
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ];

            foreach ($insertValues as $column => $value) {
                if (Schema::hasColumn('units', $column)) {
                    $insert[$column] = $value;
                }
            }

            if (! DB::table('units')->where('id', $id)->exists()) {
                DB::table('units')->insert($insert);
                continue;
            }

            $update = [];
            foreach (['slug' => 'unit'.$id, 'is_active' => true, 'updated_at' => now()] as $column => $value) {
                if (Schema::hasColumn('units', $column)) {
                    $update[$column] = $value;
                }
            }

            if ($update !== []) {
                DB::table('units')->where('id', $id)->update($update);
            }
        }
    }

    public function down(): void
    {
        // Non-destructive by design. Restoring the former cross-unit role model
        // requires a reviewed data migration and must not happen automatically.
    }
};
