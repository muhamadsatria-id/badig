<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'email')) {
            return;
        }

        DB::transaction(function (): void {
            $groups = DB::table('users')
                ->orderBy('id')
                ->get(['id', 'email'])
                ->groupBy(fn (object $user): string => Str::lower(trim((string) $user->email)));

            foreach ($groups as $normalized => $users) {
                if ($normalized === '') {
                    foreach ($users as $user) {
                        $this->quarantineEmail((int) $user->id, 'empty');
                    }

                    continue;
                }

                $keeper = $users->first();
                foreach ($users->slice(1) as $duplicate) {
                    $this->quarantineEmail((int) $duplicate->id, 'duplicate');
                }

                DB::table('users')->where('id', $keeper->id)->update([
                    'email' => mb_substr($normalized, 0, 255),
                ]);
            }

            if (Schema::hasTable('password_reset_tokens')) {
                DB::table('password_reset_tokens')->delete();
            }
        });
    }

    private function quarantineEmail(int $userId, string $reason): void
    {
        $updates = [
            'email' => $reason.'-'.$userId.'-'.Str::lower(Str::random(12)).'@invalid.local',
        ];

        if (Schema::hasColumn('users', 'is_locked')) {
            $updates['is_locked'] = true;
        }
        if (Schema::hasColumn('users', 'login_attempts')) {
            $updates['login_attempts'] = 0;
        }
        if (Schema::hasColumn('users', 'otp_code')) {
            $updates['otp_code'] = null;
        }
        if (Schema::hasColumn('users', 'otp_expires_at')) {
            $updates['otp_expires_at'] = null;
        }

        DB::table('users')->where('id', $userId)->update($updates);

        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            DB::table('sessions')->where('user_id', $userId)->delete();
        }
    }

    public function down(): void
    {
        // Canonicalization is intentionally not reversed because the previous
        // casing/duplicate ambiguity cannot be restored safely.
    }
};
