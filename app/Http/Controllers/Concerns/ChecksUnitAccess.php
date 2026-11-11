<?php

namespace App\Http\Controllers\Concerns;

trait ChecksUnitAccess
{
    protected function checkUnitAccess(object $data): bool
    {
        $unitId = isset($data->unit_id) ? (int) $data->unit_id : null;

        abort_unless(
            auth()->user()?->canAccessUnitId($unitId),
            403,
            'Anda tidak memiliki akses terhadap data unit ini.'
        );

        return true;
    }
}
