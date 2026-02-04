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

        // NUEVO
        'atendida_por',
        'atendida_at',
    ];

    protected $casts = [
        'id_local'      => 'integer',
        'capacidad'     => 'integer',
        'atendida_por'  => 'integer',
        'atendida_at'   => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    // Estados recomendados
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

    // (Opcional) Relación si tenés tabla locales
    public function local(): BelongsTo
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    // NUEVO: quién está atendiendo la mesa (mozo/admin)
    public function mozoAtendiendo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atendida_por');
    }

    // (Opcional) comandas históricas de la mesa
    public function comandas(): HasMany
    {
        return $this->hasMany(Comanda::class, 'id_mesa');
    }
}
