<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mesa extends Model
{
    protected $table = 'mesas';

    protected $fillable = [
        'id_local',
        'nombre',
        'capacidad',
        'estado',
        'observacion',
        'pos_x',
        'pos_y',
        'ancho_mapa',
        'alto_mapa',
        'atendida_por',
        'atendida_at',
    ];

    protected $casts = [
        'id_local'      => 'integer',
        'capacidad'     => 'integer',
        'pos_x'         => 'integer',
        'pos_y'         => 'integer',
        'ancho_mapa'    => 'integer',
        'alto_mapa'     => 'integer',
        'atendida_por'  => 'integer',
        'atendida_at'   => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    public const ESTADO_LIBRE = 'libre';
    public const ESTADO_OCUPADA = 'ocupada';
    public const ESTADO_RESERVADA = 'reservada';
    public const ESTADO_FUERA_SERVICIO = 'fuera_servicio';

    public static function estados(): array
    {
        return [
            self::ESTADO_LIBRE,
            self::ESTADO_OCUPADA,
            self::ESTADO_RESERVADA,
            self::ESTADO_FUERA_SERVICIO,
        ];
    }

    public function local(): BelongsTo
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    public function mozoAtendiendo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atendida_por');
    }

    public function comandas(): HasMany
    {
        return $this->hasMany(Comanda::class, 'id_mesa');
    }

    public function estaPosicionadaEnMapa(): bool
    {
        return !is_null($this->pos_x) && !is_null($this->pos_y);
    }
}