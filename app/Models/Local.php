<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
