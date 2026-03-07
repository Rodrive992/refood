@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 py-6">

    <div class="flex items-start justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">Historial de Caja</h1>
            <p class="text-sm text-gray-600">
                {{ $periodoLabel }} · {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
                @if($turnoSeleccionado)
                    · <span class="font-bold">Turno seleccionado #{{ $turnoSeleccionado->turno }}</span>
                @endif
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

            <div class="lg:col-span-2">
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
                       placeholder="ID venta / ID comanda / ID caja / nota">
            </div>

            <div class="lg:col-span-3">
                <label class="text-xs font-bold text-gray-700">Turno</label>
                <select name="caja_id" class="mt-1 w-full rounded-xl border-gray-300">
                    <option value="">Todos los turnos</option>
                    @foreach($turnos as $t)
                        <option value="{{ $t->id }}" @selected((string)$cajaId === (string)$t->id)>
                            #{{ $t->turno }} · {{ \Carbon\Carbon::parse($t->fecha)->format('d/m/Y') }} · {{ ucfirst($t->estado) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-10">
                <label class="text-xs font-bold text-gray-700">Filtrar por ítem (opcional)</label>
                <input name="q_item" value="{{ $qItem }}"
                       class="mt-1 w-full rounded-xl border-gray-300"
                       placeholder="Ej: pizza / coca / milanesa">
            </div>

            <div class="lg:col-span-2">
                <button class="w-full rounded-xl px-4 py-2 font-extrabold text-white bg-gray-900 hover:opacity-90">
                    Filtrar
                </button>
            </div>
        </div>
    </form>

    {{-- TURNOS --}}
    <section class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-5">
        <div class="px-4 py-4 border-b border-gray-200">
            <h2 class="font-bold text-gray-900">Turnos del período</h2>
            <p class="text-xs text-gray-500 mt-1">
                Podés revisar cada turno, filtrar las ventas por turno y reimprimir el ticket de cierre.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr class="text-left text-xs font-bold text-gray-600">
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Turno</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 text-right">Apertura</th>
                        <th class="px-4 py-3 text-right">Ingresos</th>
                        <th class="px-4 py-3 text-right">Salidas</th>
                        <th class="px-4 py-3 text-right">Propinas</th>
                        <th class="px-4 py-3 text-right">Ventas</th>
                        <th class="px-4 py-3 text-right">Efectivo final</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($turnos as $t)
                        <tr class="text-sm {{ (string)$cajaId === (string)$t->id ? 'bg-amber-50' : '' }}">
                            <td class="px-4 py-3 text-gray-700">
                                {{ \Carbon\Carbon::parse($t->fecha)->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-900">
                                #{{ $t->turno }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-bold
                                    {{ $t->estado === 'cerrada' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ ucfirst($t->estado) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-900">
                                $ {{ number_format((float)$t->efectivo_apertura, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-900">
                                $ {{ number_format((float)$t->ingreso_efectivo, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-900">
                                $ {{ number_format((float)$t->salida_efectivo, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-700">
                                $ {{ number_format((float)$t->propina_ventas, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                {{ (int)$t->cant_ventas }} · $ {{ number_format((float)$t->total_ventas, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-extrabold text-gray-900">
                                $ {{ number_format((float)$t->efectivo_turno, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.caja.historial.index', array_merge(request()->query(), ['caja_id' => $t->id])) }}"
                                       class="px-3 py-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-800">
                                        Ver
                                    </a>

                                    <a href="{{ route('admin.caja.turno.ticket', $t->id) }}"
                                       target="_blank"
                                       class="px-3 py-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-800">
                                        Reimprimir
                                    </a>
                                </div>
                            </td>
                        </tr>

                        @if(!empty($t->observacion))
                            <tr class="bg-gray-50">
                                <td colspan="10" class="px-4 py-2 text-xs text-gray-600">
                                    <span class="font-bold">Observación:</span> {{ $t->observacion }}
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-6 text-sm text-gray-600">
                                No hay turnos para ese período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- RESUMEN --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-3 mb-5">
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
            <div class="text-xs text-gray-500 font-semibold">Propinas</div>
            <div class="text-xl font-extrabold text-emerald-700">$ {{ number_format((float)($resumen->propina ?? 0), 2, ',', '.') }}</div>
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
                <h2 class="font-bold text-gray-900">
                    Ventas del período
                    @if($turnoSeleccionado)
                        <span class="text-sm text-gray-500 font-semibold">· Turno #{{ $turnoSeleccionado->turno }}</span>
                    @endif
                </h2>
                <p class="text-xs text-gray-500 mt-1">Ordenadas por fecha (más recientes arriba).</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr class="text-left text-xs font-bold text-gray-600">
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Venta</th>
                            <th class="px-4 py-3">Turno</th>
                            <th class="px-4 py-3">Comanda</th>
                            <th class="px-4 py-3">Mesa</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-right">Propina</th>
                            <th class="px-4 py-3 text-right">Pagado</th>
                            <th class="px-4 py-3 text-right">Vuelto</th>
                            <th class="px-4 py-3 text-right">Acción</th>
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
                                    #{{ $v->id_caja ?? '—' }}
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
                                <td class="px-4 py-3 text-right font-bold text-emerald-700">
                                    $ {{ number_format((float)($v->propina ?? 0), 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-900">
                                    $ {{ number_format((float)$v->pagado_total, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-900">
                                    $ {{ number_format((float)$v->vuelto, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.ventas.ticket', ['venta' => $v->id, 'back' => route('admin.caja.historial.index', request()->query())]) }}"
                                       target="_blank"
                                       class="px-3 py-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-800">
                                        Reimprimir
                                    </a>
                                </td>
                            </tr>

                            @if(!empty($v->nota))
                                <tr class="bg-gray-50">
                                    <td colspan="10" class="px-4 py-2 text-xs text-gray-600">
                                        <span class="font-bold">Nota:</span> {{ $v->nota }}
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-6 text-sm text-gray-600">
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
                <p class="text-xs text-gray-500 mt-1">Top 30 por importe.</p>
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