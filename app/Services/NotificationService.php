<?php

namespace App\Services;

use App\Models\User;

class NotificationService
{
    public static function notifySuperAdmin($message)
    {
        return User::where(
            'role_id',
            1
        )->get();
    }
}
