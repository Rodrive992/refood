<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'id_local',
        'id_comanda',
        'id_mesa',
        'id_mozo',

        'estado',

        'subtotal',
        'descuento',
        'recargo',
        'total',
        'pagado_total',
        'vuelto',

        'nota',
        'sold_at',
    ];

    protected $casts = [
        'id_local' => 'integer',
        'id_comanda' => 'integer',
        'id_mesa' => 'integer',
        'id_mozo' => 'integer',

        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'recargo' => 'decimal:2',
        'total' => 'decimal:2',
        'pagado_total' => 'decimal:2',
        'vuelto' => 'decimal:2',

        'sold_at' => 'datetime',
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

    public function comanda(): BelongsTo
    {
        return $this->belongsTo(Comanda::class, 'id_comanda');
    }   

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class, 'id_mesa');
    }

    public function mozo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_mozo');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'id_venta');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getEsPagadaAttribute(): bool
    {
        return $this->estado === 'pagada';
    }

    public function getSaldoAttribute(): float
    {
        // total - pagado_total (nunca negativo)
        $saldo = (float)$this->total - (float)$this->pagado_total;
        return $saldo > 0 ? $saldo : 0.0;
    }
}
