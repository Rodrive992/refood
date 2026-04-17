<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comanda;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComandaController extends Controller
{
    private function localId(): int
    {
        $idLocal = auth()->user()->id_local;
        abort_if(empty($idLocal), 403, 'Tu usuario no tiene un local asignado.');
        return (int) $idLocal;
    }

    private function pedidoNumeroActual(Comanda $comanda): int
    {
        return max(1, (int) ($comanda->current_pedido_numero ?? 1));
    }

    public function index(Request $request)
    {
        $localId = $this->localId();

        $estado = $request->get('estado', 'activas');
        $mesaId = $request->get('mesa_id');
        $q = trim((string) $request->get('q', ''));

        $query = Comanda::query()
            ->where('id_local', $localId)
            ->with(['mesa', 'mozo'])
            ->withCount(['items as items_count'])
            ->orderByDesc('opened_at');

        if ($estado === 'activas') {
            $query->whereIn('estado', ['abierta', 'en_cocina', 'lista', 'entregada', 'cerrando']);
        } elseif ($estado === 'cerradas') {
            $query->whereIn('estado', ['cerrada', 'anulada']);
        }

        if (!empty($mesaId)) {
            $query->where('id_mesa', $mesaId);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                if (ctype_digit($q)) {
                    $sub->orWhere('id', (int) $q);
                }
                $sub->orWhere('observacion', 'like', "%{$q}%");
            });
        }

        $comandas = $query->paginate(20)->withQueryString();

        $mesas = Mesa::query()
            ->where('id_local', $localId)
            ->where('estado', '!=', 'inactiva')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'estado']);

        return view('admin.comandas.index', compact('comandas', 'mesas', 'estado', 'mesaId', 'q'));
    }

    public function show(Comanda $comanda)
    {
        $localId = $this->localId();
        abort_unless((int) $comanda->id_local === $localId, 403);

        return redirect()->route('admin.caja.show', $comanda);
    }

    public function cobrar(Request $request, Comanda $comanda)
    {
        $localId = $this->localId();
        abort_unless((int) $comanda->id_local === $localId, 403);

        return redirect()
            ->route('admin.caja.show', $comanda)
            ->with('ok', 'El cobro se realiza desde CAJA.');
    }

    public function poll(Request $request)
    {
        $localId = $this->localId();

        $estado = $request->get('estado', 'activas');
        $mesaId = $request->get('mesa_id');
        $q = trim((string) $request->get('q', ''));
        $page = max(1, (int) $request->get('page', 1));

        $query = Comanda::query()
            ->where('id_local', $localId)
            ->with(['mesa', 'mozo'])
            ->withCount(['items as items_count'])
            ->orderByDesc('opened_at');

        if ($estado === 'activas') {
            $query->whereIn('estado', ['abierta', 'en_cocina', 'lista', 'entregada', 'cerrando']);
        } elseif ($estado === 'cerradas') {
            $query->whereIn('estado', ['cerrada', 'anulada']);
        }

        if (!empty($mesaId)) {
            $query->where('id_mesa', $mesaId);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                if (ctype_digit($q)) {
                    $sub->orWhere('id', (int) $q);
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

    public function print(Comanda $comanda)
    {
        $localId = $this->localId();
        abort_unless((int) $comanda->id_local === $localId, 403);

        $printedAt = now()->setTimezone('America/Argentina/Buenos_Aires');
        $pedidoNumero = null;
        $itemsPedido = collect();

        DB::transaction(function () use ($comanda, $localId, $printedAt, &$pedidoNumero, &$itemsPedido) {
            $comanda = Comanda::query()
                ->where('id', $comanda->id)
                ->where('id_local', $localId)
                ->lockForUpdate()
                ->firstOrFail();

            abort_if(
                in_array($comanda->estado, ['cerrada', 'anulada'], true),
                422,
                'La comanda ya está cerrada o anulada.'
            );

            abort_if(
                (int) ($comanda->comanda_print_pendiente ?? 0) !== 1,
                422,
                'No hay pedido pendiente de impresión para esta comanda.'
            );

            $pedidoNumero = $this->pedidoNumeroActual($comanda);

            $itemsPedido = $comanda->items()
                ->where('pedido_numero', $pedidoNumero)
                ->where('estado', '!=', 'anulado')
                ->whereNull('impreso_cocina_at')
                ->orderBy('id')
                ->get();

            abort_if(
                $itemsPedido->isEmpty(),
                422,
                'No hay items nuevos para imprimir en este pedido.'
            );

            $ids = $itemsPedido->pluck('id')->all();

            $comanda->items()
                ->whereIn('id', $ids)
                ->update([
                    'impreso_cocina_at' => $printedAt,
                ]);

            $comanda->items()
                ->whereIn('id', $ids)
                ->where('estado', 'pendiente')
                ->update([
                    'estado' => 'en_cocina',
                ]);

            $comanda->update([
                'comanda_print_pendiente'  => 0,
                'comanda_print_impreso_at' => $printedAt,
                'current_pedido_numero'    => $pedidoNumero + 1,
            ]);
        });

        $comanda->load(['mesa', 'mozo']);

        return view('admin.comandas.print', [
            'comanda'       => $comanda,
            'printedAt'     => $printedAt,
            'pedidoNumero'  => $pedidoNumero,
            'itemsPedido'   => $itemsPedido,
            'esReimpresion' => false,
        ]);
    }

    public function reprint(Request $request, Comanda $comanda, int $pedidoNumero)
    {
        $localId = $this->localId();
        abort_unless((int) $comanda->id_local === $localId, 403);

        $pedidoNumero = max(1, (int) $pedidoNumero);

        DB::transaction(function () use ($comanda, $pedidoNumero, $localId) {
            $comandaDb = Comanda::query()
                ->where('id', $comanda->id)
                ->where('id_local', $localId)
                ->lockForUpdate()
                ->firstOrFail();

            abort_if(
                in_array($comandaDb->estado, ['cerrada', 'anulada'], true),
                422,
                'La comanda ya está cerrada o anulada.'
            );

            if (
                (int) ($comandaDb->reprint_pendiente ?? 0) === 1 &&
                (int) ($comandaDb->reprint_pedido_numero ?? 0) === $pedidoNumero
            ) {
                $comandaDb->update([
                    'reprint_pendiente'      => 0,
                    'reprint_pedido_numero'  => null,
                    'reprint_solicitado_at'  => null,
                    'reprint_solicitado_por' => null,
                ]);
            }
        });

        $itemsPedido = $comanda->items()
            ->where('pedido_numero', $pedidoNumero)
            ->where('estado', '!=', 'anulado')
            ->orderBy('id')
            ->get();

        abort_if(
            $itemsPedido->isEmpty(),
            422,
            "El pedido #{$pedidoNumero} no existe o no tiene items imprimibles."
        );

        $comanda->load(['mesa', 'mozo']);
        $printedAt = now()->setTimezone('America/Argentina/Buenos_Aires');

        return view('admin.comandas.reprint', [
            'comanda'       => $comanda,
            'printedAt'     => $printedAt,
            'pedidoNumero'  => $pedidoNumero,
            'itemsPedido'   => $itemsPedido,
            'esReimpresion' => true,
        ]);
    }
}