<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnitIsolation
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->is_locked || ! $user->hasValidRoleUnitAssignment()) {
            abort(403, 'Konfigurasi role dan unit akun tidak valid.');
        }

        $unit = (string) $request->route('unit', '');
        if ($unit !== '' && preg_match('/^unit([1-4])$/', $unit, $matches)) {
            abort_unless(
                $user->canAccessUnitId((int) $matches[1]),
                403,
                'Unit ini berada di luar otorisasi Anda.'
            );
        }

        return $next($request);
    }
}
