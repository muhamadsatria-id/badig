<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemBackup extends Model
{
    protected $fillable = ['nama_file', 'ukuran', 'status', 'operator_id'];

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}
