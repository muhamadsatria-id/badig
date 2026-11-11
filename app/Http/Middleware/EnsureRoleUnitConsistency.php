<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleUnitConsistency
{
    /**
     * Reject accounts whose persisted role/unit pairing violates the five-role model.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if ($user->is_locked || ! $user->hasValidRoleUnitAssignment()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Akun terkunci atau konfigurasi role dan unit tidak valid. Hubungi Super Admin.');
        }

        return $next($request);
    }
}
