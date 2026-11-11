<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUnit;

use Illuminate\Database\Eloquent\Model;

class Unit2Economy extends Model
{
    use BelongsToUnit;

    protected $table = 'unit2_economy';

    protected $fillable = [
        'unit_id', 'tanggal_pembuatan', 'pelapor_nama', 'pelapor_nrp', 'kategori', 'nama_aset',
        'district_id', 'village_id', 'alamat', 'kapasitas', 'koordinat_lat', 'koordinat_lng',
        'operator_id',
    ];

    protected function casts(): array
    {
        return [
            'unit_id' => 'integer',
            'koordinat_lat' => 'decimal:8',
            'koordinat_lng' => 'decimal:8',
            'tanggal_pembuatan' => 'date',
        ];
    }

    public function unit(){ return $this->belongsTo(Unit::class); }
    public function district(){ return $this->belongsTo(District::class); }
    public function village(){ return $this->belongsTo(Village::class); }
    public function operator(){ return $this->belongsTo(User::class, 'operator_id')->withTrashed(); }
}
