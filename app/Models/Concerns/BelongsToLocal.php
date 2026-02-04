<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToLocal
{
    protected static function bootBelongsToLocal(): void
    {
        static::addGlobalScope('local', function (Builder $builder) {
            // Solo aplicar si hay usuario y hay local_id seteado
            if (app()->bound('local_id')) {
                $builder->where($builder->getModel()->getTable().'.id_local', app('local_id'));
            }
        });

        static::creating(function (Model $model) {
            if (empty($model->id_local) && app()->bound('local_id')) {
                $model->id_local = app('local_id');
            }
        });
    }

    public function scopeForLocal(Builder $query, int $idLocal): Builder
    {
        return $query->where('id_local', $idLocal);
    }
}
