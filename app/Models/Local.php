<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Local extends Model
{
    protected $table = 'locales';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'activo',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'id_local');
    }

    public function mapa(): HasOne
    {
        return $this->hasOne(LocalMapa::class, 'id_local');
    }

    public function mapaCeldas(): HasMany
    {
        return $this->hasMany(LocalMapaCelda::class, 'id_local');
    }

    public function mesas(): HasMany
    {
        return $this->hasMany(Mesa::class, 'id_local');
    }
}