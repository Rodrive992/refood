@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 py-6">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Caja</h1>
            <p class="text-sm text-slate-600">Cobros y pendientes por mesa · Turnos de caja</p>

            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    Auto-refresco pendientes: <b id="rfPendStatus">ON</b>
                </span>

                <button id="rfPendToggleBtn" type="button"
                        class="px-3 py-1.5 rounded-full border border-slate-200 bg-white hover:bg-slate-50 font-semibold text-slate-700">
                    Pausar
                </button>

                <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">
                    Última sync: <b id="rfPendLastSync">—</b>
                </span>

                <span id="rfPendBadge"
                      class="hidden inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-amber-100 text-amber-900 font-extrabold">
                    ⚡ Nueva cuenta solicitada
                </span>
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.caja.historial.index') }}"
               class="px-4 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-sm font-extrabold text-slate-800">
                Historial
            </a>

            <a href="{{ route('admin.caja.mozos.index') }}"
               class="px-4 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-sm font-extrabold text-slate-800">
                Mozos
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session('ok'))
        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 font-semibold">
            {{ session('ok') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 font-semibold">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
            <div class="font-extrabold mb-1">Revisá lo siguiente:</div>
            <ul class="list-disc pl-5 text-sm">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Turno / Caja abierta --}}
    <section class="bg-white rounded-2xl border border-slate-200 p-4 mb-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <div class="text-xs font-bold text-slate-500">Turno</div>

                @if($cajaAbierta)
                    <div class="text-lg font-extrabold text-slate-900">
                        Caja ABIERTA · Turno #{{ $cajaAbierta->turno }} · {{ optional($cajaAbierta->fecha)->format('d/m/Y') }}
                    </div>
                    <div class="text-sm text-slate-600 mt-1">
                        Apertura: $ {{ number_format((float)$cajaAbierta->efectivo_apertura, 2, ',', '.') }}
                        · Ingreso: $ {{ number_format((float)$cajaAbierta->ingreso_efectivo, 2, ',', '.') }}
                        · Salida: $ {{ number_format((float)$cajaAbierta->salida_efectivo, 2, ',', '.') }}
                        · Efectivo turno: <span class="font-extrabold text-slate-900">$ {{ number_format((float)$cajaAbierta->efectivo_turno, 2, ',', '.') }}</span>
                    </div>
                @else
                    <div class="text-lg font-extrabold text-slate-900">
                        No hay caja abierta
                    </div>
                    <div class="text-sm text-slate-600 mt-1">
                        Tenés que abrir un turno para poder cobrar.
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Abrir turno --}}
                @if(!$cajaAbierta)
                    <form method="POST" action="{{ route('admin.caja.turno.abrir') }}" class="flex items-center gap-2">
                        @csrf
                        <input type="number" step="0.01" min="0" name="efectivo_inicial"
                               class="w-40 rounded-xl border-slate-200"
                               placeholder="Efectivo inicial">
                        <input type="text" name="observacion"
                               class="w-64 rounded-xl border-slate-200"
                               placeholder="Obs. apertura (opcional)">
                        <button class="rounded-xl px-4 py-2 font-extrabold text-white bg-slate-900 hover:opacity-90">
                            Abrir turno
                        </button>
                    </form>
                @else
                    {{-- Movimiento (ingreso/salida) --}}
                    <form method="POST" action="{{ route('admin.caja.turno.movimiento') }}" class="flex items-center gap-2">
                        @csrf
                        <select name="tipo" class="rounded-xl border-slate-200">
                            <option value="ingreso">Ingreso</option>
                            <option value="salida">Salida</option>
                        </select>

                        <input type="number" step="0.01" min="0.01" name="monto"
                               class="w-40 rounded-xl border-slate-200"
                               placeholder="Monto">

                        <input type="text" name="concepto"
                               class="w-64 rounded-xl border-slate-200"
                               placeholder="Concepto (opcional)">

                        <button class="rounded-xl px-4 py-2 font-extrabold border border-slate-200 bg-white hover:bg-slate-50 text-slate-900">
                            Registrar
                        </button>
                    </form>

                    {{-- Cerrar turno --}}
                    <form method="POST" action="{{ route('admin.caja.turno.cerrar') }}" class="flex items-center gap-2">
                        @csrf
                        <input type="text" name="observacion"
                               class="w-64 rounded-xl border-slate-200"
                               placeholder="Obs. cierre (opcional)">
                        <button class="rounded-xl px-4 py-2 font-extrabold text-white bg-red-600 hover:opacity-90">
                            Cerrar turno
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        {{-- Mesas --}}
        <section class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-4 py-4 border-b border-slate-200">
                <h2 class="font-extrabold text-slate-900">Mesas</h2>
                <p class="text-xs text-slate-500 mt-1">Estado general (caja no opera mesas, solo cobro).</p>
            </div>

            <div class="p-4 grid grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($mesas as $m)
                    @php
                        $estado = $m->estado ?? 'libre';
                        $pendiente = $pendientesPorMesa->get($m->id);
                    @endphp

                    <div class="rounded-2xl border border-slate-200 p-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="font-extrabold text-slate-900 truncate">{{ $m->nombre }}</div>
                                <div class="text-xs text-slate-600 mt-1">
                                    Estado: <span class="font-bold">{{ $estado }}</span>
                                </div>

                                @if(!empty($m->observacion))
                                    <div class="text-xs text-slate-500 mt-1 truncate">
                                        {{ $m->observacion }}
                                    </div>
                                @endif
                            </div>

                            @if($pendiente)
                                <div class="shrink-0 text-xs font-extrabold px-2 py-1 rounded-full bg-amber-100 text-amber-800">
                                    Cuenta
                                </div>
                            @endif
                        </div>

                        @if($pendiente)
                            <div class="mt-3">
                                <a href="{{ route('admin.caja.show', $pendiente) }}"
                                   class="block text-center rounded-xl px-3 py-2 font-extrabold text-white bg-slate-900 hover:opacity-90">
                                    Cobrar (#{{ $pendiente->id }})
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Pendientes (refrescable) --}}
        <aside class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-4 py-4 border-b border-slate-200 flex items-center justify-between gap-2">
                <div>
                    <h2 class="font-extrabold text-slate-900">Pendientes</h2>
                    <p class="text-xs text-slate-500 mt-1">Comandas con cuenta solicitada.</p>
                </div>

                <button id="btnRefreshPendientes"
                        class="rounded-xl px-3 py-2 text-xs font-extrabold border border-slate-200 bg-white hover:bg-slate-50">
                    Refrescar
                </button>
            </div>

            <div id="pendientesWrap">
                @include('admin.caja.partials.pendientes', ['comandasPendientes' => $comandasPendientes])
            </div>
        </aside>
    </div>
