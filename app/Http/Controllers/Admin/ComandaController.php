<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comanda;
use App\Models\Mesa;
use Illuminate\Http\Request;

class ComandaController extends Controller
{
    private function localId(): int
    {
        $idLocal = auth()->user()->id_local;
        abort_if(empty($idLocal), 403, 'Tu usuario no tiene un local asignado.');
        return (int) $idLocal;
    }

    public function index(Request $request)
    {
        $localId = $this->localId();

        $estado = $request->get('estado', 'activas'); // activas | todas | cerradas
        $mesaId = $request->get('mesa_id');

        $q = trim((string) $request->get('q', ''));

        $query = Comanda::query()
            ->where('id_local', $localId)
            ->with(['mesa', 'mozo'])
            ->withCount(['items as items_count'])
            ->orderByDesc('opened_at');

        if ($estado === 'activas') {
            $query->whereIn('estado', ['abierta','en_cocina','lista','entregada','cerrando']);
        } elseif ($estado === 'cerradas') {
            $query->whereIn('estado', ['cerrada','anulada']);
        }

        if (!empty($mesaId)) {
            $query->where('id_mesa', $mesaId);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                if (ctype_digit($q)) {
                    $sub->orWhere('id', (int)$q);
                }
                $sub->orWhere('observacion', 'like', "%{$q}%");
            });
        }

        $comandas = $query->paginate(20)->withQueryString();

        $mesas = Mesa::query()
            ->where('id_local', $localId)
            ->where('estado', '!=', 'inactiva')
            ->orderBy('nombre')
            ->get(['id','nombre','estado']);

        return view('admin.comandas.index', compact('comandas', 'mesas', 'estado', 'mesaId', 'q'));
    }

    /**
     * ✅ Unificamos flujo:
     * El detalle real + cobro se maneja en CAJA.
     * Si alguien entra a /admin/comandas/{comanda}, lo mandamos a /admin/caja/comandas/{comanda}
     */
    public function show(Comanda $comanda)
    {
        $localId = $this->localId();
        abort_unless((int)$comanda->id_local === $localId, 403);

        return redirect()->route('admin.caja.show', $comanda);
    }

    /**
     * ❌ Cobro eliminado en Comandas.
     * El cobro se hace únicamente en CajaController@cobrar.
     *
     * Si por error alguien apunta a esta ruta, lo redirigimos a caja.
     */
    public function cobrar(Request $request, Comanda $comanda)
    {
        $localId = $this->localId();
        abort_unless((int)$comanda->id_local === $localId, 403);

        return redirect()
            ->route('admin.caja.show', $comanda)
            ->with('ok', 'El cobro se realiza desde CAJA.');
    }

    /**
     * ✅ Poll AJAX: refresco automático del index SIN recargar.
     * Devuelve HTML renderizado (cards y paginación) + contadores para sonido.
     */
    public function poll(Request $request)
    {
        $localId = $this->localId();

        $estado = $request->get('estado', 'activas'); // activas | todas | cerradas
        $mesaId = $request->get('mesa_id');
        $q = trim((string) $request->get('q', ''));
        $page = max(1, (int) $request->get('page', 1));

        $query = Comanda::query()
            ->where('id_local', $localId)
            ->with(['mesa', 'mozo'])
            ->withCount(['items as items_count'])
            ->orderByDesc('opened_at');

        if ($estado === 'activas') {
            $query->whereIn('estado', ['abierta','en_cocina','lista','entregada','cerrando']);
        } elseif ($estado === 'cerradas') {
            $query->whereIn('estado', ['cerrada','anulada']);
        }

        if (!empty($mesaId)) {
            $query->where('id_mesa', $mesaId);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                if (ctype_digit($q)) {
                    $sub->orWhere('id', (int)$q);
                }
                $sub->orWhere('observacion', 'like', "%{$q}%");
            });
        }

        $comandas = $query->paginate(20, ['*'], 'page', $page)->withQueryString();

        $cardsHtml = view('admin.comandas._poll_cards', [
            'comandas' => $comandas,
        ])->render();

        $paginationHtml = $comandas->links()->render();

        return response()->json([
            'ok' => true,
            'total' => (int) $comandas->total(),
            'page' => (int) $comandas->currentPage(),
            'cards_html' => $cardsHtml,
            'pagination_html' => $paginationHtml,
            'ts' => now()->toISOString(),
        ]);
    }

    /**
     * ✅ Imprimir comanda (ticket cocina).
     * No toca CAJA. Es solo impresión.
     */
    public function print(Comanda $comanda)
    {
        $localId = $this->localId();
        abort_unless((int)$comanda->id_local === $localId, 403);

        // Cargar relaciones y items
        $comanda->load(['mesa', 'mozo', 'items']);

        return view('admin.comandas.print', compact('comanda'));
    }
}