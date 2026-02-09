@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 py-6">

    <div class="flex items-start justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">Historial de Caja</h1>
            <p class="text-sm text-gray-600">
                {{ $periodoLabel }} · {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.caja.index') }}"
               class="px-4 py-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-sm font-semibold">
                ← Volver a caja
            </a>
        </div>
    </div>

    {{-- FILTROS --}}
    <form method="GET" action="{{ route('admin.caja.historial.index') }}"
          class="bg-white rounded-2xl border border-gray-200 p-4 mb-5">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-end">

            <div class="lg:col-span-3">
                <label class="text-xs font-bold text-gray-700">Período</label>
                <select name="periodo" class="mt-1 w-full rounded-xl border-gray-300">
                    <option value="hoy" @selected($periodo==='hoy')>Hoy</option>
                    <option value="semana" @selected($periodo==='semana')>Esta semana</option>
                    <option value="mes" @selected($periodo==='mes')>Este mes</option>
                    <option value="rango" @selected($periodo==='rango')>Rango</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="text-xs font-bold text-gray-700">Desde</label>
                <input type="date" name="desde" value="{{ $desde }}"
                       class="mt-1 w-full rounded-xl border-gray-300">
            </div>

            <div class="lg:col-span-2">
                <label class="text-xs font-bold text-gray-700">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta }}"
                       class="mt-1 w-full rounded-xl border-gray-300">
            </div>

            <div class="lg:col-span-3">
                <label class="text-xs font-bold text-gray-700">Buscar venta</label>
                <input name="q" value="{{ $qVenta }}"
                       class="mt-1 w-full rounded-xl border-gray-300"
                       placeholder="ID venta / ID comanda / nota">
            </div>

            <div class="lg:col-span-2">
                <button class="w-full rounded-xl px-4 py-2 font-extrabold text-white bg-gray-900 hover:opacity-90">
                    Filtrar
                </button>
            </div>

            <div class="lg:col-span-12">
                <label class="text-xs font-bold text-gray-700">Filtrar por ítem (opcional)</label>
                <input name="q_item" value="{{ $qItem }}"
                       class="mt-1 w-full rounded-xl border-gray-300"
                       placeholder="Ej: pizza / coca / milanesa (busca por nombre del item vendido)">
                <p class="text-xs text-gray-500 mt-1">
                    Esto afecta el ranking de items (derecha). Las ventas se filtran por venta/nota/ids (campo “Buscar venta”).
                </p>
            </div>

        </div>
    </form>

    {{-- RESUMEN --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3 mb-5">
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="text-xs text-gray-500 font-semibold">Ventas</div>
            <div class="text-xl font-extrabold text-gray-900">{{ (int)($resumen->cant_ventas ?? 0) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="text-xs text-gray-500 font-semibold">Total</div>
            <div class="text-xl font-extrabold text-gray-900">$ {{ number_format((float)($resumen->total ?? 0), 2, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="text-xs text-gray-500 font-semibold">Subtotal</div>
            <div class="text-xl font-extrabold text-gray-900">$ {{ number_format((float)($resumen->subtotal ?? 0), 2, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="text-xs text-gray-500 font-semibold">Descuento</div>
            <div class="text-xl font-extrabold text-gray-900">$ {{ number_format((float)($resumen->descuento ?? 0), 2, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="text-xs text-gray-500 font-semibold">Recargo</div>
            <div class="text-xl font-extrabold text-gray-900">$ {{ number_format((float)($resumen->recargo ?? 0), 2, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="text-xs text-gray-500 font-semibold">Promedio</div>
            <div class="text-xl font-extrabold text-gray-900">$ {{ number_format((float)($resumen->promedio ?? 0), 2, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        {{-- LISTADO VENTAS --}}
        <section class="lg:col-span-8 bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-4 border-b border-gray-200">
                <h2 class="font-bold text-gray-900">Ventas del período</h2>
                <p class="text-xs text-gray-500 mt-1">Ordenadas por fecha (más recientes arriba).</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr class="text-left text-xs font-bold text-gray-600">
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Venta</th>
                            <th class="px-4 py-3">Comanda</th>
                            <th class="px-4 py-3">Mesa</th>
                            <th class="px-4 py-3 right text-right">Total</th>
                            <th class="px-4 py-3 right text-right">Pagado</th>
                            <th class="px-4 py-3 right text-right">Vuelto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($ventas as $v)
                            <tr class="text-sm">
                                <td class="px-4 py-3 text-gray-700">
                                    {{ optional($v->sold_at)->format('d/m H:i') }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-900">
                                    #{{ $v->id }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    #{{ $v->id_comanda }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $v->id_mesa ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-extrabold text-gray-900">
                                    $ {{ number_format((float)$v->total, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-900">
                                    $ {{ number_format((float)$v->pagado_total, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-900">
                                    $ {{ number_format((float)$v->vuelto, 2, ',', '.') }}
                                </td>
                            </tr>

                            @if(!empty($v->nota))
                                <tr class="bg-gray-50">
                                    <td colspan="7" class="px-4 py-2 text-xs text-gray-600">
                                        <span class="font-bold">Nota:</span> {{ $v->nota }}
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-sm text-gray-600">
                                    No hay ventas para ese período/filtros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-4 border-t border-gray-200">
                {{ $ventas->links() }}
            </div>
        </section>

        {{-- ITEMS RANKING --}}
        <aside class="lg:col-span-4 bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-4 border-b border-gray-200">
                <h2 class="font-bold text-gray-900">Items del período</h2>
                <p class="text-xs text-gray-500 mt-1">Top 30 por importe (con filtro opcional por nombre).</p>
            </div>

            <div class="p-3 space-y-2">
                @forelse($itemsResumen as $it)
                    <div class="rounded-xl border border-gray-200 p-3">
                        <div class="font-semibold text-gray-900 leading-tight">
                            {{ $it->nombre }}
                        </div>
                        <div class="mt-1 text-xs text-gray-600 flex items-center justify-between">
                            <span>Cant: <b>{{ rtrim(rtrim(number_format((float)$it->cantidad, 2, '.', ''), '0'), '.') }}</b></span>
                            <span>Importe: <b>$ {{ number_format((float)$it->importe, 2, ',', '.') }}</b></span>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-sm text-gray-600">
                        No hay items para ese período/filtro.
                    </div>
                @endforelse
            </div>
        </aside>

    </div>
</div>
@endsection
