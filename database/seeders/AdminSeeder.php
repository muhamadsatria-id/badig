<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Password default untuk seluruh akun seed.
     * Memenuhi standar aplikasi: min 12 karakter, huruf besar, huruf kecil, angka.
     * Dioverride lewat .env: ADMIN_PASSWORD (Super Admin) dan UNIT_PASSWORD (Unit 1-4).
     */
    private const DEFAULT_PASSWORD = 'BadigAdmin2026!';

    /**
     * Satu akun per role. Pasangan role_id/unit_id harus persis seperti ini
     * agar lolos User::hasValidRoleUnitAssignment() saat login:
     * - Unit 1-4: unit_id wajib sama dengan role_id.
     * - Super Admin: unit_id wajib null (akses semua unit).
     */
    private const ACCOUNTS = [
        [
            'label' => 'Super Admin',
            'name' => 'Administrator',
            'name_env' => 'ADMIN_NAME',
            'email' => 'admin@badig.sardev.biz.id',
            'email_env' => 'ADMIN_EMAIL',
            'role_id' => User::ROLE_SUPER_ADMIN,
            'unit_id' => null,
        ],
        [
            'label' => 'Unit 1 (Ideologi Politik)',
            'name' => 'Admin Unit 1 (Ideologi Politik)',
            'email' => 'unit1@badig.sardev.biz.id',
            'email_env' => 'UNIT1_EMAIL',
            'role_id' => User::ROLE_UNIT_1,
            'unit_id' => 1,
        ],
        [
            'label' => 'Unit 2 (Ekonomi)',
            'name' => 'Admin Unit 2 (Ekonomi)',
            'email' => 'unit2@badig.sardev.biz.id',
            'email_env' => 'UNIT2_EMAIL',
            'role_id' => User::ROLE_UNIT_2,
            'unit_id' => 2,
        ],
        [
            'label' => 'Unit 3 (Sosial Budaya)',
            'name' => 'Admin Unit 3 (Sosial Budaya)',
            'email' => 'unit3@badig.sardev.biz.id',
            'email_env' => 'UNIT3_EMAIL',
            'role_id' => User::ROLE_UNIT_3,
            'unit_id' => 3,
        ],
        [
            'label' => 'Unit 4 (Keamanan Negara)',
            'name' => 'Admin Unit 4 (Keamanan Negara)',
            'email' => 'unit4@badig.sardev.biz.id',
            'email_env' => 'UNIT4_EMAIL',
            'role_id' => User::ROLE_UNIT_4,
            'unit_id' => 4,
        ],
    ];

    /**
     * Master unit 1-4 mengikuti data migrasi
     * upgrade_units_and_database_buronan. Jaring pengaman saja; migrasi
     * enforce_five_role_unit_model sudah menjamin keberadaan unit ini.
     */
    private const FIXED_UNITS = [
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

    public function run(): void
    {
        $adminPassword = $this->resolvePassword('ADMIN_PASSWORD');
        $unitPassword = $this->resolvePassword('UNIT_PASSWORD', $adminPassword);

        $this->ensureFixedUnits();

        foreach (self::ACCOUNTS as $account) {
            $roleId = (int) $account['role_id'];
            $unitId = $account['unit_id'] !== null ? (int) $account['unit_id'] : null;

            $email = Str::lower(trim($this->envValue($account['email_env'] ?? null, $account['email'])));
            $name = $this->envValue($account['name_env'] ?? null, $account['name']);
            $password = $roleId === User::ROLE_SUPER_ADMIN ? $adminPassword : $unitPassword;

            // Pola pencarian email sama dengan AuthController::processLogin().
            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

            if ($user) {
                $user->forceFill([
                    'role_id' => $roleId,
                    'unit_id' => $unitId,
                    'is_locked' => false,
                    'login_attempts' => 0,
                ])->save();

                $this->command?->info("[ada]    {$account['label']} → {$email} (password tidak diubah)");

                continue;
            }

            $user = new User([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role_id' => $roleId,
                'unit_id' => $unitId,
                'is_locked' => false,
                'login_attempts' => 0,
            ]);
            $user->email_verified_at = now();
            $user->save();

            $this->command?->info("[baru]   {$account['label']} → {$email} / {$password}");
        }

        $this->command?->info('Seed akun per role selesai. Kredensial dapat dioverride via .env (ADMIN_EMAIL, UNIT1_EMAIL..UNIT4_EMAIL, ADMIN_PASSWORD, UNIT_PASSWORD).');
    }

    /**
     * Ambil password dari .env; fallback ke default kuat.
     * Catatan: jika config di-cache, env() mengembalikan null dan default dipakai.
     */
    private function resolvePassword(string $envKey, ?string $fallback = null): string
    {
        return $this->envValue($envKey, $fallback ?? self::DEFAULT_PASSWORD);
    }

    /**
     * Baca env dengan aman; kunci kosong/ketiadaan jatuh ke nilai default.
     */
    private function envValue(?string $envKey, string $default): string
    {
        if ($envKey === null || $envKey === '') {
            return $default;
        }

        return env($envKey, null) ?: $default;
    }

    /**
     * Pastikan baris unit 1-4 tersedia (hanya jika belum ada).
     * Kolom difilter dengan Schema::hasColumn agar aman untuk instalasi lama.
     */
    private function ensureFixedUnits(): void
    {
        if (! Schema::hasTable('units')) {
            return;
        }

        foreach (self::FIXED_UNITS as $id => $values) {
            if (DB::table('units')->where('id', $id)->exists()) {
                continue;
            }

            $row = ['id' => $id, 'is_active' => true] + $values;
            $row = array_filter(
                $row,
                static fn (string $column): bool => Schema::hasColumn('units', $column),
                ARRAY_FILTER_USE_KEY
            );
            $row['created_at'] = now();
            $row['updated_at'] = now();

            try {
                DB::table('units')->insert($row);
            } catch (\Throwable $exception) {
                // Unique index (nama_unit/kode/slug) bisa bentrok pada instalasi lama;
                // unit tetap dipastikan oleh migrasi, jadi cukup warning.
                $this->command?->warn("Unit {$id} tidak dibuat: {$exception->getMessage()}");
            }
        }
    }
}
