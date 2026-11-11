<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{

    public static function log(
        $action,
        $module,
        $description=null
    )
    {

        AuditLog::create([

            'user_id'=>Auth::id(),

            'action'=>$action,

            'module'=>$module,

            'ip_address'=>Request::ip(),

            'description'=>$description

        ]);

    }

}
