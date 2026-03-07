<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caja;
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
        $desde   = $request->get('desde');
        $hasta   = $request->get('hasta');
        $cajaId  = $request->get('caja_id');

        $qItem  = trim((string)$request->get('q_item', ''));
        $qVenta = trim((string)$request->get('q', ''));

        [$from, $to] = $this->resolveRango($periodo, $desde, $hasta);

        /*
        |--------------------------------------------------------------------------
        | TURNOS DEL PERÍODO
        |--------------------------------------------------------------------------
        */
        $ventasAgg = DB::table('ventas')
            ->selectRaw('
                id_caja,
                COUNT(*) as cant_ventas,
                COALESCE(SUM(subtotal),0) as subtotal,
                COALESCE(SUM(descuento),0) as descuento,
                COALESCE(SUM(recargo),0) as recargo,
                COALESCE(SUM(propina),0) as propina,
                COALESCE(SUM(total),0) as total,
                COALESCE(SUM(vuelto),0) as vuelto,
                COALESCE(SUM(pagado_total),0) as pagado_total
            ')
            ->where('id_local', $localId)
            ->groupBy('id_caja');

        $turnos = DB::table('cajas as c')
            ->leftJoinSub($ventasAgg, 'va', function ($join) {
                $join->on('va.id_caja', '=', 'c.id');
            })
            ->where('c.id_local', $localId)
            ->whereBetween('c.fecha', [$from->toDateString(), $to->toDateString()])
            ->orderByDesc('c.fecha')
            ->orderByDesc('c.turno')
            ->selectRaw('
                c.id,
                c.turno,
                c.fecha,
                c.estado,
                c.efectivo_apertura,
                c.ingreso_efectivo,
                c.salida_efectivo,
                c.efectivo_turno,
                c.abierta_at,
                c.cerrada_at,
                c.observacion,
                COALESCE(va.cant_ventas,0) as cant_ventas,
                COALESCE(va.subtotal,0) as subtotal_ventas,
                COALESCE(va.descuento,0) as descuento_ventas,
                COALESCE(va.recargo,0) as recargo_ventas,
                COALESCE(va.propina,0) as propina_ventas,
                COALESCE(va.total,0) as total_ventas,
                COALESCE(va.vuelto,0) as vuelto_ventas,
                COALESCE(va.pagado_total,0) as pagado_total_ventas
            ')
            ->get();

        $turnoSeleccionado = null;
        if (!empty($cajaId)) {
            $turnoSeleccionado = $turnos->firstWhere('id', (int)$cajaId);
        }

        /*
        |--------------------------------------------------------------------------
        | VENTAS DEL PERÍODO / DEL TURNO
        |--------------------------------------------------------------------------
        */
        $ventasQuery = Venta::query()
            ->where('id_local', $localId)
            ->whereBetween('sold_at', [$from, $to])
            ->orderByDesc('sold_at');

        if (!empty($cajaId)) {
            $ventasQuery->where('id_caja', (int)$cajaId);
        }

        if ($qVenta !== '') {
            $ventasQuery->where(function ($sub) use ($qVenta) {
                if (ctype_digit($qVenta)) {
                    $sub->orWhere('id', (int)$qVenta)
                        ->orWhere('id_comanda', (int)$qVenta)
                        ->orWhere('id_caja', (int)$qVenta);
                }
                $sub->orWhere('nota', 'like', "%{$qVenta}%");
            });
        }

        $resumen = (clone $ventasQuery)
            ->reorder()
            ->selectRaw('
                COUNT(*) as cant_ventas,
                COALESCE(SUM(subtotal),0) as subtotal,
                COALESCE(SUM(descuento),0) as descuento,
                COALESCE(SUM(recargo),0) as recargo,
                COALESCE(SUM(propina),0) as propina,
                COALESCE(SUM(total),0) as total,
                COALESCE(SUM(vuelto),0) as vuelto,
                COALESCE(SUM(pagado_total),0) as pagado_total,
                COALESCE(AVG(total),0) as promedio
            ')
            ->first();

        $ventas = $ventasQuery->paginate(20)->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | ITEMS DEL PERÍODO / DEL TURNO
        |--------------------------------------------------------------------------
        */
        $itemsQuery = DB::table('ventas as v')
            ->join('comanda_items as ci', 'ci.id_comanda', '=', 'v.id_comanda')
            ->where('v.id_local', $localId)
            ->whereBetween('v.sold_at', [$from, $to])
            ->where('ci.estado', '!=', 'anulado');

        if (!empty($cajaId)) {
            $itemsQuery->where('v.id_caja', (int)$cajaId);
        }

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

        $periodoLabel = [
            'hoy'    => 'Hoy',
            'semana' => 'Esta semana',
            'mes'    => 'Este mes',
            'rango'  => 'Rango',
        ][$periodo] ?? 'Rango';

        return view('admin.caja.historial.index', compact(
            'turnos',
            'turnoSeleccionado',
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
            'periodoLabel',
            'cajaId'
        ));
    }

    private function resolveRango(string $periodo, ?string $desde, ?string $hasta): array
    {
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

        $from = $desde ? $now->copy()->parse($desde)->startOfDay() : $now->copy()->startOfMonth();
        $to   = $hasta ? $now->copy()->parse($hasta)->endOfDay() : $now->copy()->endOfDay();

        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }
}