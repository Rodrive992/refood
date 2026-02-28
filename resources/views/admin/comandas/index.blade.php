@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 py-6">

    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl md:text-2xl font-extrabold text-slate-900">Comandas</h1>
            <p class="text-sm text-slate-600">
                Activas del local (admin). <strong>El cobro se realiza desde CAJA</strong>.
            </p>

            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    Auto-refresco: <b id="rfStatus">ON</b>
                </span>

                <button id="rfToggleBtn"
                        type="button"
                        class="px-3 py-1.5 rounded-full border border-slate-200 bg-white hover:bg-slate-50 font-semibold text-slate-700">
                    Pausar
                </button>

                <button id="rfSoundBtn"
                        type="button"
                        class="px-3 py-1.5 rounded-full border border-slate-200 bg-white hover:bg-slate-50 font-semibold text-slate-700">
                    Sonido: ON
                </button>

                <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">
                    Última sync: <b id="rfLastSync">—</b>
                </span>
            </div>
        </div>

        <form id="filterForm" class="flex flex-col sm:flex-row gap-2" method="GET" action="{{ route('admin.comandas.index') }}">
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

    {{-- Contenedor que vamos a refrescar por AJAX --}}
    <div id="cardsWrap">
        @include('admin.comandas._poll_cards', ['comandas' => $comandas])
    </div>

    <div class="mt-6" id="paginationWrap">
        {{ $comandas->links() }}
    </div>
</div>

{{-- Audio (beep) usando WebAudio. No requiere archivos --}}
<script>
(function () {
    const POLL_EVERY_MS = 3000; // 3s
    const POLL_URL = @json(route('admin.comandas.poll'));

    const cardsWrap = document.getElementById('cardsWrap');
    const paginationWrap = document.getElementById('paginationWrap');

    const rfStatus = document.getElementById('rfStatus');
    const rfLastSync = document.getElementById('rfLastSync');

    const rfToggleBtn = document.getElementById('rfToggleBtn');
    const rfSoundBtn  = document.getElementById('rfSoundBtn');

    const filterForm = document.getElementById('filterForm');

    let enabled = true;
    let soundEnabled = true;
    let timer = null;

    // usamos "total" del paginador para detectar nuevas
    let lastTotal = @json((int)$comandas->total());

    function nowStr(){
        const d = new Date();
        const hh = String(d.getHours()).padStart(2,'0');
        const mm = String(d.getMinutes()).padStart(2,'0');
        const ss = String(d.getSeconds()).padStart(2,'0');
        return `${hh}:${mm}:${ss}`;
    }

    function setStatus() {
        rfStatus.textContent = enabled ? 'ON' : 'OFF';
        rfToggleBtn.textContent = enabled ? 'Pausar' : 'Reanudar';
    }

    function setSoundLabel() {
        rfSoundBtn.textContent = soundEnabled ? 'Sonido: ON' : 'Sonido: OFF';
    }

    function beep(){
        if(!soundEnabled) return;

        try{
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if(!AudioCtx) return;

            const ctx = new AudioCtx();
            const o = ctx.createOscillator();
            const g = ctx.createGain();

            o.type = 'sine';
            o.frequency.value = 880;
            g.gain.value = 0.08;

            o.connect(g);
            g.connect(ctx.destination);

            o.start();

            setTimeout(() => {
                o.stop();
                ctx.close();
            }, 180);
        } catch(e){
            // si el navegador bloquea audio hasta interacción del usuario, no rompemos nada
        }
    }

    function buildQueryFromFilters() {
        const fd = new FormData(filterForm);
        const params = new URLSearchParams();
        for (const [k,v] of fd.entries()) {
            if (v !== null && String(v).trim() !== '') params.set(k, v);
        }

        // si el usuario está en otra página del paginador, respetamos page actual
        const url = new URL(window.location.href);
        const page = url.searchParams.get('page');
        if (page) params.set('page', page);

        return params.toString();
    }

    async function poll() {
        if (!enabled) return;

        const qs = buildQueryFromFilters();
        const url = POLL_URL + (qs ? ('?' + qs) : '');

        try{
            const res = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                cache: 'no-store'
            });

            if(!res.ok) return;

            const data = await res.json();
            if(!data || !data.ok) return;

            // Render HTML nuevo
            if (typeof data.cards_html === 'string') {
                cardsWrap.innerHTML = data.cards_html;
            }
            if (typeof data.pagination_html === 'string') {
                paginationWrap.innerHTML = data.pagination_html;
            }

            // Sonido si entra nueva comanda
            const total = parseInt(data.total || 0, 10);
            if (total > lastTotal) {
                beep();
            }
            lastTotal = total;

            rfLastSync.textContent = nowStr();
        } catch(e){
            // silencioso
        }
    }

    function start() {
        if (timer) clearInterval(timer);
        timer = setInterval(poll, POLL_EVERY_MS);
    }

    // Toggle refresco
    rfToggleBtn.addEventListener('click', function(){
        enabled = !enabled;
        setStatus();
        if (enabled) poll();
    });

    // Toggle sonido
    rfSoundBtn.addEventListener('click', function(){
        soundEnabled = !soundEnabled;
        setSoundLabel();

        // "desbloquear" audio con una interacción, por si el navegador lo exige:
        if(soundEnabled) beep();
    });

    // Si cambian filtros, reseteamos el contador total y forzamos poll
    filterForm.addEventListener('submit', function(){
        // deja que navegue normal al index con querystring
        // (pero el auto-refresco ya arranca sobre ese estado)
    });

    // Paginación: capturamos click para no recargar página (opcional)
    // Si preferís recargar normal, podés borrar todo este bloque.
    document.addEventListener('click', function(e){
        const a = e.target.closest('#paginationWrap a');
        if(!a) return;

        e.preventDefault();
        const href = a.getAttribute('href');
        if(!href) return;

        const u = new URL(href, window.location.origin);
        const page = u.searchParams.get('page') || '1';

        const current = new URL(window.location.href);
        current.searchParams.set('page', page);
        window.history.pushState({}, '', current.toString());

        poll();
    });

    setStatus();
    setSoundLabel();
    start();

    // primer poll apenas carga
    poll();
})();
</script>
@endsection