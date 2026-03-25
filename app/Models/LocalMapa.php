<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocalMapa extends Model
{
    protected $table = 'local_mapas';

    protected $fillable = [
        'id_local',
        'filas',
        'columnas',
        'caja_x',
        'caja_y',
    ];

    protected $casts = [
        'id_local'   => 'integer',
        'filas'      => 'integer',
        'columnas'   => 'integer',
        'caja_x'     => 'integer',
        'caja_y'     => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function local(): BelongsTo
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    public function celdas(): HasMany
    {
        return $this->hasMany(LocalMapaCelda::class, 'id_local', 'id_local');
    }

    public function mesas(): HasMany
    {
        return $this->hasMany(Mesa::class, 'id_local', 'id_local');
    }
}