</div>

<script>
(function(){
    const POLL_MS = 3000;

    const btn = document.getElementById('btnRefreshPendientes');
    const wrap = document.getElementById('pendientesWrap');

    const statusEl = document.getElementById('rfPendStatus');
    const lastSyncEl = document.getElementById('rfPendLastSync');
    const toggleBtn = document.getElementById('rfPendToggleBtn');
    const badge = document.getElementById('rfPendBadge');

    let enabled = true;
    let timer = null;

    // contador actual leído del DOM
    function getDomCount(){
        const panel = document.getElementById('pendientesPanel');
        const c = panel ? parseInt(panel.getAttribute('data-count') || '0', 10) : 0;
        return isNaN(c) ? 0 : c;
    }

    let lastCount = getDomCount();

    function nowStr(){
        const d = new Date();
        const hh = String(d.getHours()).padStart(2,'0');
        const mm = String(d.getMinutes()).padStart(2,'0');
        const ss = String(d.getSeconds()).padStart(2,'0');
        return `${hh}:${mm}:${ss}`;
    }

    function setStatus(){
        statusEl.textContent = enabled ? 'ON' : 'OFF';
        toggleBtn.textContent = enabled ? 'Pausar' : 'Reanudar';
    }

    function showBadge(){
        badge.classList.remove('hidden');
        // se apaga solo a los 4s
        setTimeout(() => badge.classList.add('hidden'), 4000);
    }

    async function refreshPendientes(){
        try{
            const res = await fetch("{{ route('admin.caja.pendientesPoll') }}", {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                cache: 'no-store'
            });

            if(!res.ok) return;
            const data = await res.json();
            if(!data || !data.ok) return;

            if(typeof data.html === 'string'){
                wrap.innerHTML = data.html;
            }

            const newCount = parseInt(data.count || 0, 10);
            if(newCount > lastCount){
                showBadge();
            }
            lastCount = newCount;

            lastSyncEl.textContent = nowStr();

        }catch(e){
            // silencioso
        }
    }

    function tick(){
        if(!enabled) return;
        refreshPendientes();
    }

    function start(){
        if(timer) clearInterval(timer);
        timer = setInterval(tick, POLL_MS);
    }

    if(btn) btn.addEventListener('click', refreshPendientes);

    if(toggleBtn) toggleBtn.addEventListener('click', function(){
        enabled = !enabled;
        setStatus();
        if(enabled) refreshPendientes();
    });

    setStatus();
    start();
    refreshPendientes();
})();
</script>
@endsection