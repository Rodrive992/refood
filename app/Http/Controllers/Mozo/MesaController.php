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
        $idLocal = auth()->user()->id_local ?? null;
        abort_if(empty($idLocal), 403, 'Tu usuario no tiene un local asignado.');
        return (int) $idLocal;
    }

    private function mozoId(): int
    {
        return (int) auth()->id();
    }

    private function assertMesaLocal(Mesa $mesa): void
    {
        abort_unless((int) $mesa->id_local === $this->localId(), 403, 'La mesa no pertenece a tu local.');
        abort_if(in_array($mesa->estado, ['inactiva', 'fuera_servicio'], true), 422, 'La mesa está inactiva.');
    }

    private function assertMesaTomadaPorEsteMozo(Mesa $mesa): void
    {
        $this->assertMesaLocal($mesa);

        abort_if(
            (int) ($mesa->atendida_por ?? 0) !== $this->mozoId(),
            403,
            'Esta mesa está siendo atendida por otro mozo.'
        );
    }

    public function ocupar(Request $request, Mesa $mesa)
    {
        $this->assertMesaLocal($mesa);

        $data = $request->validate([
            'observacion' => ['nullable', 'string', 'max:255'],
        ]);

        $localId = $this->localId();
        $mozoId  = $this->mozoId();

        DB::transaction(function () use ($mesa, $data, $localId, $mozoId) {
            $mesa = Mesa::query()
                ->where('id', $mesa->id)
                ->where('id_local', $localId)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($mesa->estado, ['inactiva', 'fuera_servicio'], true)) {
                abort(422, 'La mesa está inactiva.');
            }

            if (
                $mesa->estado !== 'libre' &&
                !empty($mesa->atendida_por) &&
                (int) $mesa->atendida_por !== $mozoId
            ) {
                abort(409, 'La mesa ya está siendo atendida por otro mozo.');
            }

            $mesa->update([
                'estado'       => 'ocupada',
                'observacion'  => $data['observacion'] ?? $mesa->observacion,
                'atendida_por' => $mozoId,
                'atendida_at'  => $mesa->atendida_at ?? now(),
            ]);
        });

        return back()->with('ok', 'Mesa ocupada.');
    }

    public function liberar(Mesa $mesa)
    {
        $this->assertMesaLocal($mesa);

        $localId = $this->localId();
        $mozoId  = $this->mozoId();

        DB::transaction(function () use ($mesa, $localId, $mozoId) {
            $mesa = Mesa::query()
                ->where('id', $mesa->id)
                ->where('id_local', $localId)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) ($mesa->atendida_por ?? 0) !== $mozoId) {
                abort(403, 'No podés liberar una mesa que está siendo atendida por otro mozo.');
            }

            $comanda = Comanda::query()
                ->where('id_local', $localId)
                ->where('id_mesa', $mesa->id)
                ->whereIn('estado', ['abierta', 'en_cocina', 'lista', 'entregada'])
                ->latest('id')
                ->lockForUpdate()
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
                'estado'       => 'libre',
                'observacion'  => null,
                'atendida_por' => null,
                'atendida_at'  => null,
            ]);
        });

        return back()->with('ok', 'Mesa liberada.');
    }
}