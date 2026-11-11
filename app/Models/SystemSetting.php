<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue($key)
    {
        return static::where(
            'key',
            $key
        )->value('value');
    }
}
