<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Unit;
use App\Models\User;
use App\Services\SecureMediaStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AdminITController extends Controller
{
    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengelola pengguna dan audit sistem.');
    }

    public function users()
    {
        $this->authorizeSuperAdmin();

        $users = User::with('unit')->latest()->paginate(12);
        $units = Unit::active()->whereBetween('id', [1, 4])->orderBy('id')->get();

        return view('admin.users', compact('users', 'units'));
    }

    public function storeUser(Request $request)
    {
        $this->authorizeSuperAdmin();
        $request->merge([
            'email' => mb_strtolower(trim((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'ascii', 'max:72', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
            'role_id' => ['required', 'integer', 'between:1,5'],
            'nrp' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:2000'],
        ]);

        if (User::query()->whereRaw('LOWER(email) = ?', [$data['email']])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Alamat email sudah digunakan.',
            ]);
        }

        $roleId = (int) $data['role_id'];
        $data['unit_id'] = $roleId <= 4 ? $roleId : null;
        $data['is_locked'] = false;
        $data['login_attempts'] = 0;

        $user = User::create($data);
        AuditLog::record('CREATE_USER', 'Membuat pengguna ID '.$user->id.' dengan '.$user->roleName().'.');

        return back()->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function toggleUser(User $user)
    {
        $this->authorizeSuperAdmin();
        abort_if($user->is(auth()->user()), 422, 'Akun sendiri tidak dapat dikunci.');

        if ($user->isSuperAdmin() && ! $user->is_locked && $this->activeSuperAdminCount() <= 1) {
            return back()->withErrors(['user' => 'Super Admin aktif terakhir tidak boleh dikunci.']);
        }

        $user->forceFill([
            'is_locked' => ! (bool) $user->is_locked,
            'login_attempts' => 0,
        ])->save();

        if ($user->is_locked) {
            $this->invalidateUserSessions($user->id);
        }

        AuditLog::record('TOGGLE_USER_LOCK', 'Mengubah status kunci pengguna ID '.$user->id.'.');

        return back()->with('success', 'Status akun berhasil diperbarui.');
    }

    public function destroyUser(User $user)
    {
        $this->authorizeSuperAdmin();
        abort_if($user->is(auth()->user()), 422, 'Akun sendiri tidak dapat dihapus.');

        if ($user->isSuperAdmin() && $this->superAdminCount() <= 1) {
            return back()->withErrors(['user' => 'Super Admin terakhir tidak boleh dihapus.']);
        }

        $photo = $user->photo;
        $originalEmail = $user->email;

        DB::transaction(function () use ($user, $originalEmail): void {
            AuditLog::record('DELETE_USER', 'Menonaktifkan dan menganonimkan pengguna ID '.$user->id.' ('.$originalEmail.').');

            $user->forceFill([
                'name' => 'Pengguna Dinonaktifkan #'.$user->id,
                'email' => 'deleted-'.$user->id.'-'.Str::lower(Str::random(12)).'@invalid.local',
                'password' => Str::random(64),
                'nrp' => null,
                'phone' => null,
                'address' => null,
                'photo' => null,
                'is_locked' => true,
                'login_attempts' => 0,
                'otp_code' => null,
                'otp_expires_at' => null,
                'remember_token' => null,
            ])->save();

            $this->invalidateUserSessions($user->id);
            $user->delete();
        });

        if ($photo) {
            app(SecureMediaStorage::class)->delete($photo);
        }

        return back()->with('success', 'Pengguna berhasil dinonaktifkan dan data pribadinya dianonimkan.');
    }

    public function auditTrail()
    {
        $this->authorizeSuperAdmin();

        $logs = AuditLog::with('user')->latest()->paginate(20);

        return view('admin.audit', compact('logs'));
    }

    private function invalidateUserSessions(int $userId): void
    {
        if (config('session.driver') === 'database' && Schema::hasTable(config('session.table', 'sessions'))) {
            DB::table(config('session.table', 'sessions'))->where('user_id', $userId)->delete();
        }
    }

    private function superAdminCount(): int
    {
        return User::where('role_id', User::ROLE_SUPER_ADMIN)->count();
    }

    private function activeSuperAdminCount(): int
    {
        return User::where('role_id', User::ROLE_SUPER_ADMIN)
            ->where('is_locked', false)
            ->count();
    }
}
