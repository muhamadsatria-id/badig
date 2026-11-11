<?php

namespace App\Http\Controllers\Concerns;

use App\Services\AuditService;

trait HasAuditLog
{
    protected function audit(
        $action,
        $module,
        $description = null
    ) {

        AuditService::log(
            $action,
            $module,
            $description
        );

    }
}
