<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToUnit
{
    public static function bootBelongsToUnit(): void
    {
        static::creating(function ($model): void {
            $user = auth()->user();

            if ($user && ! $user->isSuperAdmin()) {
                $model->unit_id = $user->expectedUnitId();
            }
        });

        static::updating(function ($model): void {
            $user = auth()->user();

            if ($user && ! $user->isSuperAdmin()) {
                $model->unit_id = $user->expectedUnitId();
            }
        });

        static::addGlobalScope('unit-isolation', function (Builder $builder): void {
            $user = auth()->user();

            if ($user && ! $user->isSuperAdmin()) {
                $unitId = $user->expectedUnitId();

                if ($unitId !== null) {
                    $builder->where(
                        $builder->getModel()->qualifyColumn('unit_id'),
                        $unitId
                    );
                } else {
                    $builder->whereRaw('1 = 0');
                }
            }
        });
    }

    public function unit()
    {
        return $this->belongsTo(\App\Models\Unit::class);
    }
}
