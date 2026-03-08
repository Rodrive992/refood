<?php

namespace App\Http\Controllers\Mozo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Comanda;
use App\Models\Mesa;
use App\Models\Venta;
use App\Models\Pago;

class VentaController extends Controller
{
    private function localId(): int
    {
        $idLocal = auth()->user()->id_local ?? null;
        abort_if(empty($idLocal), 403, 'Tu usuario no tiene un local asignado.');
        return (int) $idLocal;
    }

    private function mozoId(): int
    {
        return (int) auth()->id();
    }

    private function assertComandaLocal(Comanda $comanda): void
    {
        abort_if((int)$comanda->id_local !== $this->localId(), 403, 'Comanda fuera de tu local.');
    }

    private function ensureComandaActiva(Comanda $comanda): void
    {
        abort_if(!in_array($comanda->estado, ['abierta','en_cocina','lista','entregada'], true), 422, 'La comanda no está activa.');
    }

    private function assertComandaOperablePorEsteMozo(Comanda $comanda): void
    {
        $this->assertComandaLocal($comanda);

        abort_if(empty($comanda->id_mesa), 422, 'La comanda no tiene una mesa asociada.');

        $mesa = Mesa::query()
            ->where('id', $comanda->id_mesa)
            ->where('id_local', $this->localId())
            ->firstOrFail();

        abort_if(
            (int) ($mesa->atendida_por ?? 0) !== $this->mozoId(),
            403,
            'Esta mesa está siendo atendida por otro mozo.'
        );
    }

    public function cobrar(Request $request, Comanda $comanda)
    {
        $this->assertComandaLocal($comanda);
        $this->ensureComandaActiva($comanda);
        $this->assertComandaOperablePorEsteMozo($comanda);

        $localId = $this->localId();
        $user = auth()->user();

        $validated = $request->validate([
            'descuento' => ['nullable','numeric','min:0'],
            'recargo'   => ['nullable','numeric','min:0'],
            'nota'      => ['nullable','string','max:255'],
            'pagos' => ['required','array','min:1'],
            'pagos.*.tipo' => ['required','in:efectivo,debito,transferencia'],
            'pagos.*.monto' => ['required','numeric','min:0.01'],
            'pagos.*.referencia' => ['nullable','string','max:120'],
        ], [
            'pagos.required' => 'Agregá al menos un pago.',
            'pagos.*.monto.min' => 'El monto mínimo por pago es 0,01.',
        ]);

        $descuento = (float) ($validated['descuento'] ?? 0);
        $recargo = (float) ($validated['recargo'] ?? 0);

        $comanda->load('items');

        $subtotal = (float) $comanda->items
            ->sum(fn($it) => (float)$it->precio_snapshot * (float)$it->cantidad);

        $total = max(0, $subtotal - $descuento + $recargo);

        $pagado = 0.0;
        foreach ($validated['pagos'] as $p) {
            $pagado += (float)$p['monto'];
        }

        abort_if($pagado + 0.00001 < $total, 422, 'El total pagado es menor al total a cobrar.');

        $vuelto = max(0, $pagado - $total);

        $venta = null;

        DB::transaction(function () use (
            &$venta,
            $validated,
            $localId,
            $user,
            $comanda,
            $subtotal,
            $descuento,
            $recargo,
            $total,
            $pagado,
            $vuelto
        ) {
            $comanda = Comanda::query()
                ->where('id', $comanda->id)
                ->where('id_local', $localId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureComandaActiva($comanda);
            $this->assertComandaOperablePorEsteMozo($comanda);

            $ya = Venta::query()
                ->where('id_comanda', $comanda->id)
                ->lockForUpdate()
                ->first();

            abort_if($ya, 422, 'Esta comanda ya fue cobrada.');

            $venta = Venta::create([
                'id_local'     => $localId,
                'id_comanda'   => $comanda->id,
                'id_mesa'      => $comanda->id_mesa,
                'id_mozo'      => $user->id,
                'estado'       => 'pagada',
                'subtotal'     => $subtotal,
                'descuento'    => $descuento,
                'recargo'      => $recargo,
                'total'        => $total,
                'pagado_total' => $pagado,
                'vuelto'       => $vuelto,
                'nota'         => $validated['nota'] ?? null,
                'sold_at'      => now(),
            ]);

            foreach ($validated['pagos'] as $p) {
                Pago::create([
                    'id_venta'    => $venta->id,
                    'tipo'        => $p['tipo'],
                    'monto'       => (float)$p['monto'],
                    'referencia'  => $p['referencia'] ?? null,
                    'recibido_at' => now(),
                ]);
            }

            $comanda->estado = 'cerrada';
            $comanda->closed_at = now();
            $comanda->save();

            if ($comanda->id_mesa) {
                $mesa = Mesa::query()
                    ->where('id', $comanda->id_mesa)
                    ->where('id_local', $localId)
                    ->lockForUpdate()
                    ->first();

                if ($mesa) {
                    $mesa->estado = 'libre';
                    $mesa->observacion = null;
                    $mesa->atendida_por = null;
                    $mesa->atendida_at = null;
                    $mesa->save();
                }
            }
        });

        return redirect()
            ->route('mozo.ventas.ticket', $venta)
            ->with('ok', 'Cobro confirmado. Venta #' . $venta->id);
    }

    public function ticket(Venta $venta)
    {
        $localId = $this->localId();
        abort_if((int)$venta->id_local !== $localId, 403, 'Venta fuera de tu local.');

        $venta->load(['pagos']);

        $comanda = null;
        if ($venta->id_comanda) {
            $comanda = Comanda::query()
                ->where('id', $venta->id_comanda)
                ->where('id_local', $localId)
                ->with('items')
                ->first();
        }

        return view('mozo.ventas.ticket', compact('venta','comanda'));
    }
}