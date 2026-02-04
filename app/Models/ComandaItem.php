<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComandaItem extends Model
{
    protected $table = 'comanda_items';

    protected $fillable = [
        'id_comanda',
        'id_item',
        'nombre_snapshot',
        'precio_snapshot',
        'cantidad',
        'nota',
        'estado',
    ];

    protected $casts = [
        'id_comanda' => 'integer',
        'id_item' => 'integer',
        'precio_snapshot' => 'decimal:2',
        'cantidad' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function comanda(): BelongsTo
    {
        return $this->belongsTo(Comanda::class, 'id_comanda');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(CartaItem::class, 'id_item');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getTotalAttribute(): float
    {
        return (float)$this->precio_snapshot * (float)$this->cantidad;
    }

    public function getEsEditableAttribute(): bool
    {
        // Editable mientras no esté entregado o anulado
        return in_array($this->estado, ['pendiente', 'en_cocina'], true);
    }
}
