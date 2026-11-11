<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {

        $logs = AuditLog::latest()
            ->with('user')
            ->get();

        return view(
            'super-admin.audit.index',
            compact('logs')
        );

    }
}
