<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'units';

    protected $fillable = [
        'nama_unit',
        'kode',
        'kode_unit',
        'slug',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope untuk mengambil unit aktif.
     *
     * Dibuat kompatibel dengan database lama:
     * - Jika kolom is_active ada, ambil yang is_active = 1 atau NULL.
     * - Jika kolom is_active tidak ada, kembalikan query apa adanya.
     */
    public function scopeActive($query)
    {
        try {
            if (Schema::hasColumn($this->getTable(), 'is_active')) {
                return $query->where(function ($q) {
                    $q->where('is_active', true)
                        ->orWhereNull('is_active');
                });
            }
        } catch (\Throwable $e) {
            // Saat migration/cache belum stabil, fallback tanpa filter.
        }

        return $query;
    }

    /**
     * Alias kode lama/baru.
     * Beberapa bagian sistem memakai kode, database lama memakai kode_unit.
     */
    public function getDisplayKodeAttribute(): string
    {
        return (string) ($this->attributes['kode'] ?? $this->attributes['kode_unit'] ?? '');
    }

    public function getKodeAttribute($value)
    {
        return $value ?: ($this->attributes['kode_unit'] ?? null);
    }

    public function getKodeUnitAttribute($value)
    {
        return $value ?: ($this->attributes['kode'] ?? null);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'unit_id');
    }

    public function databaseOrang()
    {
        return $this->hasMany(DatabaseOrang::class, 'unit_id');
    }
}
