<?php
// app/Http/Controllers/Admin/CajaMovimientoController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\CajaMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CajaMovimientoController extends Controller
{
    private function localId(): int
    {
        $idLocal = auth()->user()->id_local ?? null;
        abort_if(empty($idLocal), 403, 'Tu usuario no tiene un local asignado.');
        return (int) $idLocal;
    }

    /**
     * ✅ Obtiene caja abierta del local con lock (dentro de tx)
     */
    private function cajaAbiertaLock(int $localId): ?Caja
    {
        return Caja::query()
            ->where('id_local', $localId)
            ->where('estado', 'abierta')
            ->lockForUpdate()
            ->first();
    }

    /**
     * POST admin/caja/movimientos
     * Crea un ingreso o salida y recalcula totales del turno.
     */
    public function store(Request $request)
    {
        $localId = $this->localId();
        $userId  = (int) auth()->id();

        $data = $request->validate([
            'tipo' => ['required', 'in:ingreso,salida'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'concepto' => ['nullable', 'string', 'max:255'],
            'movido_at' => ['nullable', 'date'],
        ]);

        return DB::transaction(function () use ($localId, $userId, $data) {

            $caja = $this->cajaAbiertaLock($localId);

            if (!$caja) {
                return back()->with('error', 'No hay un turno de caja abierto. Abrí caja para registrar movimientos.');
            }

            CajaMovimiento::create([
                'id_local' => $localId,
                'id_caja'  => $caja->id,
                'id_user'  => $userId,
                'tipo'     => (string) $data['tipo'],
                'monto'    => (float) $data['monto'],
                'concepto' => $data['concepto'] ?? null,
                'movido_at'=> !empty($data['movido_at']) ? $data['movido_at'] : now(),
            ]);

            // ✅ Recalcula: apertura + ventas efectivo neto (resta vuelto) + ingresos - salidas
            $caja->refreshTotalesCache();

            return back()->with('ok', 'Movimiento registrado.');
        });
    }

    /**
     * DELETE admin/caja/movimientos/{movimiento}
     * Elimina un movimiento (solo si pertenece a la caja abierta del mismo local)
     * y recalcula totales del turno.
     */
    public function destroy(CajaMovimiento $movimiento)
    {
        $localId = $this->localId();

        return DB::transaction(function () use ($localId, $movimiento) {

            $caja = $this->cajaAbiertaLock($localId);

            if (!$caja) {
                return back()->with('error', 'No hay caja abierta. No se pueden borrar movimientos.');
            }

            if ((int)$movimiento->id_local !== $localId || (int)$movimiento->id_caja !== (int)$caja->id) {
                abort(403, 'Movimiento fuera de tu caja abierta.');
            }

            $movimiento->delete();

            // ✅ Recalcula totales reales del turno
            $caja->refreshTotalesCache();

            return back()->with('ok', 'Movimiento eliminado.');
        });
    }
}