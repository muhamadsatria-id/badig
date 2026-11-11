<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const DUMMY_PASSWORD_HASH = '$2y$12$YVsgBym2lwx3lX2wK91osuCOGhpzIu10fnaLeJGT3zFrAR7wRJcim';

    public function home(): RedirectResponse
    {
        return auth()->check()
            ? redirect()->route('dashboard')
            : redirect()->route('login');
    }

    public function showLogin(): View
    {
        return view('auth.login', [
            'otpRequired' => session()->has('pending_auth_user_id'),
        ]);
    }

    public function processLogin(Request $request): RedirectResponse
    {
        if ($request->filled('otp')) {
            return $this->verifyOtp($request);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
        ]);

        $email = Str::lower(trim($credentials['email']));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        $passwordValid = Hash::check(
            $credentials['password'],
            $user?->password ?? self::DUMMY_PASSWORD_HASH
        );

        if (! $user || ! $passwordValid) {
            $this->registerFailedPassword($request, $user, $email);

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak sesuai.',
            ]);
        }

        if ($user->is_locked) {
            AuditLog::record('LOCKED_ACCOUNT_LOGIN_ATTEMPT', 'Percobaan login pada akun terkunci.', $user);

            throw ValidationException::withMessages([
                'email' => 'Akun tidak dapat digunakan. Hubungi Super Admin.',
            ]);
        }

        if (! $user->hasValidRoleUnitAssignment()) {
            AuditLog::record('INVALID_ROLE_UNIT_LOGIN', 'Login ditolak karena pasangan role dan unit tidak valid.', $user);

            throw ValidationException::withMessages([
                'email' => 'Konfigurasi akun tidak valid. Hubungi Super Admin.',
            ]);
        }

        $user->forceFill(['login_attempts' => 0])->save();
        RateLimiter::clear('failed-password:'.hash('sha256', $email).'|'.$request->ip());

        if (! config('security.auth.otp_enabled', false)) {
            return $this->completeLogin($request, $user);
        }

        return $this->issueOtp($request, $user);
    }

    private function registerFailedPassword(Request $request, ?User $user, string $email): void
    {
        $auditThreshold = max(1, (int) config('security.auth.failed_password_audit_threshold', 5));
        $key = 'failed-password:'.hash('sha256', $email).'|'.$request->ip();
        RateLimiter::hit($key, 900);
        $attempts = RateLimiter::attempts($key);

        if ($attempts !== 1 && $attempts % $auditThreshold !== 0) {
            return;
        }

        AuditLog::record(
            $attempts >= $auditThreshold ? 'LOGIN_FAILURE_THRESHOLD' : 'LOGIN_FAILED',
            $attempts >= $auditThreshold
                ? 'Ambang kegagalan login terlampaui; rate limiter menjadi kontrol utama agar akun tidak dapat dikunci oleh pihak luar.'
                : 'Kredensial login tidak valid.',
            $user
        );
    }

    private function issueOtp(Request $request, User $user): RedirectResponse
    {
        $otp = (string) random_int(100000, 999999);
        $ttl = max(1, min(30, (int) config('security.auth.otp_ttl_minutes', 5)));

        $user->forceFill([
            'otp_code' => $this->otpHash($otp),
            'otp_expires_at' => now()->addMinutes($ttl),
        ])->save();

        try {
            Mail::raw(
                "Kode verifikasi BADIG Anda: {$otp}\n\nKode berlaku selama {$ttl} menit. Abaikan email ini jika Anda tidak mencoba masuk.",
                static function ($message) use ($user): void {
                    $message->to($user->email, $user->name)
                        ->subject('Kode verifikasi login BADIG');
                }
            );
        } catch (\Throwable $exception) {
            $user->forceFill(['otp_code' => null, 'otp_expires_at' => null])->save();
            report($exception);

            throw ValidationException::withMessages([
                'email' => 'Kode verifikasi tidak dapat dikirim. Hubungi administrator sistem.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put([
            'pending_auth_user_id' => $user->id,
            'pending_auth_fingerprint' => $this->requestFingerprint($request),
        ]);

        AuditLog::record('OTP_ISSUED', 'Kode verifikasi login diterbitkan.', $user);

        return back()->with('otp_notice', 'Kode verifikasi telah dikirim ke email akun.');
    }

    private function verifyOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $rateKey = 'otp:'.$request->ip().':'.(string) session('pending_auth_user_id', 'guest');
        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            throw ValidationException::withMessages([
                'otp' => 'Terlalu banyak percobaan. Coba kembali setelah beberapa menit.',
            ]);
        }

        $userId = session('pending_auth_user_id');
        $user = $userId ? User::query()->find($userId) : null;
        $fingerprintValid = hash_equals(
            (string) session('pending_auth_fingerprint', ''),
            $this->requestFingerprint($request)
        );

        $valid = $user
            && ! $user->is_locked
            && $user->hasValidRoleUnitAssignment()
            && $fingerprintValid
            && $user->otp_code
            && $user->otp_expires_at
            && now()->lessThanOrEqualTo($user->otp_expires_at)
            && hash_equals($user->otp_code, $this->otpHash($data['otp']));

        if (! $valid) {
            RateLimiter::hit($rateKey, 600);
            AuditLog::record('OTP_FAILED', 'Verifikasi kode login gagal.', $user);

            throw ValidationException::withMessages([
                'otp' => 'Kode verifikasi salah atau sudah kedaluwarsa.',
            ]);
        }

        RateLimiter::clear($rateKey);
        $user->forceFill(['otp_code' => null, 'otp_expires_at' => null])->save();

        return $this->completeLogin($request, $user);
    }

    private function completeLogin(Request $request, User $user): RedirectResponse
    {
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget(['pending_auth_user_id', 'pending_auth_fingerprint']);
        $request->session()->put('last_activity_time', time());

        AuditLog::record('LOGIN', 'Login berhasil.', $user);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        AuditLog::record('LOGOUT', 'Pengguna keluar dari sistem.');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function otpHash(string $otp): string
    {
        return hash_hmac('sha256', $otp, (string) config('app.key'));
    }

    private function requestFingerprint(Request $request): string
    {
        return hash('sha256', $request->ip().'|'.Str::limit((string) $request->userAgent(), 500, ''));
    }
}
