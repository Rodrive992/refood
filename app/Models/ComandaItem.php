<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComandaItem extends Model
{
    protected $table = 'comanda_items';

    protected $fillable = [
        'id_comanda',
        'pedido_numero',
        'id_item',
        'nombre_snapshot',
        'precio_snapshot',
        'cantidad',
        'nota',
        'estado',
        'impreso_cocina_at',
    ];

    protected $casts = [
        'id_comanda' => 'integer',
        'pedido_numero' => 'integer',
        'id_item' => 'integer',
        'precio_snapshot' => 'decimal:2',
        'cantidad' => 'decimal:2',
        'impreso_cocina_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function comanda(): BelongsTo
    {
        return $this->belongsTo(Comanda::class, 'id_comanda');
    }

    public function cartaItem(): BelongsTo
    {
        return $this->belongsTo(CartaItem::class, 'id_item');
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', '!=', 'anulado');
    }

    public function scopeDelPedido($query, int $pedidoNumero)
    {
        return $query->where('pedido_numero', max(1, $pedidoNumero));
    }

    public function scopeSinImprimirCocina($query)
    {
        return $query->whereNull('impreso_cocina_at');
    }

    public function getTotalAttribute(): float
    {
        return (float) $this->precio_snapshot * (float) $this->cantidad;
    }

    public function getFueImpresoEnCocinaAttribute(): bool
    {
        return !empty($this->impreso_cocina_at);
    }
}