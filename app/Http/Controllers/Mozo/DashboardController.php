<?php

namespace App\Http\Controllers\Mozo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mesa;
use App\Models\Comanda;
use App\Models\CartaCategoria;
use App\Models\CartaItem;

class DashboardController extends Controller
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

    private function comandaActivaDeMesa(int $mesaId): ?Comanda
    {
        return Comanda::query()
            ->where('id_local', $this->localId())
            ->where('id_mesa', $mesaId)
            ->whereIn('estado', ['abierta', 'en_cocina', 'lista', 'entregada'])
            ->latest('id')
            ->with(['items' => function ($q) {
                $q->orderBy('id');
            }])
            ->first();
    }

    private function calcSubtotal(?Comanda $comanda): float
    {
        if (!$comanda) {
            return 0.0;
        }

        return (float) $comanda->items
            ->sum(fn($it) => (float) $it->precio_snapshot * (float) $it->cantidad);
    }

    private function mesasDelLocal(int $localId)
    {
        return Mesa::query()
            ->with('mozoAtendiendo:id,name')
            ->where('id_local', $localId)
            ->orderByRaw("FIELD(estado,'ocupada','reservada','libre','inactiva','fuera_servicio')")
            ->orderBy('nombre')
            ->get();
    }

    private function comandasActivasPorMesa(int $localId)
    {
        return Comanda::query()
            ->where('id_local', $localId)
            ->whereNotNull('id_mesa')
            ->whereIn('estado', ['abierta', 'en_cocina', 'lista', 'entregada'])
            ->withCount('items')
            ->latest('id')
            ->get()
            ->keyBy('id_mesa');
    }

    private function viewMode(Request $request): string
    {
        $view = strtolower((string) $request->get('view', 'desktop'));
        return in_array($view, ['mobile', 'desktop'], true) ? $view : 'desktop';
    }

    public function index(Request $request)
    {
        $localId = $this->localId();
        $mozoId  = $this->mozoId();
        $mesaId  = (int) $request->get('mesa_id', 0);

        $mesas = $this->mesasDelLocal($localId);

        $mesaActiva = null;
        $comandaActiva = null;
        $subtotal = 0.0;

        if ($mesaId > 0) {
            $mesaActiva = $mesas->firstWhere('id', $mesaId);

            if ($mesaActiva) {
                $comandaActiva = $this->comandaActivaDeMesa((int) $mesaActiva->id);
                $subtotal = $this->calcSubtotal($comandaActiva);
            }
        }

        $cartaCategorias = CartaCategoria::query()
            ->where('id_local', $localId)
            ->where('activo', 1)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $cartaItems = CartaItem::query()
            ->where('id_local', $localId)
            ->where('activo', 1)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $comandasActivasPorMesa = $this->comandasActivasPorMesa($localId);

        return view('mozo.dashboard', compact(
            'mesas',
            'mesaActiva',
            'comandaActiva',
            'subtotal',
            'cartaCategorias',
            'cartaItems',
            'comandasActivasPorMesa',
            'mozoId'
        ));
    }

    public function partialMesas(Request $request)
    {
        $localId = $this->localId();
        $mozoId = $this->mozoId();
        $view = $this->viewMode($request);
        $mesaId = (int) $request->get('mesa_id', 0);

        $mesas = $this->mesasDelLocal($localId);
        $comandasActivasPorMesa = $this->comandasActivasPorMesa($localId);

        $mesaSelected = null;
        if ($mesaId > 0) {
            $mesaSelected = $mesas->firstWhere('id', $mesaId);
        }

        return view('mozo.partials.mesa', [
            'isMobile' => ($view === 'mobile'),
            'mesas' => $mesas,
            'comandasActivasPorMesa' => $comandasActivasPorMesa,
            'mesaSelected' => $mesaSelected,
            'mozoId' => $mozoId,
        ]);
    }

    public function partialComanda(Request $request)
    {
        $localId = $this->localId();
        $mozoId = $this->mozoId();
        $view = $this->viewMode($request);
        $mesaId  = (int) $request->get('mesa_id', 0);

        $mesaActiva = null;
        $comandaActiva = null;
        $subtotal = 0.0;

        if ($mesaId > 0) {
            $mesaActiva = Mesa::query()
                ->with('mozoAtendiendo:id,name')
                ->where('id_local', $localId)
                ->where('id', $mesaId)
                ->first();

            if ($mesaActiva) {
                $comandaActiva = $this->comandaActivaDeMesa((int) $mesaActiva->id);
                $subtotal = $this->calcSubtotal($comandaActiva);
            }
        }

        return view('mozo.partials.comanda', [
            'isMobile' => ($view === 'mobile'),
            'mesaSelected' => $mesaActiva,
            'comanda'      => $comandaActiva,
            'subtotal'     => $subtotal,
            'mozoId'       => $mozoId,
        ]);
    }

    public function partialCuenta(Request $request)
    {
        $localId = $this->localId();
        $mozoId = $this->mozoId();
        $view = $this->viewMode($request);
        $mesaId  = (int) $request->get('mesa_id', 0);

        $mesaActiva = null;
        $comandaActiva = null;
        $subtotal = 0.0;

        if ($mesaId > 0) {
            $mesaActiva = Mesa::query()
                ->with('mozoAtendiendo:id,name')
                ->where('id_local', $localId)
                ->where('id', $mesaId)
                ->first();

            if ($mesaActiva) {
                $comandaActiva = $this->comandaActivaDeMesa((int) $mesaActiva->id);
                $subtotal = $this->calcSubtotal($comandaActiva);
            }
        }

        return view('mozo.partials.cuenta', [
            'isMobile' => ($view === 'mobile'),
            'mesaSelected' => $mesaActiva,
            'comanda'      => $comandaActiva,
            'subtotal'     => $subtotal,
            'mozoId'       => $mozoId,
        ]);
    }
}