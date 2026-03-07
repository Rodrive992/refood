{{-- resources/views/admin/caja/index.blade.php --}}

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
                        Auto-refresco pendientes y mesas: <b id="rfPendStatus">ON</b>
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

        {{-- Turno --}}
        <section class="bg-white rounded-2xl border border-slate-200 p-4 mb-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <div class="text-xs font-bold text-slate-500">Turno</div>

                    @if ($cajaAbierta)
                        <div class="text-lg font-extrabold text-slate-900">
                            Caja ABIERTA · Turno #{{ $cajaAbierta->turno }} ·
                            {{ optional($cajaAbierta->fecha)->format('d/m/Y') }}
                        </div>
                        <div class="text-sm text-slate-600 mt-1">
                            Apertura: $ {{ number_format((float) $cajaAbierta->efectivo_apertura, 2, ',', '.') }}
                            · Ingresos: $ {{ number_format((float) $cajaAbierta->ingreso_efectivo, 2, ',', '.') }}
                            · Salidas: $ {{ number_format((float) $cajaAbierta->salida_efectivo, 2, ',', '.') }}
                            · Propinas: <span class="font-bold text-emerald-700">$
                                {{ number_format((float) ($propinasTurno ?? 0), 2, ',', '.') }}</span>
                            · Efectivo turno: <span class="font-extrabold text-slate-900">$
                                {{ number_format((float) $cajaAbierta->efectivo_turno, 2, ',', '.') }}</span>
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
                    @if (!$cajaAbierta)
                        <form method="POST" action="{{ route('admin.caja.turno.abrir') }}"
                            class="flex items-center gap-2">
                            @csrf
                            <input type="number" step="0.01" min="0" name="efectivo_inicial"
                                class="w-40 rounded-xl border-slate-200" placeholder="Efectivo inicial">
                            <input type="text" name="observacion" class="w-64 rounded-xl border-slate-200"
                                placeholder="Obs. apertura (opcional)">
                            <button class="rounded-xl px-4 py-2 font-extrabold text-white bg-slate-900 hover:opacity-90">
                                Abrir turno
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.caja.turno.movimiento') }}"
                            class="flex items-center gap-2">
                            @csrf
                            <select name="tipo" class="rounded-xl border-slate-200">
                                <option value="ingreso">Ingreso</option>
                                <option value="salida">Salida</option>
                            </select>

                            <input type="number" step="0.01" min="0.01" name="monto"
                                class="w-40 rounded-xl border-slate-200" placeholder="Monto">

                            <input type="text" name="concepto" class="w-64 rounded-xl border-slate-200"
                                placeholder="Concepto (opcional)">

                            <button
                                class="rounded-xl px-4 py-2 font-extrabold border border-slate-200 bg-white hover:bg-slate-50 text-slate-900">
                                Registrar
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.caja.turno.cerrar') }}"
                            class="flex items-center gap-2">
                            @csrf
                            <input type="text" name="observacion"
                                class="w-64 rounded-xl border border-slate-200 rounded-xl"
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
                <div class="px-4 py-4 border-b border-slate-200 flex items-center justify-between gap-2">
                    <div>
                        <h2 class="font-extrabold text-slate-900">Mesas</h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Estado general en vivo. Solo informativo; pasá el cursor por encima para ver detalles.
                        </p>
                    </div>

                    <button id="btnRefreshMesas"
                        class="rounded-xl px-3 py-2 text-xs font-extrabold border border-slate-200 bg-white hover:bg-slate-50">
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

            {{-- Pendientes --}}
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
                    @include('admin.caja.partials.pendientes', [
                        'comandasPendientes' => $comandasPendientes,
                    ])
                </div>
            </aside>
        </div>
    </div>

    {{-- Poll pendientes + mesas --}}
    <script>
        (function() {
            const POLL_MS = 3000;

            const btnPend = document.getElementById('btnRefreshPendientes');
            const btnMesas = document.getElementById('btnRefreshMesas');

            const pendientesWrap = document.getElementById('pendientesWrap');
            const mesasWrap = document.getElementById('mesasWrap');

            const statusEl = document.getElementById('rfPendStatus');
            const lastSyncEl = document.getElementById('rfPendLastSync');
            const toggleBtn = document.getElementById('rfPendToggleBtn');
            const badge = document.getElementById('rfPendBadge');

            let enabled = true;
            let timer = null;

            function getDomPendCount() {
                const panel = document.getElementById('pendientesPanel');
                const c = panel ? parseInt(panel.getAttribute('data-count') || '0', 10) : 0;
                return isNaN(c) ? 0 : c;
            }

            let lastPendCount = getDomPendCount();

            function nowStr() {
                const d = new Date();
                const hh = String(d.getHours()).padStart(2, '0');
                const mm = String(d.getMinutes()).padStart(2, '0');
                const ss = String(d.getSeconds()).padStart(2, '0');
                return `${hh}:${mm}:${ss}`;
            }

            function setStatus() {
                if (statusEl) statusEl.textContent = enabled ? 'ON' : 'OFF';
                if (toggleBtn) toggleBtn.textContent = enabled ? 'Pausar' : 'Reanudar';
            }

            function showBadge() {
                if (!badge) return;
                badge.classList.remove('hidden');
                setTimeout(() => badge.classList.add('hidden'), 4000);
            }

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

                    const newCount = parseInt(data.count || 0, 10);
                    if (newCount > lastPendCount) {
                        showBadge();
                    }

                    lastPendCount = newCount;
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

                if (lastSyncEl) {
                    lastSyncEl.textContent = nowStr();
                }
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

            setStatus();
            start();
            refreshAll();
        })();
    </script>

    {{-- Toast + impresión --}}
    <div id="rfCajaToast"
        class="fixed bottom-5 right-5 z-50 pointer-events-none opacity-0 translate-y-2 transition duration-200 ease-out">
        <div
            class="pointer-events-auto rounded-2xl border border-emerald-200 bg-white shadow-lg px-4 py-3 flex items-start gap-3">
            <div class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                ✅
            </div>
            <div class="min-w-0">
                <div class="font-extrabold text-slate-900" id="rfCajaToastTitle">Listo</div>
                <div class="text-sm text-slate-600" id="rfCajaToastMsg">Impreso.</div>
            </div>
            <button type="button" id="rfCajaToastClose" class="ml-2 text-slate-400 hover:text-slate-700 font-bold">
                ✕
            </button>
        </div>
    </div>

    <script>
        (function() {
            const toast = document.getElementById('rfCajaToast');
            const toastTitle = document.getElementById('rfCajaToastTitle');
            const toastMsg = document.getElementById('rfCajaToastMsg');
            const toastClose = document.getElementById('rfCajaToastClose');
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

            let printFrame = document.getElementById('rfPrintFrame');
            if (!printFrame) {
                printFrame = document.createElement('iframe');
                printFrame.id = 'rfPrintFrame';
                printFrame.style.position = 'fixed';
                printFrame.style.right = '0';
                printFrame.style.bottom = '0';
                printFrame.style.width = '0';
                printFrame.style.height = '0';
                printFrame.style.border = '0';
                printFrame.style.opacity = '0';
                document.body.appendChild(printFrame);
            }

            function printIframeWindow(frame, doneCb) {
                if (!frame || !frame.contentWindow) return;

                try {
                    frame.contentWindow.focus();
                    frame.contentWindow.print();
                } catch (e) {}

                if (typeof doneCb === 'function') {
                    setTimeout(doneCb, 900);
                    setTimeout(doneCb, 1800);
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
                if (!once(key)) return;
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

            document.addEventListener('click', function(e) {
                const a = e.target.closest('.js-print-preticket');
                if (!a) return;

                e.preventDefault();

                const url = a.dataset.printUrl || a.getAttribute('href');
                const comandaId = parseInt(a.dataset.comandaId || '0', 10);

                if (!url) return;
                if (comandaId && !once('click:preticket:' + comandaId, 800)) return;

                printFrame.onload = function() {
                    printFrame.onload = null;
                    printIframeWindow(printFrame, function() {
                        notifyPreticket(comandaId);
                    });
                };

                printFrame.src = url;
            });

            const finalUrl = @json(session('rf_print_final_url'));
            const ventaId = Number(@json(session('rf_venta_id', 0)));

            if (finalUrl && ventaId) {
                if (once('auto:final-load:' + ventaId, 8000)) {
                    printFrame.onload = function() {
                        printFrame.onload = null;
                        printIframeWindow(printFrame, function() {
                            notifyFinal(ventaId);
                        });
                    };

                    printFrame.src = finalUrl;
                }
            }

            const turnoUrl = @json(session('rf_print_turno_url'));
            const turnoId = Number(@json(session('rf_turno_id', 0)));

            if (turnoUrl && turnoId) {
                if (once('auto:turno-load:' + turnoId, 8000)) {
                    printFrame.onload = function() {
                        printFrame.onload = null;
                        printIframeWindow(printFrame, function() {
                            notifyTurno(turnoId);
                        });
                    };

                    printFrame.src = turnoUrl;
                }
            }

            window.addEventListener('message', function(ev) {
                const data = ev.data || {};
                if (data.type !== 'RF_PRINT_DONE') return;

                if (data.mode === 'preticket' && data.comanda_id) {
                    notifyPreticket(parseInt(data.comanda_id, 10));
                }

                if (data.mode === 'final' && data.venta_id) {
                    notifyFinal(parseInt(data.venta_id, 10));
                }

                if (data.mode === 'turno' && data.turno_id) {
                    notifyTurno(parseInt(data.turno_id, 10));
                }
            });

            window.__rfPrintOpen = function(url) {
                if (url) printFrame.src = url;
            };
        })();
    </script>
@endsection