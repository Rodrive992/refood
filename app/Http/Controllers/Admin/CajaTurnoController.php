<?php
// app/Http/Controllers/Admin/CajaTurnoController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\CajaMovimiento;
use App\Models\Comanda;
use App\Models\Mesa;
use App\Models\Pago;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CajaTurnoController extends Controller
{
    private function localId(): int
    {
        $idLocal = auth()->user()->id_local ?? null;
        abort_if(empty($idLocal), 403, 'Tu usuario no tiene un local asignado.');
        return (int) $idLocal;
    }

    private function cajaAbiertaQuery(int $localId)
    {
        return Caja::query()
            ->where('id_local', $localId)
            ->where('estado', 'abierta');
    }

    private function ultimaCajaCerrada(int $localId): ?Caja
    {
        return Caja::query()
            ->where('id_local', $localId)
            ->where('estado', 'cerrada')
            ->orderByDesc('id')
            ->first();
    }

    public function abrir(Request $request)
    {
        $localId = $this->localId();
        $userId  = (int) auth()->id();

        $data = $request->validate([
            'observacion'    => ['nullable', 'string', 'max:255'],
            'ajuste_ingreso' => ['nullable', 'numeric', 'min:0'],
            'ajuste_salida'  => ['nullable', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($localId, $userId, $data) {

            $ya = $this->cajaAbiertaQuery($localId)->lockForUpdate()->first();
            if ($ya) {
                return back()->with('error', 'Ya existe un turno de caja abierto.');
            }

            $ultima = Caja::query()
                ->where('id_local', $localId)
                ->orderByDesc('turno')
                ->lockForUpdate()
                ->first();

            $turnoSiguiente = $ultima ? ((int)$ultima->turno + 1) : 1;

            $ultimaCerrada = $this->ultimaCajaCerrada($localId);
            $efectivoApertura = $ultimaCerrada ? (float) $ultimaCerrada->efectivo_turno : 0.0;

            $ajIng = (float)($data['ajuste_ingreso'] ?? 0);
            $ajSal = (float)($data['ajuste_salida'] ?? 0);

            if ($ajIng > 0 && $ajSal > 0) {
                return back()->with('error', 'Usá solo ingreso o salida de ajuste, no ambos.');
            }

            $ingresoInicial = $ajIng;
            $salidaInicial  = $ajSal;

            $caja = Caja::create([
                'id_local'          => $localId,
                'turno'             => $turnoSiguiente,
                'fecha'             => now('America/Argentina/Buenos_Aires')->toDateString(),
                'estado'            => 'abierta',

                'efectivo_apertura' => $efectivoApertura,
                'ingreso_efectivo'  => 0,
                'salida_efectivo'   => 0,
                'efectivo_turno'    => (float)$efectivoApertura,

                'abierta_at'        => now('America/Argentina/Buenos_Aires'),
                'cerrada_at'        => null,
                'abierta_por'       => $userId,
                'cerrada_por'       => null,

                'observacion'       => $data['observacion'] ?? null,
            ]);

            if ($ingresoInicial > 0) {
                CajaMovimiento::create([
                    'id_local'  => $localId,
                    'id_caja'   => $caja->id,
                    'id_user'   => $userId,
                    'tipo'      => 'ingreso',
                    'monto'     => $ingresoInicial,
                    'concepto'  => 'Ajuste de apertura (ingreso)',
                    'movido_at' => now('America/Argentina/Buenos_Aires'),
                ]);
            }

            if ($salidaInicial > 0) {
                CajaMovimiento::create([
                    'id_local'  => $localId,
                    'id_caja'   => $caja->id,
                    'id_user'   => $userId,
                    'tipo'      => 'salida',
                    'monto'     => $salidaInicial,
                    'concepto'  => 'Ajuste de apertura (salida)',
                    'movido_at' => now('America/Argentina/Buenos_Aires'),
                ]);
            }

            $caja->refreshTotalesCache();

            return redirect()
                ->route('admin.caja.index')
                ->with('ok', 'Turno de caja abierto (#' . $caja->turno . ').');
        });
    }

    public function cerrar(Request $request)
    {
        $localId = $this->localId();
        $userId  = (int) auth()->id();

        $data = $request->validate([
            'observacion'    => ['nullable', 'string', 'max:255'],
            'ajuste_ingreso' => ['nullable', 'numeric', 'min:0'],
            'ajuste_salida'  => ['nullable', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($localId, $userId, $data) {

            $caja = $this->cajaAbiertaQuery($localId)->lockForUpdate()->first();

            if (!$caja) {
                return back()->with('error', 'No hay un turno de caja abierto para cerrar.');
            }

            $hayComandasActivas = Comanda::query()
                ->where('id_local', $localId)
                ->whereIn('estado', ['abierta', 'en_cocina', 'lista', 'entregada', 'cerrando'])
                ->lockForUpdate()
                ->exists();

            if ($hayComandasActivas) {
                return back()->with('error', 'No se puede cerrar el turno: hay comandas abiertas/activas. Cerrá o anulá todas antes de cerrar caja.');
            }

            $ajIng = (float)($data['ajuste_ingreso'] ?? 0);
            $ajSal = (float)($data['ajuste_salida'] ?? 0);

            if ($ajIng > 0 && $ajSal > 0) {
                return back()->with('error', 'Usá solo ingreso o salida de ajuste, no ambos.');
            }

            if ($ajIng > 0) {
                CajaMovimiento::create([
                    'id_local'  => $localId,
                    'id_caja'   => $caja->id,
                    'id_user'   => $userId,
                    'tipo'      => 'ingreso',
                    'monto'     => $ajIng,
                    'concepto'  => 'Ajuste de cierre (ingreso)',
                    'movido_at' => now('America/Argentina/Buenos_Aires'),
                ]);
            }

            if ($ajSal > 0) {
                CajaMovimiento::create([
                    'id_local'  => $localId,
                    'id_caja'   => $caja->id,
                    'id_user'   => $userId,
                    'tipo'      => 'salida',
                    'monto'     => $ajSal,
                    'concepto'  => 'Ajuste de cierre (salida)',
                    'movido_at' => now('America/Argentina/Buenos_Aires'),
                ]);
            }

            $caja->refreshTotalesCache();
            $caja->refresh();

            $caja->estado      = 'cerrada';
            $caja->cerrada_at  = now('America/Argentina/Buenos_Aires');
            $caja->cerrada_por = $userId;

            if (!empty($data['observacion'])) {
                $caja->observacion = trim((string)($caja->observacion ?? ''));
                $caja->observacion = $caja->observacion === ''
                    ? $data['observacion']
                    : ($caja->observacion . ' | ' . $data['observacion']);
            }

            $caja->save();

            User::query()
                ->where('id_local', $localId)
                ->where('role', 'mozo')
                ->update(['estado' => 'inactivo']);

            Mesa::query()
                ->where('id_local', $localId)
                ->update([
                    'estado'       => 'libre',
                    'observacion'  => null,
                    'atendida_por' => null,
                    'atendida_at'  => null,
                ]);

            $printUrl = route('admin.caja.turno.ticket', $caja);

            return redirect()
                ->route('admin.caja.index')
                ->with(
                    'ok',
                    'Turno de caja cerrado (#' . $caja->turno . '). Mozos inactivados y mesas liberadas. Efectivo final: ' .
                    number_format((float)$caja->efectivo_turno, 2, ',', '.')
                )
                ->with('rf_print_turno_url', $printUrl)
                ->with('rf_turno_id', (int)$caja->id);
        });
    }

    public function ticket(Caja $caja)
    {
        $localId = $this->localId();
        abort_unless((int)$caja->id_local === $localId, 403);

        $movimientos = CajaMovimiento::query()
            ->where('id_caja', $caja->id)
            ->orderBy('movido_at')
            ->orderBy('id')
            ->get();

        $ventas = Venta::query()
            ->where('id_caja', $caja->id)
            ->orderBy('sold_at')
            ->orderBy('id')
            ->get();

        $ventaIds = $ventas->pluck('id')->all();

        $pagos = empty($ventaIds)
            ? collect()
            : Pago::query()
                ->whereIn('id_venta', $ventaIds)
                ->orderBy('id')
                ->get();

        $pagosPorVenta = $pagos->groupBy('id_venta');

        $ventasTotal       = (float) $ventas->sum('total');
        $ventasPagadoTotal = (float) $ventas->sum('pagado_total');
        $ventasVuelto      = (float) $ventas->sum('vuelto');

        $efectivoBruto = (float) $pagos->where('tipo', 'efectivo')->sum('monto');
        $debitoTotal   = (float) $pagos->where('tipo', 'debito')->sum('monto');
        $transferTotal = (float) $pagos->where('tipo', 'transferencia')->sum('monto');

        $efectivoVentasNeto = (float) ($efectivoBruto - $ventasVuelto);

        // ✅ Propinas desde ventas, no desde movimientos
        $propinas = (float) $ventas->sum('propina');

        // ✅ Ingresos manuales/otros desde movimientos
        $otrosIngresos = (float) $movimientos
            ->where('tipo', 'ingreso')
            ->sum('monto');

        $otrasSalidas = (float) $movimientos
            ->where('tipo', 'salida')
            ->sum('monto');

        $usuarioApertura = $caja->abierta_por ? User::find($caja->abierta_por) : null;
        $usuarioCierre   = $caja->cerrada_por ? User::find($caja->cerrada_por) : null;

        return view('admin.caja.turno-ticket', compact(
            'caja',
            'movimientos',
            'ventas',
            'pagosPorVenta',
            'ventasTotal',
            'ventasPagadoTotal',
            'ventasVuelto',
            'efectivoBruto',
            'efectivoVentasNeto',
            'debitoTotal',
            'transferTotal',
            'propinas',
            'otrosIngresos',
            'otrasSalidas',
            'usuarioApertura',
            'usuarioCierre'
        ));
    }
}