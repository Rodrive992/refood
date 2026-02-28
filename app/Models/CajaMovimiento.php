<?php
// app/Models/CajaMovimiento.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CajaMovimiento extends Model
{
    protected $table = 'caja_movimientos';

    protected $fillable = [
        'id_local',
        'id_caja',
        'id_user',
        'tipo',       // ingreso | salida
        'monto',
        'concepto',
        'movido_at',
    ];

    protected $casts = [
        'id_local' => 'integer',
        'id_caja'  => 'integer',
        'id_user'  => 'integer',

        'monto'    => 'decimal:2',
        'movido_at'=> 'datetime',

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function local(): BelongsTo
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getEsIngresoAttribute(): bool
    {
        return $this->tipo === 'ingreso';
    }

    public function getEsSalidaAttribute(): bool
    {
        return $this->tipo === 'salida';
    }
}