{{-- resources/views/admin/caja/index.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 py-6">

    {{-- Header compacto --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl md:text-3xl font-extrabold" style="color: #0F172A;">Caja</h1>
            <span class="px-3 py-1 rounded-full text-xs font-medium" style="background: #F1F5F9; color: #475569;">
                {{ now()->format('d/m/Y H:i') }}
            </span>

            <div class="flex items-center gap-2 text-xs">
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="font-medium" id="rfPendStatus" style="color: #0F172A;">ON</span>
                </div>

                <button id="rfPendToggleBtn" type="button"
                    class="px-3 py-1 rounded-full text-xs font-medium transition-all"
                    style="background: white; border: 1px solid #E2E8F0; color: #475569;">
                    Pausar
                </button>
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.caja.historial.index') }}"
                class="px-4 py-2 rounded-xl text-sm font-medium transition-all flex items-center gap-2"
                style="background: white; border: 1px solid #E2E8F0; color: #475569;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Historial
            </a>

            <a href="{{ route('admin.caja.mozos.index') }}"
                class="px-4 py-2 rounded-xl text-sm font-medium transition-all flex items-center gap-2"
                style="background: white; border: 1px solid #E2E8F0; color: #475569;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Mozos
            </a>
        </div>
    </div>

    {{-- Alerts compactas --}}
    @if (session('ok'))
        <div class="mb-4 rounded-xl px-4 py-2.5 text-sm flex items-center gap-2" style="background: #ECFDF5; border: 1px solid #D1FAE5; color: #065F46;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('ok') }}
        </div>
    @endif

    @if (session('error') || $errors->any())
        <div class="mb-4 rounded-xl px-4 py-2.5 text-sm" style="background: #FEF2F2; border: 1px solid #FEE2E2; color: #991B1B;">
            @if (session('error'))
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    {{-- Turno compacto --}}
    <div class="rounded-xl mb-6 p-4" style="background: white; border: 1px solid #E2E8F0;">
        @if ($cajaAbierta)
            <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <span class="text-xs font-medium uppercase tracking-wider" style="color: #64748B;">Turno #{{ $cajaAbierta->turno }}</span>
                    </div>

                    <div class="flex items-center gap-3 text-sm">
                        <span style="color: #475569;">Apertura: <strong style="color: #0F172A;">${{ number_format((float) $cajaAbierta->efectivo_apertura, 0, ',', '.') }}</strong></span>
                        <span style="color: #475569;">Ingresos: <strong class="text-emerald-600">${{ number_format((float) $cajaAbierta->ingreso_efectivo, 0, ',', '.') }}</strong></span>
                        <span style="color: #475569;">Salidas: <strong class="text-red-600">${{ number_format((float) $cajaAbierta->salida_efectivo, 0, ',', '.') }}</strong></span>
                        <span style="color: #475569;">Propinas: <strong style="color: #D97706;">${{ number_format((float) ($propinasTurno ?? 0), 0, ',', '.') }}</strong></span>
                        <span style="color: #475569;">Efectivo: <strong style="color: #0F172A;">${{ number_format((float) $cajaAbierta->efectivo_turno, 0, ',', '.') }}</strong></span>
                    </div>
                </div>

                <div class="flex items-center gap-2 ml-auto">
                    <form method="POST" action="{{ route('admin.caja.turno.movimiento') }}" class="flex items-center gap-2">
                        @csrf
                        <select name="tipo" class="h-9 rounded-lg text-xs px-2" style="border: 1px solid #E2E8F0; background: white;">
                            <option value="ingreso">💰 Ingreso</option>
                            <option value="salida">💸 Salida</option>
                        </select>
                        <div class="relative">
                            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-xs" style="color: #64748B;">$</span>
                            <input type="number" step="0.01" min="0.01" name="monto" placeholder="0"
                                class="w-24 h-9 pl-5 pr-2 rounded-lg text-xs" style="border: 1px solid #E2E8F0; background: white;">
                        </div>
                        <input type="text" name="concepto" placeholder="Concepto"
                            class="w-32 h-9 px-2 rounded-lg text-xs" style="border: 1px solid #E2E8F0; background: white;">
                        <button class="h-9 px-3 rounded-lg text-xs font-medium" style="background: #F8FAFC; border: 1px solid #E2E8F0; color: #475569;">
                            Registrar
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.caja.turno.cerrar') }}" class="flex items-center gap-2">
                        @csrf
                        <input type="text" name="observacion" placeholder="Obs. cierre"
                            class="h-9 px-2 rounded-lg text-xs w-32" style="border: 1px solid #E2E8F0; background: white;">
                        <button class="h-9 px-3 rounded-lg text-xs font-medium text-white" style="background: #DC2626;">
                            Cerrar
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                    <span class="text-sm" style="color: #64748B;">No hay turno activo</span>
                </div>

                <form method="POST" action="{{ route('admin.caja.turno.abrir') }}" class="flex items-center gap-2">
                    @csrf
                    <div class="relative">
                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-xs" style="color: #64748B;">$</span>
                        <input type="number" step="0.01" min="0" name="efectivo_inicial" placeholder="Efectivo inicial"
                            class="w-36 h-9 pl-5 pr-2 rounded-lg text-xs" style="border: 1px solid #E2E8F0; background: white;">
                    </div>
                    <input type="text" name="observacion" placeholder="Observación"
                        class="w-48 h-9 px-2 rounded-lg text-xs" style="border: 1px solid #E2E8F0; background: white;">
                    <button class="h-9 px-4 rounded-lg text-xs font-medium text-white" style="background: #0F172A;">
                        Abrir turno
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- Grid principal --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <section class="lg:col-span-7 rounded-xl overflow-hidden" style="background: white; border: 1px solid #E2E8F0;">
            <div class="px-4 py-3 flex items-center justify-between" style="border-bottom: 1px solid #F1F5F9;">
                <h2 class="font-bold" style="color: #0F172A;">Mesas</h2>
                <button id="btnRefreshMesas"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all flex items-center gap-1"
                    style="background: white; border: 1px solid #E2E8F0; color: #475569;">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refrescar
                </button>
            </div>
            <div id="mesasWrap">
                @include('admin.caja.partials.mesas', [
                    'mesas' => $mesas,
                    'pendientesPorMesa' => $pendientesPorMesa,
                ])
            </div>
        </section>

        <aside class="lg:col-span-5 rounded-xl overflow-hidden" style="background: white; border: 1px solid #E2E8F0;">
            <div class="px-4 py-3 flex items-center justify-between" style="border-bottom: 1px solid #F1F5F9;">
                <h2 class="font-bold" style="color: #0F172A;">Pendientes</h2>
                <button id="btnRefreshPendientes"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all flex items-center gap-1"
                    style="background: white; border: 1px solid #E2E8F0; color: #475569;">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refrescar
                </button>
            </div>
            <div id="pendientesWrap">
                @include('admin.caja.partials.pendientes', [
                    'comandasPendientes' => $comandasPendientes,
                ])
            </div>
        </aside>
    </div>
</div>

{{-- Poll general --}}
<script>
(function() {
    const POLL_MS = 3000;

    const btnPend = document.getElementById('btnRefreshPendientes');
    const btnMesas = document.getElementById('btnRefreshMesas');

    const pendientesWrap = document.getElementById('pendientesWrap');
    const mesasWrap = document.getElementById('mesasWrap');

    const statusEl = document.getElementById('rfPendStatus');
    const toggleBtn = document.getElementById('rfPendToggleBtn');

    let enabled = true;
    let timer = null;

    async function refreshPendientes() {
        try {
            const res = await fetch("{{ route('admin.caja.pendientesPoll') }}", {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                cache: 'no-store'
            });

            if (!res.ok) return;
            const data = await res.json();
            if (!data || !data.ok) return;

            if (typeof data.html === 'string' && pendientesWrap) {
                pendientesWrap.innerHTML = data.html;
            }
        } catch (e) {}
    }

    async function refreshMesas() {
        try {
            const res = await fetch("{{ route('admin.caja.mesasPoll') }}", {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                cache: 'no-store'
            });

            if (!res.ok) return;
            const data = await res.json();
            if (!data || !data.ok) return;

            if (typeof data.html === 'string' && mesasWrap) {
                mesasWrap.innerHTML = data.html;
            }
        } catch (e) {}
    }

    async function refreshAll() {
        await Promise.all([
            refreshPendientes(),
            refreshMesas()
        ]);
    }

    function setStatus() {
        if (statusEl) statusEl.textContent = enabled ? 'ON' : 'OFF';
        if (toggleBtn) toggleBtn.textContent = enabled ? 'Pausar' : 'Reanudar';
    }

    function tick() {
        if (!enabled) return;
        refreshAll();
    }

    function start() {
        if (timer) clearInterval(timer);
        timer = setInterval(tick, POLL_MS);
    }

    if (btnPend) btnPend.addEventListener('click', refreshAll);
    if (btnMesas) btnMesas.addEventListener('click', refreshAll);

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            enabled = !enabled;
            setStatus();
            if (enabled) refreshAll();
        });
    }

    window.__rfRefreshCaja = refreshAll;

    setStatus();
    start();
    refreshAll();
})();
</script>

{{-- Toast + impresión --}}
<div id="rfCajaToast"
    class="fixed bottom-5 right-5 z-50 pointer-events-none opacity-0 translate-y-2 transition duration-200 ease-out">
    <div class="pointer-events-auto rounded-2xl border border-emerald-200 bg-white shadow-lg px-4 py-3 flex items-start gap-3">
        <div class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
            ✅
        </div>
        <div class="min-w-0">
            <div class="font-extrabold text-slate-900" id="rfCajaToastTitle">Listo</div>
            <div class="text-sm text-slate-600" id="rfCajaToastMsg">Impreso.</div>
        </div>
        <button type="button" id="rfCajaToastClose" class="ml-2 text-slate-400 hover:text-slate-700 font-bold">✕</button>
    </div>
</div>

<script>
(function() {
    const toast = document.getElementById('rfCajaToast');
    const toastTitle = document.getElementById('rfCajaToastTitle');
    const toastMsg = document.getElementById('rfCajaToastMsg');
    const toastClose = document.getElementById('rfCajaToastClose');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let toastTimer = null;

    function showToast(title, msg) {
        if (!toast) return;
        toastTitle.textContent = title || 'Listo';
        toastMsg.textContent = msg || '';
        toast.classList.remove('opacity-0', 'translate-y-2');
        toast.classList.add('opacity-100', 'translate-y-0');
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(hideToast, 2600);
    }

    function hideToast() {
        if (!toast) return;
        toast.classList.add('opacity-0', 'translate-y-2');
        toast.classList.remove('opacity-100', 'translate-y-0');
    }

    toastClose?.addEventListener('click', hideToast);

    let preticketFrame = document.getElementById('rfPreticketFrame');
    if (!preticketFrame) {
        preticketFrame = document.createElement('iframe');
        preticketFrame.id = 'rfPreticketFrame';
        preticketFrame.style.position = 'fixed';
        preticketFrame.style.right = '0';
        preticketFrame.style.bottom = '0';
        preticketFrame.style.width = '0';
        preticketFrame.style.height = '0';
        preticketFrame.style.border = '0';
        preticketFrame.style.opacity = '0';
        document.body.appendChild(preticketFrame);
    }

    let finalFrame = document.getElementById('rfFinalFrame');
    if (!finalFrame) {
        finalFrame = document.createElement('iframe');
        finalFrame.id = 'rfFinalFrame';
        finalFrame.style.position = 'fixed';
        finalFrame.style.right = '0';
        finalFrame.style.bottom = '0';
        finalFrame.style.width = '0';
        finalFrame.style.height = '0';
        finalFrame.style.border = '0';
        finalFrame.style.opacity = '0';
        document.body.appendChild(finalFrame);
    }

    function printIframeWindow(frame, doneCb) {
        if (!frame || !frame.contentWindow) return;

        try {
            frame.contentWindow.focus();
            frame.contentWindow.print();
        } catch (e) {
            console.warn('Error imprimiendo iframe:', e);
        }

        if (typeof doneCb === 'function') {
            setTimeout(doneCb, 1200);
            setTimeout(doneCb, 2200);
        }
    }

    const seen = new Map();

    function once(key, ms = 2000) {
        const now = Date.now();
        const prev = seen.get(key) || 0;
        if (now - prev < ms) return false;
        seen.set(key, now);
        return true;
    }

    function notifyPreticket(comandaId) {
        const key = 'preticket:' + comandaId;
        if (!once(key, 5000)) return;
        showToast('Pre-ticket impreso', 'Comanda #' + comandaId + ' enviada a impresión.');
    }

    function notifyFinal(ventaId) {
        const key = 'final:' + ventaId;
        if (!once(key, 8000)) return;
        showToast('Cuenta final impresa', 'Venta #' + ventaId + ' enviada a impresión.');
    }

    function notifyTurno(turnoId) {
        const key = 'turno:' + turnoId;
        if (!once(key, 8000)) return;
        showToast('Cierre de turno impreso', 'Turno #' + turnoId + ' enviado a impresión.');
    }

    async function markPreticketPrinted(comandaId) {
        try {
            await fetch(`{{ url('/admin/caja/comandas') }}/${comandaId}/preticket-printed`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
        } catch (e) {
            console.warn('No se pudo marcar preticket como impreso', e);
        }
    }

    let preticketBusy = false;
    let currentPreticketId = null;

    function triggerPreticket(printUrl, comandaId, remote = false) {
        if (!printUrl || !comandaId) return;
        if (preticketBusy) return;

        preticketBusy = true;
        currentPreticketId = Number(comandaId);

        preticketFrame.onload = function() {
            preticketFrame.onload = null;

            if (!remote) {
                printIframeWindow(preticketFrame, async function() {
                    notifyPreticket(currentPreticketId);

                    preticketBusy = false;
                    currentPreticketId = null;

                    if (typeof window.__rfRefreshCaja === 'function') {
                        window.__rfRefreshCaja();
                    }
                });
            }
        };

        preticketFrame.src = `${printUrl}${printUrl.includes('?') ? '&' : '?'}t=${Date.now()}`;
    }

    async function pollPretickets() {
        if (preticketBusy) return;

        try {
            const res = await fetch("{{ route('admin.caja.preticketsPoll') }}", {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                cache: 'no-store'
            });

            if (!res.ok) return;

            const data = await res.json();
            if (!data || !data.ok || !Array.isArray(data.jobs) || !data.jobs.length) return;

            const job = data.jobs[0];
            const comandaId = Number(job.id || 0);
            const url = job.print_url || '';

            if (!comandaId || !url) return;

            const remoteUrl = `${url}${url.includes('?') ? '&' : '?'}autoprint=1`;
            triggerPreticket(remoteUrl, comandaId, true);
        } catch (e) {
            console.warn('Error en pollPretickets:', e);
        }
    }

    document.addEventListener('click', function(e) {
        const a = e.target.closest('.js-print-preticket');
        if (!a) return;

        e.preventDefault();

        const url = a.dataset.printUrl || a.getAttribute('href');
        const comandaId = parseInt(a.dataset.comandaId || '0', 10);

        if (!url) return;
        if (comandaId && !once('click:preticket:' + comandaId, 800)) return;

        triggerPreticket(url, comandaId, false);
    });

    const finalUrl = @json(session('rf_print_final_url'));
    const ventaId = Number(@json(session('rf_venta_id', 0)));

    if (finalUrl && ventaId) {
        setTimeout(function() {
            if (once('auto:final-load:' + ventaId, 8000)) {
                finalFrame.onload = function() {
                    finalFrame.onload = null;
                    printIframeWindow(finalFrame, function() {
                        notifyFinal(ventaId);
                    });
                };

                finalFrame.src = `${finalUrl}${finalUrl.includes('?') ? '&' : '?'}t=${Date.now()}`;
            }
        }, 250);
    }

    const turnoUrl = @json(session('rf_print_turno_url'));
    const turnoId = Number(@json(session('rf_turno_id', 0)));

    if (turnoUrl && turnoId) {
        setTimeout(function() {
            if (once('auto:turno-load:' + turnoId, 8000)) {
                finalFrame.onload = function() {
                    finalFrame.onload = null;
                    printIframeWindow(finalFrame, function() {
                        notifyTurno(turnoId);
                    });
                };

                finalFrame.src = `${turnoUrl}${turnoUrl.includes('?') ? '&' : '?'}t=${Date.now()}`;
            }
        }, 300);
    }

    window.addEventListener('message', async function(ev) {
        const data = ev.data || {};
        if (data.type !== 'RF_PRINT_DONE') return;

        if (data.mode === 'preticket' && data.comanda_id) {
            const comandaId = parseInt(data.comanda_id, 10);

            await markPreticketPrinted(comandaId);
            notifyPreticket(comandaId);

            if (currentPreticketId === comandaId) {
                currentPreticketId = null;
                preticketBusy = false;

                if (typeof window.__rfRefreshCaja === 'function') {
                    window.__rfRefreshCaja();
                }
            }
        }

        if (data.mode === 'final' && data.venta_id) {
            notifyFinal(parseInt(data.venta_id, 10));
        }

        if (data.mode === 'turno' && data.turno_id) {
            notifyTurno(parseInt(data.turno_id, 10));
        }
    });

    setInterval(pollPretickets, 3000);
    pollPretickets();

    window.__rfPrintOpen = function(url) {
        if (url) {
            finalFrame.src = `${url}${url.includes('?') ? '&' : '?'}t=${Date.now()}`;
        }
    };
})();
</script>
@endsection