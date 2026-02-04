<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Comanda extends Model
{
    protected $table = 'comandas';

    protected $fillable = [
        'id_local',
        'id_mesa',
        'id_mozo',

        'estado',

        // NUEVO: cuenta solicitada
        'cuenta_solicitada',
        'cuenta_solicitada_at',
        'cuenta_solicitada_por',
        'cuenta_solicitada_nota',

        // NUEVO: total estimado y estado caja
        'total_estimado',
        'estado_caja',

        'observacion',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'id_local' => 'integer',
        'id_mesa' => 'integer',
        'id_mozo' => 'integer',

        // NUEVO
        'cuenta_solicitada' => 'boolean',
        'cuenta_solicitada_at' => 'datetime',
        'cuenta_solicitada_por' => 'integer',
        'total_estimado' => 'decimal:2',

        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
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

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class, 'id_mesa');
    }

    public function mozo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_mozo');
    }

    // NUEVO: quién tocó "Solicitar cuenta"
    public function cuentaSolicitadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cuenta_solicitada_por');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ComandaItem::class, 'id_comanda');
    }

    /**
     * Venta asociada (si existe)
     */
    public function venta(): HasOne
    {
        return $this->hasOne(Venta::class, 'id_comanda');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes útiles
    |--------------------------------------------------------------------------
    */

    public function scopeActivas($query)
    {
        return $query->whereIn('estado', [
            'abierta',
            'en_cocina',
            'lista',
            'entregada',
            'cerrando',
        ]);
    }

    public function scopeCerradas($query)
    {
        return $query->whereIn('estado', ['cerrada', 'anulada']);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getEsCobrableAttribute(): bool
    {
        return !in_array($this->estado, ['cerrada', 'anulada'], true);
    }

    public function getSubtotalAttribute(): float
    {
        // Calculado por snapshot
        return (float) $this->items->sum(function ($item) {
            return (float) $item->precio_snapshot * (float) $item->cantidad;
        });
    }

    public function getCantidadItemsAttribute(): int
    {
        return (int) $this->items->sum('cantidad');
    }

    public function getTieneItemsPendientesAttribute(): bool
    {
        return $this->items()
            ->whereIn('estado', ['pendiente', 'en_cocina'])
            ->exists();
    }
}
