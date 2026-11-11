<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUnit;
use Illuminate\Database\Eloquent\Model;

class ArchiveFile extends Model
{
    use BelongsToUnit;

    protected $fillable = [
        'unit_id',
        'nama_file',
        'path_file',
        'extension',
        'size',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
