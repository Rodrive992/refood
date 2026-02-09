<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CajaHistorialController extends Controller
{
    private function localId(): int
    {
        $idLocal = auth()->user()->id_local ?? null;
        abort_if(empty($idLocal), 403, 'Tu usuario no tiene un local asignado.');
        return (int) $idLocal;
    }

    public function index(Request $request)
    {
        $localId = $this->localId();

        // Filtros
        $periodo = $request->get('periodo', 'hoy'); // hoy | semana | mes | rango
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');

        $qItem = trim((string)$request->get('q_item', '')); // filtro por nombre_snapshot
        $qVenta = trim((string)$request->get('q', ''));     // buscar por id venta o id comanda o nota

        // Rango de fechas (sold_at)
        [$from, $to] = $this->resolveRango($periodo, $desde, $hasta);

        // Base ventas
        $ventasQuery = Venta::query()
            ->where('id_local', $localId)
            ->whereBetween('sold_at', [$from, $to])
            ->orderByDesc('sold_at');

        if ($qVenta !== '') {
            $ventasQuery->where(function ($sub) use ($qVenta) {
                if (ctype_digit($qVenta)) {
                    $sub->orWhere('id', (int)$qVenta)
                        ->orWhere('id_comanda', (int)$qVenta);
                }
                $sub->orWhere('nota', 'like', "%{$qVenta}%");
            });
        }

        // ✅ Resumen del período
        $resumen = (clone $ventasQuery)
            ->reorder() // <- limpia ORDER BY
            ->selectRaw('
        COUNT(*) as cant_ventas,
        COALESCE(SUM(subtotal),0) as subtotal,
        COALESCE(SUM(descuento),0) as descuento,
        COALESCE(SUM(recargo),0) as recargo,
        COALESCE(SUM(total),0) as total,
        COALESCE(SUM(vuelto),0) as vuelto,
        COALESCE(SUM(pagado_total),0) as pagado_total,
        COALESCE(AVG(total),0) as promedio
    ')
            ->first();

        // ✅ Ventas paginadas
        $ventas = $ventasQuery->paginate(20)->withQueryString();

        // ✅ Ranking / resumen por items del período (y filtro opcional por nombre item)
        // Se basa en comanda_items snapshot enlazados por id_comanda en ventas
        $itemsQuery = DB::table('ventas as v')
            ->join('comanda_items as ci', 'ci.id_comanda', '=', 'v.id_comanda')
            ->where('v.id_local', $localId)
            ->whereBetween('v.sold_at', [$from, $to])
            ->where('ci.estado', '!=', 'anulado');

        if ($qItem !== '') {
            $itemsQuery->where('ci.nombre_snapshot', 'like', "%{$qItem}%");
        }

        $itemsResumen = (clone $itemsQuery)
            ->selectRaw('
                ci.nombre_snapshot as nombre,
                SUM(ci.cantidad) as cantidad,
                SUM(ci.cantidad * ci.precio_snapshot) as importe
            ')
            ->groupBy('ci.nombre_snapshot')
            ->orderByDesc(DB::raw('importe'))
            ->limit(30)
            ->get();

        // Para mostrar período en UI
        $periodoLabel = [
            'hoy' => 'Hoy',
            'semana' => 'Esta semana',
            'mes' => 'Este mes',
            'rango' => 'Rango',
        ][$periodo] ?? 'Rango';

        return view('admin.caja.historial.index', compact(
            'ventas',
            'resumen',
            'itemsResumen',
            'qItem',
            'qVenta',
            'periodo',
            'desde',
            'hasta',
            'from',
            'to',
            'periodoLabel'
        ));
    }

    private function resolveRango(string $periodo, ?string $desde, ?string $hasta): array
    {
        // Usamos timezone app (ideal Argentina/BuenosAires)
        $now = now();

        if ($periodo === 'hoy') {
            return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
        }

        if ($periodo === 'semana') {
            return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()];
        }

        if ($periodo === 'mes') {
            return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
        }

        // rango
        $from = $desde ? $now->copy()->parse($desde)->startOfDay() : $now->copy()->startOfMonth();
        $to   = $hasta ? $now->copy()->parse($hasta)->endOfDay() : $now->copy()->endOfDay();

        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }
}
