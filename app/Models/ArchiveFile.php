<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToUnit;

class ArchiveFile extends Model
{
    use BelongsToUnit;

    protected $fillable = [
        'unit_id',
        'nama_file',
        'path_file',
        'extension',
        'size'
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

}

