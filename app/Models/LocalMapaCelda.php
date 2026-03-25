<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocalMapaCelda extends Model
{
    protected $table = 'local_mapa_celdas';

    public const TIPO_PARED = 'pared';

    protected $fillable = [
        'id_local',
        'x',
        'y',
        'tipo',
    ];

    protected $casts = [
        'id_local'   => 'integer',
        'x'          => 'integer',
        'y'          => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function local(): BelongsTo
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    public function mapa(): BelongsTo
    {
        return $this->belongsTo(LocalMapa::class, 'id_local', 'id_local');
    }

    public function esPared(): bool
    {
        return $this->tipo === self::TIPO_PARED;
    }
}