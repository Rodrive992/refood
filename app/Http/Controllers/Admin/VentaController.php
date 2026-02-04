<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Venta;

class VentaController extends Controller
{
    private function localId(): int
    {
        $idLocal = auth()->user()->id_local;
        abort_if(empty($idLocal), 403, 'Tu usuario no tiene un local asignado.');
        return (int) $idLocal;
    }

    public function ticket(Venta $venta)
    {
        $localId = $this->localId();
        abort_unless((int)$venta->id_local === $localId, 403);

        $venta->load([
            'comanda',
            'mesa',
            'mozo',
            'pagos',
            'comanda.items',
        ]);

        return view('admin.ventas.ticket', compact('venta'));
    }
}
