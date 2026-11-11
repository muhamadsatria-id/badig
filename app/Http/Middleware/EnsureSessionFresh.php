<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionFresh
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = $request->user();

            if (! $user || $user->is_locked || ! $user->hasValidRoleUnitAssignment()) {
                AuditLog::record('SESSION_REVOKED', 'Sesi dicabut karena akun terkunci atau konfigurasi role-unit tidak valid.', $user);
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Sesi tidak lagi valid. Hubungi Super Admin bila akses masih diperlukan.',
                ]);
            }

            $last = session('last_activity_time');
            $limit = ((int) config('session.lifetime', 30)) * 60;

            if ($last && (time() - (int) $last) > $limit) {
                AuditLog::record('SESSION_TIMEOUT', 'Sesi otomatis berakhir karena tidak aktif.');
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors(['email' => 'Sesi berakhir karena tidak aktif selama '.config('session.lifetime').' menit. Silakan login ulang.']);
            }

            session(['last_activity_time' => time()]);
        }

        return $next($request);
    }
}
