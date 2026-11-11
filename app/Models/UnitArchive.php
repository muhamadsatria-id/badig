<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUnit;
use Illuminate\Database\Eloquent\Model;

class UnitArchive extends Model
{
    use BelongsToUnit;

    protected $fillable = [

        'unit_id',
        'judul',
        'file',

    ];
}
