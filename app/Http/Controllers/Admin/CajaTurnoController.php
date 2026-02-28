<?php
// app/Http/Controllers/Admin/CajaTurnoController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\CajaMovimiento;
use App\Models\Comanda;
use App\Models\User;
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
                'fecha'             => now()->toDateString(),
                'estado'            => 'abierta',

                'efectivo_apertura' => $efectivoApertura,
                'ingreso_efectivo'  => 0,
                'salida_efectivo'   => 0,
                'efectivo_turno'    => (float)$efectivoApertura,

                'abierta_at'        => now(),
                'cerrada_at'        => null,
                'abierta_por'       => $userId,
                'cerrada_por'       => null,

                'observacion'       => $data['observacion'] ?? null,
            ]);

            // ✅ Registrar ajustes como movimientos para mantener consistencia
            if ($ingresoInicial > 0) {
                CajaMovimiento::create([
                    'id_local' => $localId,
                    'id_caja'  => $caja->id,
                    'id_user'  => $userId,
                    'tipo'     => 'ingreso',
                    'monto'    => $ingresoInicial,
                    'concepto' => 'Ajuste de apertura (ingreso)',
                    'movido_at'=> now(),
                ]);
            }
            if ($salidaInicial > 0) {
                CajaMovimiento::create([
                    'id_local' => $localId,
                    'id_caja'  => $caja->id,
                    'id_user'  => $userId,
                    'tipo'     => 'salida',
                    'monto'    => $salidaInicial,
                    'concepto' => 'Ajuste de apertura (salida)',
                    'movido_at'=> now(),
                ]);
            }

            // ✅ recalcular totales reales del turno
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

            // ✅ No permitir cerrar si hay comandas activas
            $hayComandasActivas = Comanda::query()
                ->where('id_local', $localId)
                ->whereIn('estado', ['abierta', 'en_cocina', 'lista', 'entregada', 'cerrando'])
                ->lockForUpdate()
                ->exists();

            if ($hayComandasActivas) {
                return back()->with('error', 'No se puede cerrar el turno: hay comandas abiertas/activas. Cerrá o anulá todas antes de cerrar caja.');
            }

            $ajIng = (float)($data['ajuste_ingreso'] ?? 0);
            $ajSal = (float)($data['ajuste_salida'  ] ?? 0);

            if ($ajIng > 0 && $ajSal > 0) {
                return back()->with('error', 'Usá solo ingreso o salida de ajuste, no ambos.');
            }

            // ✅ Ajustes finales como movimientos
            if ($ajIng > 0) {
                CajaMovimiento::create([
                    'id_local' => $localId,
                    'id_caja'  => $caja->id,
                    'id_user'  => $userId,
                    'tipo'     => 'ingreso',
                    'monto'    => $ajIng,
                    'concepto' => 'Ajuste de cierre (ingreso)',
                    'movido_at'=> now(),
                ]);
            }
            if ($ajSal > 0) {
                CajaMovimiento::create([
                    'id_local' => $localId,
                    'id_caja'  => $caja->id,
                    'id_user'  => $userId,
                    'tipo'     => 'salida',
                    'monto'    => $ajSal,
                    'concepto' => 'Ajuste de cierre (salida)',
                    'movido_at'=> now(),
                ]);
            }

            // ✅ recalcula totales reales (incluye ventas efectivo neto y vuelto)
            $caja->refreshTotalesCache();
            $caja->refresh();

            $caja->estado      = 'cerrada';
            $caja->cerrada_at  = now();
            $caja->cerrada_por = $userId;

            if (!empty($data['observacion'])) {
                $caja->observacion = trim((string)($caja->observacion ?? ''));
                $caja->observacion = $caja->observacion === ''
                    ? $data['observacion']
                    : ($caja->observacion . ' | ' . $data['observacion']);
            }

            $caja->save();

            // ✅ Al cerrar el turno: inactivar TODOS los mozos del local
            User::query()
                ->where('id_local', $localId)
                ->where('role', 'mozo')
                ->update(['estado' => 'inactivo']);

            return redirect()
                ->route('admin.caja.index')
                ->with(
                    'ok',
                    'Turno de caja cerrado (#' . $caja->turno . '). Mozos inactivados. Efectivo final: ' .
                    number_format((float)$caja->efectivo_turno, 2, ',', '.')
                );
        });
    }
}