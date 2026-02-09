{{-- resources/views/mozo/dashboard.blade.php --}}
@extends('layouts.app')

@section('page_width', 'max-w-7xl')

@section('header')
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl flex items-center justify-center"
                 style="background: var(--rf-primary-soft); color: var(--rf-primary-hover);">
                <span class="font-black">POS</span>
            </div>
            <div>
                <h1 class="text-xl font-extrabold leading-tight" style="color: var(--rf-text);">Mozo</h1>
                <p class="text-xs" style="color: var(--rf-text-light);">
                    Local #{{ auth()->user()->id_local ?? '—' }} • <span id="liveClock">--:--:--</span>
                </p>
            </div>
        </div>

        <div class="hidden md:flex items-center gap-2">
            <a href="{{ route('mozo.dashboard') }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold border rf-hover-lift"
               style="border-color: var(--rf-border); color: var(--rf-text); background: var(--rf-white);">
                Recargar
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
        {{-- Alerts --}}
        @if(session('ok'))
            <div class="mb-4 rounded-2xl border p-3 text-sm"
                 style="border-color: var(--rf-border); background: var(--rf-secondary-soft); color: var(--rf-secondary-hover);">
                ✅ {{ session('ok') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-2xl border p-3 text-sm"
                 style="border-color: var(--rf-border); background: rgba(239,68,68,0.10); color: var(--rf-error);">
                <div class="font-bold mb-1">Revisá lo siguiente:</div>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- =========================
            DESKTOP (3 columnas)
        ========================== --}}
        <div class="hidden md:grid md:grid-cols-12 gap-4">
            <div id="mesasPanelDesktop" class="md:col-span-4 space-y-3">
                @include('mozo.partials.mesa', [
                    'isMobile' => false,
                    'mesas' => $mesas,
                    'comandasActivasPorMesa' => $comandasActivasPorMesa,
                    'mesaSelected' => $mesaActiva,
                ])
            </div>

            <div id="comandaPanelDesktop" class="md:col-span-5 space-y-3">
                @include('mozo.partials.comanda', [
                    'isMobile' => false,
                    'mesaSelected' => $mesaActiva,
                    'comanda' => $comandaActiva,
                    'subtotal' => $subtotal,
                ])
            </div>

            <div id="cuentaPanelDesktop" class="md:col-span-3 space-y-3">
                @include('mozo.partials.cuenta', [
                    'isMobile' => false,
                    'mesaSelected' => $mesaActiva,
                    'comanda' => $comandaActiva,
                    'subtotal' => $subtotal,
                ])
            </div>
        </div>

        {{-- =========================
            MOBILE
        ========================== --}}
        <div class="md:hidden space-y-4">
            <div id="mesasPanelMobile" class="space-y-3">
                @include('mozo.partials.mesa', [
                    'isMobile' => true,
                    'mesas' => $mesas,
                    'comandasActivasPorMesa' => $comandasActivasPorMesa,
                    'mesaSelected' => $mesaActiva,
                ])
            </div>

            <div class="sticky top-[64px] z-30 -mx-4 px-4 py-2"
                 style="background: var(--rf-bg); border-bottom: 1px solid var(--rf-border);">
                <div class="grid grid-cols-2 gap-2">
                    <button id="mobileTabComanda"
                            class="px-3 py-2 rounded-xl text-sm font-bold border rf-transition-smooth"
                            style="border-color: var(--rf-border); background: var(--rf-white);">
                        Comanda
                    </button>
                    <button id="mobileTabCuenta"
                            class="px-3 py-2 rounded-xl text-sm font-bold border rf-transition-smooth"
                            style="border-color: var(--rf-border); background: var(--rf-white);">
                        Cuenta
                    </button>
                </div>
            </div>

            <div id="mobileComandaContent">
                <div id="mobileComandaPanel" class="space-y-3">
                    @include('mozo.partials.comanda', [
                        'isMobile' => true,
                        'mesaSelected' => $mesaActiva,
                        'comanda' => $comandaActiva,
                        'subtotal' => $subtotal,
                    ])
                </div>
            </div>

            <div id="mobileCuentaContent" class="hidden">
                <div id="mobileCuentaPanel" class="space-y-3">
                    @include('mozo.partials.cuenta', [
                        'isMobile' => true,
                        'mesaSelected' => $mesaActiva,
                        'comanda' => $comandaActiva,
                        'subtotal' => $subtotal,
                    ])
                </div>
            </div>
        </div>
    </div>

    {{-- MODALS (los globales) --}}
    @push('modals')
        @include('mozo.modals.comanda')   {{-- modal ocupar --}}
        @include('mozo.modals.add-item')  {{-- modal add items --}}
        {{-- OJO: Solicitar cuenta queda SOLO en partial cuenta (un solo id) --}}
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                initClock();
                initMobileTabs();
                initDelegations();

                if (currentMesaId() > 0 && isMobile()) switchTab('comanda');
            });

            function isMobile() {
                return window.matchMedia('(max-width: 767px)').matches;
            }

            function currentMesaId() {
                const url = new URL(window.location.href);
                const v = parseInt(url.searchParams.get('mesa_id') || '0', 10);
                return Number.isFinite(v) ? v : 0;
            }

            function setMesaIdInUrl(mesaId) {
                const url = new URL(window.location.href);
                if (mesaId && mesaId > 0) url.searchParams.set('mesa_id', String(mesaId));
                else url.searchParams.delete('mesa_id');
                window.history.pushState({}, '', url.toString());
            }

            function cap(s){ return s.charAt(0).toUpperCase() + s.slice(1); }

            function lockBodyScroll(lock) {
                document.body.classList.toggle('modal-open', !!lock);
            }

            function openModal(id) {
                const el = document.getElementById(id);
                if (!el) return;
                el.classList.remove('hidden');
                el.classList.add('flex');
                lockBodyScroll(true);
            }

            function closeModal(id) {
                const el = document.getElementById(id);
                if (!el) return;
                el.classList.add('hidden');
                el.classList.remove('flex');
                lockBodyScroll(false);
            }

            /* -------------------------
               Clock
            ------------------------- */
            function initClock() {
                const clockEl = document.getElementById('liveClock');
                if (!clockEl) return;

                function tick() {
                    const now = new Date();
                    clockEl.textContent = now.toLocaleTimeString('es-AR', {
                        hour: '2-digit', minute: '2-digit', second: '2-digit'
                    });
                }
                tick();
                setInterval(tick, 1000);
            }

            /* -------------------------
               Tabs
            ------------------------- */
            function initMobileTabs() {
                if (!document.getElementById('mobileTabComanda')) return;

                const saved = localStorage.getItem('activeMobileTab');
                const def = (currentMesaId() > 0) ? 'comanda' : 'comanda';
                switchTab(saved || def);

                document.getElementById('mobileTabComanda')?.addEventListener('click', () => switchTab('comanda'));
                document.getElementById('mobileTabCuenta')?.addEventListener('click', () => switchTab('cuenta'));
            }

            function switchTab(tabName) {
                const tabs = ['comanda', 'cuenta'];

                tabs.forEach(tab => {
                    const tabEl = document.getElementById(`mobileTab${cap(tab)}`);
                    const contentEl = document.getElementById(`mobile${cap(tab)}Content`);

                    if (tabEl) {
                        if (tab === tabName) {
                            tabEl.classList.add('rf-mobile-tab-active');
                            tabEl.classList.remove('rf-mobile-tab-inactive');
                        } else {
                            tabEl.classList.add('rf-mobile-tab-inactive');
                            tabEl.classList.remove('rf-mobile-tab-active');
                        }
                    }
                    if (contentEl) contentEl.classList.toggle('hidden', tab !== tabName);
                });

                localStorage.setItem('activeMobileTab', tabName);
            }

            /* -------------------------
               AJAX (anti mesa anterior)
            ------------------------- */
            let rfReq = { mesas: null, comanda: null, cuenta: null };
            let rfToken = 0;

            function abortReq(key){
                try { if (rfReq[key]) rfReq[key].abort(); } catch(_){}
                rfReq[key] = null;
            }

            function skeletonBox() {
                return `
                    <div class="bg-white rounded-2xl border p-6" style="border-color: var(--rf-border);">
                        <div class="animate-pulse space-y-3">
                            <div class="h-6 bg-gray-200 rounded w-1/2"></div>
                            <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                            <div class="h-20 bg-gray-200 rounded"></div>
                        </div>
                    </div>
                `;
            }

            function errorBox(msg){
                return `
                    <div class="bg-white rounded-2xl border p-4 text-sm"
                         style="border-color: var(--rf-border); color: var(--rf-error);">
                        ${msg || 'No se pudo cargar.'}
                        <button class="underline ml-2" onclick="selectMesaFast(currentMesaId())">Reintentar</button>
                    </div>
                `;
            }

            async function refreshMesas(mesaId, token){
                abortReq('mesas');
                const ctrl = new AbortController();
                rfReq.mesas = ctrl;

                const desk = document.getElementById('mesasPanelDesktop');
                const mob  = document.getElementById('mesasPanelMobile');

                const stillValid = () => currentMesaId() === mesaId && rfToken === token;

                try{
                    const urlD = new URL(@json(route('mozo.dashboard.mesas')), window.location.origin);
                    urlD.searchParams.set('view', 'desktop');
                    if (mesaId > 0) urlD.searchParams.set('mesa_id', String(mesaId));

                    const urlM = new URL(@json(route('mozo.dashboard.mesas')), window.location.origin);
                    urlM.searchParams.set('view', 'mobile');
                    if (mesaId > 0) urlM.searchParams.set('mesa_id', String(mesaId));

                    const [rD, rM] = await Promise.allSettled([
                        desk ? fetch(urlD.toString(), {headers:{'X-Requested-With':'XMLHttpRequest'}, signal: ctrl.signal}) : Promise.resolve(null),
                        mob  ? fetch(urlM.toString(), {headers:{'X-Requested-With':'XMLHttpRequest'}, signal: ctrl.signal}) : Promise.resolve(null),
                    ]);

                    if (desk && rD.status === 'fulfilled') {
                        const res = rD.value;
                        if (res && res.ok && stillValid()) desk.innerHTML = await res.text();
                    }
                    if (mob && rM.status === 'fulfilled') {
                        const res = rM.value;
                        if (res && res.ok && stillValid()) mob.innerHTML = await res.text();
                    }
                } catch(_) {} finally {
                    if (rfReq.mesas === ctrl) rfReq.mesas = null;
                }
            }

            async function refreshComanda(mesaId, token){
                abortReq('comanda');
                const ctrl = new AbortController();
                rfReq.comanda = ctrl;

                const desk = document.getElementById('comandaPanelDesktop');
                const mob  = document.getElementById('mobileComandaPanel');

                const stillValid = () => currentMesaId() === mesaId && rfToken === token;

                try{
                    const urlD = new URL(@json(route('mozo.dashboard.comanda')), window.location.origin);
                    urlD.searchParams.set('view','desktop');
                    urlD.searchParams.set('mesa_id', String(mesaId));

                    const urlM = new URL(@json(route('mozo.dashboard.comanda')), window.location.origin);
                    urlM.searchParams.set('view','mobile');
                    urlM.searchParams.set('mesa_id', String(mesaId));

                    const [rD, rM] = await Promise.allSettled([
                        desk ? fetch(urlD.toString(), {headers:{'X-Requested-With':'XMLHttpRequest'}, signal: ctrl.signal}) : Promise.resolve(null),
                        mob  ? fetch(urlM.toString(), {headers:{'X-Requested-With':'XMLHttpRequest'}, signal: ctrl.signal}) : Promise.resolve(null),
                    ]);

                    if (desk && rD.status === 'fulfilled') {
                        const res = rD.value;
                        if (res && res.ok && stillValid()) desk.innerHTML = await res.text();
                        else if (stillValid()) desk.innerHTML = errorBox('No se pudo cargar la comanda.');
                    }

                    if (mob && rM.status === 'fulfilled') {
                        const res = rM.value;
                        if (res && res.ok && stillValid()) mob.innerHTML = await res.text();
                        else if (stillValid()) mob.innerHTML = errorBox('No se pudo cargar la comanda.');
                    }
                } catch(_) {
                    if (desk && stillValid()) desk.innerHTML = errorBox('No se pudo cargar la comanda.');
                    if (mob && stillValid()) mob.innerHTML = errorBox('No se pudo cargar la comanda.');
                } finally {
                    if (rfReq.comanda === ctrl) rfReq.comanda = null;
                }
            }

            async function refreshCuenta(mesaId, token){
                abortReq('cuenta');
                const ctrl = new AbortController();
                rfReq.cuenta = ctrl;

                const desk = document.getElementById('cuentaPanelDesktop');
                const mob  = document.getElementById('mobileCuentaPanel');

                const stillValid = () => currentMesaId() === mesaId && rfToken === token;

                try{
                    const urlD = new URL(@json(route('mozo.dashboard.cuenta')), window.location.origin);
                    urlD.searchParams.set('view','desktop');
                    urlD.searchParams.set('mesa_id', String(mesaId));

                    const urlM = new URL(@json(route('mozo.dashboard.cuenta')), window.location.origin);
                    urlM.searchParams.set('view','mobile');
                    urlM.searchParams.set('mesa_id', String(mesaId));

                    const [rD, rM] = await Promise.allSettled([
                        desk ? fetch(urlD.toString(), {headers:{'X-Requested-With':'XMLHttpRequest'}, signal: ctrl.signal}) : Promise.resolve(null),
                        mob  ? fetch(urlM.toString(), {headers:{'X-Requested-With':'XMLHttpRequest'}, signal: ctrl.signal}) : Promise.resolve(null),
                    ]);

                    if (desk && rD.status === 'fulfilled') {
                        const res = rD.value;
                        if (res && res.ok && stillValid()) desk.innerHTML = await res.text();
                        else if (stillValid()) desk.innerHTML = errorBox('No se pudo cargar la cuenta.');
                    }

                    if (mob && rM.status === 'fulfilled') {
                        const res = rM.value;
                        if (res && res.ok && stillValid()) mob.innerHTML = await res.text();
                        else if (stillValid()) mob.innerHTML = errorBox('No se pudo cargar la cuenta.');
                    }
                } catch(_) {
                    if (desk && stillValid()) desk.innerHTML = errorBox('No se pudo cargar la cuenta.');
                    if (mob && stillValid()) mob.innerHTML = errorBox('No se pudo cargar la cuenta.');
                } finally {
                    if (rfReq.cuenta === ctrl) rfReq.cuenta = null;
                }
            }

            async function selectMesaFast(mesaId){
                if (!mesaId) return;

                rfToken++;
                const token = rfToken;

                abortReq('mesas');
                abortReq('comanda');
                abortReq('cuenta');

                setMesaIdInUrl(mesaId);

                // Skeleton inmediato (evita ver mesa anterior)
                const deskC = document.getElementById('comandaPanelDesktop');
                const mobC  = document.getElementById('mobileComandaPanel');
                const deskK = document.getElementById('cuentaPanelDesktop');
                const mobK  = document.getElementById('mobileCuentaPanel');

                if (deskC) deskC.innerHTML = skeletonBox();
                if (mobC)  mobC.innerHTML  = skeletonBox();
                if (deskK) deskK.innerHTML = skeletonBox();
                if (mobK)  mobK.innerHTML  = skeletonBox();

                if (isMobile()) switchTab('comanda');

                await Promise.allSettled([
                    refreshMesas(mesaId, token),
                    refreshComanda(mesaId, token),
                    refreshCuenta(mesaId, token),
                ]);
            }
            window.selectMesaFast = selectMesaFast;

            /* -------------------------
               Delegaciones (clicks)
            ------------------------- */
            function initDelegations() {
                // ESC cierra
                document.addEventListener('keydown', function (e) {
                    if (e.key !== 'Escape') return;
                    closeModal('modalOcupar');
                    closeModal('modalAddItems');
                    closeModal('modalSolicitarCuenta');
                });

                // Backdrop click cierra
                document.addEventListener('click', function(e){
                    const b = e.target.closest('.rf-modal-backdrop');
                    if (b && e.target === b) {
                        b.classList.add('hidden');
                        b.classList.remove('flex');
                        lockBodyScroll(false);
                    }
                });

                document.addEventListener('click', function(e){
                    // ✅ seleccionar mesa (TU partial usa data-action="select-mesa")
                    const btnMesa = e.target.closest('[data-action="select-mesa"][data-mesa-id]');
                    if (btnMesa) {
                        e.preventDefault();
                        const mesaId = parseInt(btnMesa.getAttribute('data-mesa-id') || '0', 10);
                        if (mesaId) selectMesaFast(mesaId);
                        return;
                    }

                    // ocupar modal
                    const ocupar = e.target.closest('[data-action="ocupar"]');
                    if (ocupar) {
                        e.preventDefault();
                        const mesaId = parseInt(ocupar.getAttribute('data-mesa-id') || '0', 10);
                        const mesaNombre = ocupar.getAttribute('data-mesa-nombre') || '';
                        if (!mesaId) return;

                        const form = document.getElementById('modalOcuparForm');
                        const title = document.getElementById('modalOcuparTitle');
                        const obs = document.getElementById('modalOcuparObs');

                        const tpl = @json(route('mozo.mesas.ocupar', ['mesa' => '__MESA__']));
                        const action = tpl.replace('__MESA__', String(mesaId));

                        if (title) title.textContent = mesaNombre ? `Ocupar ${mesaNombre}` : 'Ocupar mesa';
                        if (form) form.action = action;
                        if (obs) obs.value = '';

                        openModal('modalOcupar');
                        return;
                    }

                    // add-items modal
                    const addItems = e.target.closest('[data-action="add-items"]');
                    if (addItems) {
                        e.preventDefault();
                        const mesaId = parseInt(addItems.getAttribute('data-mesa-id') || '0', 10);
                        if (!mesaId) return;

                        const form = document.getElementById('modalAddItemsForm');
                        const tpl = @json(route('mozo.mesas.items.add', ['mesa' => '__MESA__']));
                        const action = tpl.replace('__MESA__', String(mesaId));
                        if (form) form.action = action;

                        openModal('modalAddItems');
                        return;
                    }

                    // solicitar cuenta (modal está en partial cuenta)
                    const solicitar = e.target.closest('[data-action="open-solicitar-cuenta"]');
                    if (solicitar) {
                        e.preventDefault();
                        openModal('modalSolicitarCuenta');
                        return;
                    }

                    // cerrar modal
                    const close = e.target.closest('[data-action="close-modal"]');
                    if (close) {
                        e.preventDefault();
                        const id = close.getAttribute('data-modal');
                        if (id) closeModal(id);
                        return;
                    }
                });

                // back/forward
                window.addEventListener('popstate', () => {
                    const mesaId = currentMesaId();
                    if (mesaId > 0) selectMesaFast(mesaId);
                });
            }
        </script>
    @endpush
@endsection
