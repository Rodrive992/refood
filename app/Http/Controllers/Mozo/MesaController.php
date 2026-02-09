<?php

namespace App\Http\Controllers\Mozo;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Comanda;
use App\Models\ComandaItem;

class MesaController extends Controller
{
    private function localId(): int
    {
        $idLocal = auth()->user()->id_local;
        abort_if(empty($idLocal), 403, 'Tu usuario no tiene un local asignado.');
        return (int) $idLocal;
    }

    private function assertMesaLocal(Mesa $mesa): void
    {
        abort_unless((int) $mesa->id_local === $this->localId(), 403);
        abort_if(in_array($mesa->estado, ['inactiva', 'fuera_servicio'], true), 422, 'La mesa está inactiva.');
    }

    // ✅ ocupar SIN comanda
    public function ocupar(Request $request, Mesa $mesa)
    {
        $this->assertMesaLocal($mesa);

        $data = $request->validate([
            'observacion' => ['nullable', 'string', 'max:255'],
        ]);

        $mesa->update([
            'estado'        => 'ocupada',
            'observacion'   => $data['observacion'] ?? null,
            'atendida_por'  => auth()->id(),
            'atendida_at'   => now(),
        ]);

        return back()->with('ok', 'Mesa ocupada.');
    }

    // ✅ liberar y además anular comanda activa + borrar items
    public function liberar(Mesa $mesa)
    {
        $this->assertMesaLocal($mesa);

        $localId = $this->localId();

        DB::transaction(function () use ($mesa, $localId) {

            $comanda = Comanda::query()
                ->where('id_local', $localId)
                ->where('id_mesa', $mesa->id)
                ->whereIn('estado', ['abierta', 'en_cocina', 'lista', 'entregada'])
                ->latest('id')
                ->first();

            if ($comanda) {
                ComandaItem::query()
                    ->where('id_comanda', $comanda->id)
                    ->delete();

                $comanda->estado = 'anulada';
                $comanda->closed_at = now();
                $comanda->save();
            }

            $mesa->update([
                'estado'        => 'libre',
                'observacion'   => null,
                'atendida_por'  => null,
                'atendida_at'   => null,
            ]);
        });

        return back()->with('ok', 'Mesa liberada.');
    }
}
