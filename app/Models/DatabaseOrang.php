<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUnit;
use Illuminate\Database\Eloquent\Model;

class DatabaseOrang extends Model
{
    use BelongsToUnit;

    protected $table = 'database_orang';

    protected $fillable = [
        'unit_id', 'foto', 'kategori', 'status_dpo',
        'instansi', 'sifat_dokumen', 'jenis_dokumen', 'nomor_dpo', 'tanggal_dikeluarkan', 'tempat_dikeluarkan',
        'nama', 'nik', 'nrp', 'kewarganegaraan', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'pekerjaan', 'phone', 'email',
        'catatan_hukum', 'rumus_dactiloscopy',
        'alamat', 'alamat_alternatif',
        'tinggi_badan', 'berat_badan', 'rambut', 'bentuk_tubuh', 'warna_kulit', 'warna_mata', 'hidung', 'bibir', 'ciri_lainnya',
        'dugaan_tindak_pidana', 'melanggar_pasal', 'no_reg_kejahatan', 'keterangan_lain', 'analisis_deskripsi', 'keterangan',
        'nama_penyidik', 'jabatan_penyidik', 'no_hp_penyidik',
        'pejabat_penandatangan', 'jabatan_penandatangan', 'nrp_penandatangan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'tanggal_dikeluarkan' => 'date',
            'unit_id' => 'integer',
            'created_by' => 'integer',
        ];
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function attachments()
    {
        return $this->hasMany(DataAttachment::class, 'record_id')
            ->where('unit', 'buronan')
            ->where('record_table', $this->getTable())
            ->latest();
    }
}
