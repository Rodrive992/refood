<?php

namespace App\Http\Controllers\Mozo;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use Illuminate\Http\Request;

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

        // Ojo: tu modelo usa fuera_servicio, pero vos estabas chequeando "inactiva"
        // Te dejo ambos por compatibilidad:
        abort_if(in_array($mesa->estado, ['inactiva', 'fuera_servicio'], true), 422, 'La mesa está inactiva.');
    }

    public function ocupar(Request $request, Mesa $mesa)
    {
        $this->assertMesaLocal($mesa);

        $data = $request->validate([
            'observacion' => ['nullable', 'string', 'max:255'],
        ]);

        $mesa->update([
            'estado'        => 'ocupada',
            'observacion'   => $data['observacion'] ?? null,

            // NUEVO
            'atendida_por'  => auth()->id(),
            'atendida_at'   => now(),
        ]);

        return back()->with('ok', 'Mesa ocupada.');
    }

    public function reservar(Request $request, Mesa $mesa)
    {
        $this->assertMesaLocal($mesa);

        $data = $request->validate([
            'observacion' => ['nullable', 'string', 'max:255'],
        ]);

        $mesa->update([
            'estado'        => 'reservada',
            'observacion'   => $data['observacion'] ?? null,

            // NUEVO (recomendado): si la reserva la hace un mozo, que quede marcado
            'atendida_por'  => auth()->id(),
            'atendida_at'   => now(),
        ]);

        return back()->with('ok', 'Mesa reservada.');
    }

    public function liberar(Mesa $mesa)
    {
        $this->assertMesaLocal($mesa);

        $mesa->update([
            'estado'        => 'libre',
            'observacion'   => null,

            // NUEVO
            'atendida_por'  => null,
            'atendida_at'   => null,
        ]);

        return back()->with('ok', 'Mesa liberada.');
    }
}
