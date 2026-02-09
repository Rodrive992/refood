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
    // =========================================================
    // Helpers
    // =========================================================
    private function localId(): int
    {
        $idLocal = auth()->user()->id_local ?? null;
        abort_if(empty($idLocal), 403, 'Tu usuario no tiene un local asignado.');
        return (int) $idLocal;
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
        if (!$comanda) return 0.0;

        // ✅ ya no hay "anulado": si se borra, no suma
        return (float) $comanda->items
            ->sum(fn($it) => (float) $it->precio_snapshot * (float) $it->cantidad);
    }

    private function mesasDelLocal(int $localId)
    {
        return Mesa::query()
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
            ->latest('id')
            ->get()
            ->keyBy('id_mesa');
    }

    /**
     * Compat para el dashboard:
     * - desktop|mobile (desde tus fetch)
     * - default desktop (no rompe nada)
     */
    private function viewMode(Request $request): string
    {
        $view = strtolower((string) $request->get('view', 'desktop'));
        return in_array($view, ['mobile', 'desktop'], true) ? $view : 'desktop';
    }

    // =========================================================
    // Dashboard
    // =========================================================
    public function index(Request $request)
    {
        $localId = $this->localId();
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

        // Carta (solo activos del local)
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
            'comandasActivasPorMesa'
        ));
    }

    // =========================================================
    // PARTIAL: MESAS
    // GET /mozo/dashboard/mesas?view=mobile|desktop&mesa_id=XX
    // Devuelve: mozo.partials.mesa (unificado)
    // =========================================================
    public function partialMesas(Request $request)
    {
        $localId = $this->localId();
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
        ]);
    }

    // =========================================================
    // PARTIAL: COMANDA
    // GET /mozo/dashboard/comanda?mesa_id=XX&view=mobile|desktop
    // Devuelve: mozo.partials.comanda (unificado)
    // =========================================================
    public function partialComanda(Request $request)
    {
        $localId = $this->localId();
        $view = $this->viewMode($request);
        $mesaId  = (int) $request->get('mesa_id', 0);

        $mesaActiva = null;
        $comandaActiva = null;
        $subtotal = 0.0;

        if ($mesaId > 0) {
            $mesaActiva = Mesa::query()
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
        ]);
    }

    // =========================================================
    // PARTIAL: CUENTA
    // GET /mozo/dashboard/cuenta?mesa_id=XX&view=mobile|desktop
    // Devuelve: mozo.partials.cuenta (unificado)
    // =========================================================
    public function partialCuenta(Request $request)
    {
        $localId = $this->localId();
        $view = $this->viewMode($request);
        $mesaId  = (int) $request->get('mesa_id', 0);

        $mesaActiva = null;
        $comandaActiva = null;
        $subtotal = 0.0;

        if ($mesaId > 0) {
            $mesaActiva = Mesa::query()
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
        ]);
    }
}
