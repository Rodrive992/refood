@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 py-6">

    <div class="flex items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">Caja</h1>
            <p class="text-sm text-gray-600">Cuentas solicitadas, impresión y cobro.</p>
        </div>

        <a href="{{ route('admin.comandas.index', ['estado' => 'activas']) }}"
           class="px-4 py-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-sm font-semibold">
            Ver comandas
        </a>
    </div>

    @if(session('ok'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
            {{ session('ok') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- PENDIENTES -->
        <section class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-4 md:px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="font-bold text-gray-900">Pendientes de caja</h2>
                <span id="pendientesCount" class="text-sm text-gray-600">
                    {{ $comandasPendientes->count() }} pendientes
                </span>
            </div>

            <div id="pendientesWrap">
                @include('admin.caja.partials.pendientes', ['comandasPendientes' => $comandasPendientes])
            </div>
        </section>

        <!-- ESTADO POR MESA -->
        <aside class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-4 border-b border-gray-200">
                <h2 class="font-bold text-gray-900">Mesas</h2>
                <p class="text-xs text-gray-500 mt-1">Vista rápida por mesa</p>
            </div>

            <div class="p-3 space-y-2">
                @foreach($mesas as $m)
                    @php
                        // A prueba: si no mandaste pendientesPorMesa, lo busco en la colección
                        $c = null;
                        if (isset($pendientesPorMesa)) {
                            $c = $pendientesPorMesa->get($m->id);
                        } else {
                            $c = $comandasPendientes->firstWhere('id_mesa', $m->id);
                        }

                        $badge = match($m->estado) {
                            'libre' => 'bg-green-100 text-green-800',
                            'ocupada' => 'bg-red-100 text-red-800',
                            'reservada' => 'bg-yellow-100 text-yellow-800',
                            'cerrando' => 'bg-emerald-100 text-emerald-800',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    @endphp

                    <div class="rounded-xl border border-gray-200 p-3">
                        <div class="flex items-center justify-between">
                            <div class="font-bold text-gray-900">{{ $m->nombre }}</div>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $badge }}">
                                {{ $m->estado }}
                            </span>
                        </div>

                        @if($m->observacion)
                            <div class="mt-2 text-xs text-gray-500">
                                <span class="font-semibold">Obs:</span> {{ $m->observacion }}
                            </div>
                        @endif

                        @if($c)
                            <div class="mt-2 text-xs text-gray-600">
                                Comanda #{{ $c->id }} · {{ $c->estado }}
                                @if((int)($c->cuenta_solicitada ?? 0) === 1)
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full bg-orange-100 text-orange-800 font-bold">
                                        cuenta
                                    </span>
                                @endif
                            </div>

                            <div class="mt-2 flex gap-2">
                                <a class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-white border border-gray-200 hover:bg-gray-50"
                                   href="{{ route('admin.caja.show', $c) }}">
                                    Cobrar
                                </a>

                                <a class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-gray-900 text-white hover:opacity-90"
                                   href="{{ route('admin.caja.cuenta', $c) }}">
                                    Cuenta
                                </a>
                            </div>
                        @else
                            <div class="mt-2 text-xs text-gray-500">Sin cuenta pendiente</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </aside>

    </div>
</div>

<audio id="beep" preload="auto">
    <source src="{{ asset('sounds/beep.mp3') }}" type="audio/mpeg">
</audio>

<script>
    let lastCount = {{ (int)$comandasPendientes->count() }};

    // ✅ unlock de audio (por políticas del navegador)
    let audioUnlocked = false;

    function unlockAudioOnce() {
        if (audioUnlocked) return;

        const a = document.getElementById('beep');
        if (!a) return;

        a.volume = 0.8;

        a.play().then(() => {
            a.pause();
            a.currentTime = 0;
            audioUnlocked = true;
        }).catch(() => {
            // seguirá bloqueado hasta que el navegador permita
            audioUnlocked = false;
        });
    }

    document.addEventListener('click', unlockAudioOnce);
    document.addEventListener('keydown', unlockAudioOnce);

    async function refreshPendientes() {
        try {
            const res = await fetch(@json(route('admin.caja.pendientes')), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!res.ok) return;

            const html = await res.text();
            const wrap = document.getElementById('pendientesWrap');
            if (!wrap) return;

            wrap.innerHTML = html;

            const panel = document.querySelector('#pendientesWrap #pendientesPanel');
            if (!panel) return;

            const newCount = parseInt(panel.dataset.count || '0', 10);

            const countEl = document.getElementById('pendientesCount');
            if (countEl) countEl.textContent = `${newCount} pendientes`;

            if (newCount > lastCount) {
                try { document.getElementById('beep')?.play?.(); } catch(e) {}
                toast('Nueva cuenta solicitada', 'success');
            }

            lastCount = newCount;
        } catch (e) {
            // silencioso
        }
    }

    function toast(msg, type = 'info') {
        const t = document.createElement('div');
        t.className =
            'fixed bottom-4 right-4 z-50 rounded-xl px-4 py-3 shadow-lg border text-sm font-semibold ' +
            (type === 'success'
                ? 'bg-emerald-50 border-emerald-200 text-emerald-900'
                : 'bg-slate-50 border-slate-200 text-slate-800');

        t.textContent = msg;
        document.body.appendChild(t);

        setTimeout(() => t.classList.add('opacity-0'), 2500);
        setTimeout(() => t.remove(), 3000);
    }

    setInterval(refreshPendientes, 5000);

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) refreshPendientes();
    });
</script>

@endsection
