<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'id_local',
        'id_caja',
        'id_comanda',
        'id_mesa',
        'id_mozo',
        'estado',
        'subtotal',
        'descuento',
        'recargo',
        'propina',
        'total',
        'pagado_total',
        'vuelto',
        'nota',
        'sold_at',
    ];

    protected $casts = [
        'subtotal'     => 'decimal:2',
        'descuento'    => 'decimal:2',
        'recargo'      => 'decimal:2',
        'propina'      => 'decimal:2',
        'total'        => 'decimal:2',
        'pagado_total' => 'decimal:2',
        'vuelto'       => 'decimal:2',
        'sold_at'      => 'datetime',
    ];

    public function comanda()
    {
        return $this->belongsTo(Comanda::class, 'id_comanda');
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class, 'id_mesa');
    }

    public function mozo()
    {
        return $this->belongsTo(User::class, 'id_mozo');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_venta');
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }
}