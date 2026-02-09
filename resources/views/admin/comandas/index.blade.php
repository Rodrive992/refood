@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 py-6">

    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl md:text-2xl font-extrabold text-slate-900">Comandas</h1>
            <p class="text-sm text-slate-600">
                Activas del local (admin). <strong>El cobro se realiza desde CAJA</strong>.
            </p>
        </div>

        <form class="flex flex-col sm:flex-row gap-2" method="GET" action="{{ route('admin.comandas.index') }}">
            <input type="hidden" name="estado" value="{{ $estado }}">

            <div class="flex gap-2">
                <select name="mesa_id" class="rounded-xl border-slate-200">
                    <option value="">Todas las mesas</option>
                    @foreach($mesas as $m)
                        <option value="{{ $m->id }}" @selected((string)$mesaId === (string)$m->id)>
                            {{ $m->nombre }} ({{ $m->estado }})
                        </option>
                    @endforeach
                </select>

                <select name="estado" class="rounded-xl border-slate-200">
                    <option value="activas" @selected($estado==='activas')>Activas</option>
                    <option value="todas" @selected($estado==='todas')>Todas</option>
                    <option value="cerradas" @selected($estado==='cerradas')>Cerradas/Anuladas</option>
                </select>
            </div>

            <div class="flex gap-2">
                <input name="q" value="{{ $q }}" class="rounded-xl border-slate-200 w-full sm:w-64"
                       placeholder="Buscar por #ID u observación...">
                <button class="rounded-xl px-4 py-2 font-semibold text-white bg-emerald-600 hover:bg-emerald-700">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    @if(session('ok'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-900 px-4 py-3">
            {{ session('ok') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @forelse($comandas as $c)
            @php
                $cuentaPedida = (int)($c->cuenta_solicitada ?? 0) === 1;
                $estadoCaja = $c->estado_caja ?? null; // opcional (pendiente/pagada/anulada...)
            @endphp

            {{-- ✅ Abre directamente CAJA --}}
            <a href="{{ route('admin.caja.show', $c) }}"
               class="group rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-md transition overflow-hidden">

                <div class="p-4 md:p-5 flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <div class="text-base md:text-lg font-extrabold text-slate-900">
                                #{{ $c->id }}
                            </div>

                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-slate-100 text-slate-700">
                                {{ $c->estado }}
                            </span>

                            {{-- ✅ Badge cuenta solicitada --}}
                            @if($cuentaPedida)
                                <span class="text-xs font-extrabold px-2 py-1 rounded-full bg-emerald-100 text-emerald-800">
                                    💳 Cuenta solicitada
                                </span>
                            @endif

                            {{-- ✅ Badge estado_caja (si lo usás) --}}
                            @if(!empty($estadoCaja))
                                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                                    Caja: {{ $estadoCaja }}
                                </span>
                            @endif
                        </div>

                        <div class="mt-1 text-sm text-slate-600">
                            Mesa:
                            <span class="font-semibold text-slate-800">
                                {{ $c->mesa->nombre ?? 'Sin mesa' }}
                            </span>
                            · Mozo:
                            <span class="font-semibold text-slate-800">
                                {{ $c->mozo->name ?? '—' }}
                            </span>
                        </div>

                        @if($c->observacion)
                            <div class="mt-2 text-sm text-slate-700 line-clamp-2">
                                <span class="font-semibold">Obs:</span> {{ $c->observacion }}
                            </div>
                        @endif

                        {{-- ✅ Preview de items + notas (SIN tocar controller) --}}
                        @php
                            $previewItems = $c->items()
                                ->select(['id','nombre_snapshot','cantidad','nota'])
                                ->orderBy('id', 'asc')
                                ->take(3)
                                ->get();

                            $totalItems = (int)($c->items_count ?? 0);
                            $previewCount = $previewItems->count();
                            $faltan = max(0, $totalItems - $previewCount);
                        @endphp

                        @if($previewCount > 0)
                            <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                <div class="text-xs font-bold text-slate-600 mb-1">Items solicitados</div>

                                <ul class="space-y-1">
                                    @foreach($previewItems as $it)
                                        <li class="text-sm text-slate-800 leading-snug">
                                            <span class="font-extrabold">
                                                {{ rtrim(rtrim(number_format((float)$it->cantidad, 2, '.', ''), '0'), '.') }}
                                            </span>
                                            <span class="font-semibold">×</span>
                                            <span class="font-semibold">{{ $it->nombre_snapshot }}</span>

                                            @if(!empty($it->nota))
                                                <span class="text-xs text-slate-600 italic">
                                                    — “{{ $it->nota }}”
                                                </span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>

                                @if($faltan > 0)
                                    <div class="mt-1 text-xs font-semibold text-slate-500">
                                        +{{ $faltan }} más…
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="text-right shrink-0">
                        <div class="text-sm text-slate-600">Items</div>
                        <div class="text-lg font-extrabold text-slate-900">{{ (int)$c->items_count }}</div>
                        <div class="text-xs text-slate-500 mt-1">
                            {{ \Carbon\Carbon::parse($c->opened_at)->format('d/m H:i') }}
                        </div>
                    </div>
                </div>

                <div class="px-4 md:px-5 pb-4 md:pb-5">
                    <div class="rounded-xl border px-3 py-2 text-sm transition
                        {{ $cuentaPedida
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-900 group-hover:bg-emerald-100'
                            : 'border-slate-200 bg-slate-50 text-slate-700 group-hover:bg-emerald-50 group-hover:border-emerald-200'
                        }}">
                        Abrir en caja → Imprimir cuenta / Cobrar
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full rounded-2xl border border-slate-200 bg-white p-6 text-slate-700">
                No hay comandas para los filtros seleccionados.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $comandas->links() }}
    </div>
</div>
@endsection
