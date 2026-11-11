<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToUnit;

class UnitArchive extends Model
{

    use BelongsToUnit;


    protected $fillable = [

        'unit_id',
        'judul',
        'file'

    ];

}
