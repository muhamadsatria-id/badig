<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if ($user->is_locked || ! $user->hasValidRoleUnitAssignment()) {
            abort(403, 'Konfigurasi role dan unit akun tidak valid.');
        }

        $allowedRoles = array_values(array_unique(array_map('intval', $roles)));

        if (! in_array((int) $user->role_id, $allowedRoles, true)) {
            AuditLog::record(
                'ILLEGAL_ACCESS_ATTEMPT',
                'Akses role ditolak pada route '.$request->route()?->getName().'.',
                $user
            );

            abort(403, 'Anda tidak memiliki akses ke modul ini.');
        }

        return $next($request);
    }
}
