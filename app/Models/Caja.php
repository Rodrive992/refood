<?php
// app/Models/Caja.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caja extends Model
{
    protected $table = 'cajas';

    protected $fillable = [
        'id_local',
        'turno',
        'fecha',

        'estado', // abierta | cerrada

        'efectivo_apertura',
        'ingreso_efectivo',
        'salida_efectivo',
        'efectivo_turno',

        'abierta_at',
        'cerrada_at',
        'abierta_por',
        'cerrada_por',

        'observacion',
    ];

    protected $casts = [
        'id_local' => 'integer',
        'turno'    => 'integer',
        'fecha'    => 'date',

        'efectivo_apertura' => 'decimal:2',
        'ingreso_efectivo'  => 'decimal:2',
        'salida_efectivo'   => 'decimal:2',
        'efectivo_turno'    => 'decimal:2',

        'abierta_at' => 'datetime',
        'cerrada_at' => 'datetime',

        'abierta_por' => 'integer',
        'cerrada_por' => 'integer',

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

    public function usuarioApertura(): BelongsTo
    {
        return $this->belongsTo(User::class, 'abierta_por');
    }

    public function usuarioCierre(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrada_por');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'id_caja');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(CajaMovimiento::class, 'id_caja');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeAbierta($query)
    {
        return $query->where('estado', 'abierta');
    }

    /*
    |--------------------------------------------------------------------------
    | Estado
    |--------------------------------------------------------------------------
    */

    public function getEstaAbiertaAttribute(): bool
    {
        return $this->estado === 'abierta';
    }

    public function getEstaCerradaAttribute(): bool
    {
        return $this->estado === 'cerrada';
    }

    /*
    |--------------------------------------------------------------------------
    | Totales (SQL optimizado)
    |--------------------------------------------------------------------------
    */

    /**
     * ✅ Efectivo BRUTO cobrado (sum(pagos.monto) tipo efectivo)
     */
    public function totalVentasEfectivoBruto(): float
    {
        return (float) Pago::query()
            ->join('ventas', 'ventas.id', '=', 'pagos.id_venta')
            ->where('ventas.id_caja', $this->id)
            ->where('ventas.estado', 'pagada')
            ->where('pagos.tipo', 'efectivo')
            ->sum('pagos.monto');
    }

    /**
     * ✅ Vuelto total entregado (sale del cajón)
     */
    public function totalVuelto(): float
    {
        return (float) Venta::query()
            ->where('id_caja', $this->id)
            ->where('estado', 'pagada')
            ->sum('vuelto');
    }

    /**
     * ✅ Efectivo NETO que queda en el cajón por ventas
     * (efectivo cobrado - vuelto entregado)
     */
    public function totalVentasEfectivoNeto(): float
    {
        $bruto  = $this->totalVentasEfectivoBruto();
        $vuelto = $this->totalVuelto();

        return (float) max(0, $bruto - $vuelto);
    }

    public function totalIngresos(): float
    {
        return (float) $this->movimientos()
            ->where('tipo', 'ingreso')
            ->sum('monto');
    }

    public function totalSalidas(): float
    {
        return (float) $this->movimientos()
            ->where('tipo', 'salida')
            ->sum('monto');
    }

    public function calcularEfectivoTurno(): float
    {
        return (float) $this->efectivo_apertura
            + $this->totalVentasEfectivoNeto()
            + $this->totalIngresos()
            - $this->totalSalidas();
    }

    /**
     * Cachea en columnas: ingreso_efectivo, salida_efectivo, efectivo_turno
     */
    public function refreshTotalesCache(): void
    {
        $ing = $this->totalIngresos();
        $sal = $this->totalSalidas();

        $this->ingreso_efectivo = $ing;
        $this->salida_efectivo  = $sal;

        $this->efectivo_turno = (float)$this->efectivo_apertura
            + (float)$this->totalVentasEfectivoNeto()
            + (float)$ing
            - (float)$sal;

        $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers de Turno
    |--------------------------------------------------------------------------
    */

    public static function cajaAbiertaDelLocal(int $localId): ?self
    {
        return self::query()
            ->where('id_local', $localId)
            ->where('estado', 'abierta')
            ->latest('id')
            ->first();
    }

    public static function nextTurno(int $localId): int
    {
        $last = self::query()
            ->where('id_local', $localId)
            ->max('turno');

        return (int)($last ?? 0) + 1;
    }

    public static function efectivoAperturaSugerido(int $localId): float
    {
        $last = self::query()
            ->where('id_local', $localId)
            ->where('estado', 'cerrada')
            ->latest('id')
            ->first();

        return (float) ($last->efectivo_turno ?? 0);
    }
}