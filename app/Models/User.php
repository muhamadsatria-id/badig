<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_UNIT_1 = 1;

    public const ROLE_UNIT_2 = 2;

    public const ROLE_UNIT_3 = 3;

    public const ROLE_UNIT_4 = 4;

    public const ROLE_SUPER_ADMIN = 5;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'unit_id',
        'nrp',
        'phone',
        'address',
        'photo',
        'is_locked',
        'login_attempts',
        'otp_code',
        'otp_expires_at',
    ];

    protected $hidden = ['password', 'remember_token', 'otp_code'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_locked' => 'boolean',
            'otp_expires_at' => 'datetime',
            'role_id' => 'integer',
            'unit_id' => 'integer',
        ];
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function isSuperAdmin(): bool
    {
        return (int) $this->role_id === self::ROLE_SUPER_ADMIN;
    }

    public function isUnitRole(): bool
    {
        return in_array((int) $this->role_id, [
            self::ROLE_UNIT_1,
            self::ROLE_UNIT_2,
            self::ROLE_UNIT_3,
            self::ROLE_UNIT_4,
        ], true);
    }

    public function expectedUnitId(): ?int
    {
        return $this->isUnitRole() ? (int) $this->role_id : null;
    }

    public function hasValidRoleUnitAssignment(): bool
    {
        if ($this->isSuperAdmin()) {
            return $this->unit_id === null;
        }

        return $this->isUnitRole()
            && (int) $this->unit_id === $this->expectedUnitId();
    }

    public function roleName(): string
    {
        return match ((int) $this->role_id) {
            self::ROLE_UNIT_1 => 'Role Unit 1',
            self::ROLE_UNIT_2 => 'Role Unit 2',
            self::ROLE_UNIT_3 => 'Role Unit 3',
            self::ROLE_UNIT_4 => 'Role Unit 4',
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            default => 'Tidak Diketahui',
        };
    }

    public function unitName(): string
    {
        if ($this->isSuperAdmin()) {
            return 'Semua Unit';
        }

        if (! $this->unit_id) {
            return '-';
        }

        if ($this->relationLoaded('unit') && $this->unit) {
            return $this->unit->nama_unit;
        }

        try {
            if (Schema::hasTable('units')) {
                $unit = Unit::query()->find($this->unit_id);
                if ($unit) {
                    return $unit->nama_unit;
                }
            }
        } catch (\Throwable) {
            // Fallback is used while migrations are not available.
        }

        return match ((int) $this->unit_id) {
            1 => 'Unit 1',
            2 => 'Unit 2',
            3 => 'Unit 3',
            4 => 'Unit 4',
            default => '-',
        };
    }

    public function canAccessUnitId(?int $unitId): bool
    {
        if (! $unitId || ! $this->hasValidRoleUnitAssignment()) {
            return false;
        }

        return $this->isSuperAdmin()
            || (int) $this->unit_id === (int) $unitId;
    }

    public function canWriteUnitId(?int $unitId): bool
    {
        return $this->canAccessUnitId($unitId);
    }

    public function canAccessDatabaseOrang(): bool
    {
        return $this->isSuperAdmin() || $this->canAccessUnitId(3);
    }
}
