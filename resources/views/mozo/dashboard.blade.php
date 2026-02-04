@extends('layouts.app')

@section('content')
    @php
        $mesaSelected = $mesaActiva ?? null;
        $comanda = $comandaActiva ?? null;

        // ✅ NUEVO: mapa mesa_id => comanda activa (para mostrar badge "cuenta" en tarjetas de mesas)
        // Requiere que tu controller mande $comandasActivasPorMesa como collection/array
        // Ej: $comandasActivasPorMesa = Comanda::...->get()->keyBy('id_mesa');
        $comandaActivaPorMesa = $comandasActivasPorMesa ?? [];
    @endphp

    <div class="max-w-7xl mx-auto px-4 md:px-6 py-6">

        {{-- Flash --}}
        @if (session('ok'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-900 px-4 py-3">
                {{ session('ok') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-900 px-4 py-3">
                <div class="font-bold mb-1">Revisá estos errores:</div>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">Mozo · POS</h1>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold"
                    style="background: var(--rf-secondary-soft); color: var(--rf-secondary);">
                    ● En turno
                </span>
                <span class="hidden sm:inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold"
                    style="background: var(--rf-primary-soft); color: var(--rf-primary);">
                    Local: {{ auth()->user()->id_local ?? '—' }}
                </span>
            </div>
        </div>

        {{-- Layout POS --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

            {{-- =========================
             COLUMNA 1: MESAS
        ========================= --}}
            <section class="lg:col-span-4 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-4 py-4 border-b border-slate-200">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-extrabold text-slate-900">Mesas</h2>
                        <div class="text-xs font-semibold px-2 py-1 rounded-full bg-slate-100 text-slate-700">
                            Elegí una mesa
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <div id="mesasWrap">
                        @include('mozo.partials.mesas', [
                            'mesas' => $mesas,
                            'comandasActivasPorMesa' => $comandasActivasPorMesa ?? collect(),
                        ])
                    </div>
                    {{-- Acciones rápidas --}}
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <button type="button" onclick="openMesaModal('ocupar')"
                            class="rounded-xl px-3 py-2 font-semibold text-white disabled:opacity-60 disabled:cursor-not-allowed"
                            style="background: var(--rf-primary);" @disabled(!$mesaSelected)>
                            Ocupar
                        </button>

                        <button type="button" onclick="openMesaModal('reservar')"
                            class="rounded-xl px-3 py-2 font-semibold text-white disabled:opacity-60 disabled:cursor-not-allowed"
                            style="background: var(--rf-secondary);" @disabled(!$mesaSelected)>
                            Reservar
                        </button>
                    </div>

                    <button type="button" onclick="liberarMesaConfirm()"
                        class="mt-2 w-full rounded-xl px-3 py-2 font-semibold text-slate-900 border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-60 disabled:cursor-not-allowed"
                        @disabled(!$mesaSelected)>
                        Liberar mesa
                    </button>

                    {{-- Form oculto liberar --}}
                    @if ($mesaSelected)
                        <form id="formLiberarMesa" method="POST" action="{{ route('mozo.mesas.liberar', $mesaSelected) }}"
                            class="hidden">
                            @csrf
                        </form>
                    @endif
                </div>
            </section>

            {{-- =========================
             COLUMNA 2: COMANDA
        ========================= --}}
            <section class="lg:col-span-5 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-4 py-4 border-b border-slate-200">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-extrabold text-slate-900">Comanda</h2>

                        <div class="text-sm text-slate-600">
                            Mesa:
                            <span class="font-extrabold text-slate-900">
                                {{ $mesaSelected->nombre ?? '—' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    @if (!$mesaSelected)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700">
                            Elegí una mesa para ver/crear su comanda.
                        </div>
                    @else
                        @php
                            $cuentaPedida = $comanda && (int) ($comanda->cuenta_solicitada ?? 0) === 1;
                        @endphp

                        <div class="flex items-center justify-between gap-3 mb-3">
                            <div>
                                <div class="text-sm text-slate-600">Comanda activa</div>
                                <div class="text-lg font-extrabold text-slate-900">
                                    {{ $comanda ? '#' . $comanda->id : '—' }}

                                    @if ($comanda)
                                        <span
                                            class="text-xs font-semibold px-2 py-1 rounded-full bg-slate-100 text-slate-700 ml-2">
                                            {{ $comanda->estado }}
                                        </span>

                                        @if ($cuentaPedida)
                                            <span
                                                class="text-xs font-bold px-2 py-1 rounded-full bg-emerald-100 text-emerald-800 ml-2">
                                                ● cuenta
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            @if (!$comanda)
                                <form method="POST" action="{{ route('mozo.comandas.createForMesa', $mesaSelected) }}">
                                    @csrf
                                    <button type="submit" class="rounded-xl px-4 py-2 font-semibold text-white"
                                        style="background: var(--rf-secondary);">
                                        Crear comanda
                                    </button>
                                </form>
                            @else
                                <button type="button"
                                    onclick="{{ $cuentaPedida ? 'alert(\'Ya se solicitó la cuenta. No se pueden agregar items.\')' : 'openAddItemModal()' }}"
                                    class="rounded-xl px-4 py-2 font-semibold text-white disabled:opacity-60 disabled:cursor-not-allowed"
                                    style="background: var(--rf-primary);" @disabled($cuentaPedida)>
                                    + Agregar items
                                </button>
                            @endif
                        </div>

                        @if ($comanda)
                            <div class="rounded-2xl border border-slate-200 overflow-hidden">
                                <div
                                    class="bg-slate-50 px-4 py-3 border-b border-slate-200 flex justify-between text-sm font-semibold text-slate-700">
                                    <div>Item</div>
                                    <div class="flex gap-6">
                                        <div class="w-16 text-right">Cant</div>
                                        <div class="w-24 text-right">Total</div>
                                    </div>
                                </div>

                                <div class="divide-y divide-slate-200">
                                    @forelse($comanda->items as $it)
                                        <div class="px-4 py-3 flex justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="font-semibold text-slate-900 truncate">
                                                    {{ $it->nombre_snapshot }}
                                                </div>
                                                <div class="text-xs text-slate-600 mt-1">
                                                    $ {{ number_format((float) $it->precio_snapshot, 2, ',', '.') }}
                                                    · <span class="font-semibold">{{ $it->estado }}</span>
                                                </div>

                                                @if ($it->nota)
                                                    <div class="text-xs text-slate-500 mt-1">
                                                        * {{ $it->nota }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="flex items-start gap-4 shrink-0">
                                                <div class="text-right">
                                                    <div class="w-16 text-right font-bold text-slate-900">
                                                        {{ rtrim(rtrim(number_format((float) $it->cantidad, 2, '.', ''), '0'), '.') }}
                                                    </div>
                                                    <div class="w-24 text-right font-extrabold text-slate-900">
                                                        $
                                                        {{ number_format((float) $it->precio_snapshot * (float) $it->cantidad, 2, ',', '.') }}
                                                    </div>
                                                </div>

                                                {{-- Eliminar / Anular --}}
                                                <form method="POST"
                                                    action="{{ route('mozo.comandas.items.delete', $it) }}"
                                                    onsubmit="return confirm('¿Eliminar este item de la comanda?');">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                        class="rounded-lg px-3 py-1.5 text-sm font-extrabold border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 disabled:opacity-60 disabled:cursor-not-allowed"
                                                        @disabled($cuentaPedida)>
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="px-4 py-4 text-sm text-slate-600">
                                            No hay items en la comanda.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-between">
                                <div class="text-slate-600 text-sm">Subtotal</div>
                                <div class="text-xl font-extrabold text-slate-900">
                                    $ {{ number_format((float) $subtotal, 2, ',', '.') }}
                                </div>
                            </div>

                            @if ($cuentaPedida)
                                <div
                                    class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-900 text-sm">
                                    Ya se solicitó la cuenta. Caja realizará el cobro y emitirá el ticket.
                                </div>
                            @endif
                        @endif

                    @endif
                </div>
            </section>

            {{-- =========================
    COLUMNA 3: CUENTA (MOZO)
========================= --}}
            <section class="lg:col-span-3 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-4 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-extrabold text-slate-900">Cuenta</h2>
                    <p class="text-sm text-slate-600 mt-1">Solicitar a caja</p>
                </div>

                <div class="p-4">
                    @if (!$mesaSelected || !$comanda)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700">
                            Seleccioná una mesa con comanda activa para solicitar la cuenta.
                        </div>
                    @else
                        @php
                            $cuentaPedida = (int) ($comanda->cuenta_solicitada ?? 0) === 1;
                        @endphp

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs text-slate-600">Total estimado</div>
                            <div class="text-2xl font-extrabold text-slate-900">
                                $ {{ number_format((float) $subtotal, 2, ',', '.') }}
                            </div>

                            @if ($cuentaPedida)
                                <div
                                    class="mt-2 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold bg-emerald-100 text-emerald-800">
                                    ● Cuenta solicitada
                                </div>

                                @if (!empty($comanda->cuenta_solicitada_at))
                                    <div class="mt-2 text-xs text-slate-500">
                                        {{ \Illuminate\Support\Carbon::parse($comanda->cuenta_solicitada_at)->format('d/m/Y H:i') }}
                                    </div>
                                @endif

                                @if (!empty($comanda->cuenta_solicitada_nota))
                                    <div class="mt-2 text-xs text-slate-600">
                                        <span class="font-semibold">Nota:</span> {{ $comanda->cuenta_solicitada_nota }}
                                    </div>
                                @endif
                            @else
                                <div class="text-xs text-slate-500 mt-1">
                                    El mozo solicita la cuenta. Caja cobra y emite ticket.
                                </div>
                            @endif
                        </div>

                        <button type="button" onclick="openCuentaModal()"
                            class="mt-3 w-full rounded-xl px-4 py-2 font-semibold text-white disabled:opacity-60 disabled:cursor-not-allowed"
                            style="background: var(--rf-secondary);" @disabled($cuentaPedida)>
                            Solicitar cuenta
                        </button>

                        <div class="mt-2 text-xs text-slate-500">
                            Al confirmar, se marca la comanda como <strong>cuenta solicitada</strong> y caja la ve para
                            cobrar.
                        </div>
                    @endif
                </div>
            </section>

            {{-- =========================================================
    MODAL: OCUPAR / RESERVAR (RESPONSIVE)
========================================================= --}}
            <div id="mesaModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
                <div class="absolute inset-0 bg-black/40" onclick="closeMesaModal()"></div>

                <div class="relative h-full w-full p-3 sm:p-6 flex items-end sm:items-center justify-center">
                    <div class="w-[96vw] max-w-md rounded-2xl bg-white shadow-xl border border-slate-200 overflow-hidden">
                        {{-- Header --}}
                        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                            <div class="min-w-0">
                                <div class="text-xs text-slate-500">Mesa</div>
                                <div class="text-lg font-extrabold text-slate-900 truncate">
                                    {{ $mesaSelected->nombre ?? '—' }}
                                </div>
                                <div id="mesaModalTitle" class="text-sm font-semibold text-slate-700"></div>
                            </div>
                            <button type="button" class="text-slate-500 hover:text-slate-800" onclick="closeMesaModal()"
                                aria-label="Cerrar">✕</button>
                        </div>

                        @if ($mesaSelected)
                            <form id="mesaModalForm" method="POST" action="">
                                @csrf

                                {{-- Body --}}
                                <div class="px-4 py-4">
                                    <label class="block text-sm font-semibold text-slate-700">
                                        Observación (opcional)
                                    </label>
                                    <input id="mesaObs" name="observacion"
                                        class="mt-1 w-full rounded-xl border-slate-200 text-sm"
                                        placeholder="Ej: Juan - 2 personas">
                                    <div class="text-xs text-slate-500 mt-2">
                                        Tip: agregá nombre y cantidad para trabajar más rápido.
                                    </div>
                                </div>

                                {{-- Footer --}}
                                <div class="px-4 py-3 border-t border-slate-200 flex gap-2">
                                    <button type="button" onclick="closeMesaModal()"
                                        class="flex-1 rounded-xl px-4 py-2 font-semibold border border-slate-200 bg-white hover:bg-slate-50">
                                        Cancelar
                                    </button>
                                    <button type="submit" class="flex-1 rounded-xl px-4 py-2 font-semibold text-white"
                                        style="background: var(--rf-primary);">
                                        Confirmar
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="p-4 text-slate-700">Seleccioná una mesa primero.</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- =========================================================
    MODAL: AGREGAR ITEMS (MULTI-SELECCIÓN + CATEGORÍAS + RESPONSIVE)
========================================================= --}}
            <div id="addItemModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
                <div class="absolute inset-0 bg-black/40" onclick="closeAddItemModal()"></div>

                <div class="relative h-full w-full p-3 sm:p-6 flex items-end sm:items-center justify-center">
                    <div
                        class="w-[96vw] max-w-2xl max-h-[88vh] rounded-2xl bg-white shadow-xl border border-slate-200 overflow-hidden flex flex-col">

                        {{-- Header fijo --}}
                        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                            <div class="min-w-0">
                                <div class="text-xs text-slate-500">Agregar a comanda</div>
                                <div class="text-lg font-extrabold text-slate-900 truncate">
                                    {{ $comanda ? '#' . $comanda->id : '—' }}
                                </div>
                            </div>
                            <button type="button" class="text-slate-500 hover:text-slate-800"
                                onclick="closeAddItemModal()" aria-label="Cerrar">✕</button>
                        </div>

                        @if ($comanda)
                            @php
                                // ✅ si cuenta ya pedida, no debería poder abrir modal; igual por seguridad mostramos aviso.
                                $cuentaPedida = (int) ($comanda->cuenta_solicitada ?? 0) === 1;

                                $itemsPorCat = collect($cartaItems ?? [])->groupBy(fn($ci) => $ci->id_categoria ?? 0);

                                $catNameMap = [];
                                foreach ($cartaCategorias ?? collect() as $cc) {
                                    $catNameMap[$cc->id] = $cc->nombre;
                                }

                                $catKeys = collect($cartaCategorias ?? [])
                                    ->pluck('id')
                                    ->filter(fn($id) => $itemsPorCat->has($id))
                                    ->values()
                                    ->all();

                                foreach ($itemsPorCat->keys() as $k) {
                                    if ((int) $k !== 0 && !in_array((int) $k, $catKeys, true)) {
                                        $catKeys[] = (int) $k;
                                    }
                                }

                                if ($itemsPorCat->has(0)) {
                                    $catKeys[] = 0;
                                }
                                if (empty($catKeys)) {
                                    $catKeys = [0];
                                }
                            @endphp

                            @if ($cuentaPedida)
                                <div class="p-4">
                                    <div
                                        class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900 text-sm">
                                        Ya se solicitó la cuenta. No se pueden agregar items.
                                    </div>
                                </div>
                            @else
                                <form id="addItemForm" method="POST"
                                    action="{{ route('mozo.comandas.items.add', $comanda) }}"
                                    class="flex flex-col flex-1 min-h-0">
                                    @csrf

                                    {{-- Body scrolleable --}}
                                    <div class="p-4 space-y-3 overflow-y-auto min-h-0">

                                        {{-- Buscar --}}
                                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                            <label class="block text-sm font-semibold text-slate-700">Buscar</label>
                                            <input id="itemSearch" type="text"
                                                class="mt-1 w-full rounded-xl border-slate-200 text-sm"
                                                placeholder="Ej: coca, mila, pizza..." oninput="filterCartaItems()">
                                            <div class="text-xs text-slate-500 mt-1">
                                                Filtra dentro de la categoría actual.
                                            </div>
                                        </div>

                                        {{-- Seleccionados --}}
                                        <div class="rounded-xl border border-slate-200 p-3">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <div class="text-xs text-slate-500">Seleccionados</div>
                                                    <div class="text-sm font-extrabold text-slate-900" id="selCount">0
                                                        item(s)</div>
                                                </div>

                                                <button type="button"
                                                    class="rounded-lg px-3 py-1 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50"
                                                    onclick="clearSelectedItems()">
                                                    Limpiar
                                                </button>
                                            </div>

                                            <div id="selectedWrap" class="mt-3 space-y-2">
                                                <div class="text-sm text-slate-600">No hay items seleccionados.</div>
                                            </div>

                                            <div class="text-xs text-slate-500 mt-2">
                                                Tip: tocá un item para sumarlo, tocá otra vez para aumentar cantidad.
                                            </div>
                                        </div>

                                        {{-- Tabs + lista --}}
                                        <div class="rounded-2xl border border-slate-200 overflow-hidden">
                                            {{-- Tabs --}}
                                            <div class="bg-white px-3 py-2 border-b border-slate-200">
                                                <div class="flex gap-2 overflow-auto pb-1" id="catTabs">
                                                    @foreach ($catKeys as $idx => $catId)
                                                        @php
                                                            $count = ($itemsPorCat[$catId] ?? collect())->count();
                                                            $label =
                                                                (int) $catId === 0
                                                                    ? 'Sin categoría'
                                                                    : $catNameMap[$catId] ?? 'Categoría ' . $catId;
                                                            $active = $idx === 0;
                                                        @endphp
                                                        <button type="button"
                                                            class="cat-tab whitespace-nowrap rounded-xl px-3 py-1.5 text-sm font-semibold border
                                                           {{ $active ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50' }}"
                                                            data-catid="{{ (int) $catId }}"
                                                            onclick="setActiveCat({{ (int) $catId }}, this)">
                                                            {{ $label }}
                                                            <span
                                                                class="ml-1 text-xs font-bold opacity-70">({{ $count }})</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>

                                            {{-- Header lista --}}
                                            <div
                                                class="bg-slate-50 px-4 py-2 border-b border-slate-200 flex items-center justify-between">
                                                <div class="text-sm font-extrabold text-slate-900">Items</div>
                                                <div class="text-xs text-slate-500">Toque para sumar</div>
                                            </div>

                                            {{-- Panels --}}
                                            <div class="bg-white">
                                                @foreach ($catKeys as $idx => $catId)
                                                    <div class="cat-panel {{ $idx === 0 ? '' : 'hidden' }}"
                                                        data-catid="{{ (int) $catId }}">
                                                        <div
                                                            class="max-h-[260px] sm:max-h-[320px] overflow-auto divide-y divide-slate-200">
                                                            @foreach ($itemsPorCat[$catId] ?? collect() as $ci)
                                                                <button type="button"
                                                                    class="carta-item-btn w-full text-left px-4 py-2 hover:bg-emerald-50 transition"
                                                                    data-id="{{ $ci->id }}"
                                                                    data-nombre="{{ $ci->nombre }}"
                                                                    data-precio="{{ (float) $ci->precio }}">
                                                                    <div class="flex items-start justify-between gap-3">
                                                                        <div class="min-w-0">
                                                                            <div
                                                                                class="font-semibold text-slate-900 truncate">
                                                                                {{ $ci->nombre }}</div>
                                                                            <div class="text-xs text-slate-600 mt-0.5">ID:
                                                                                {{ $ci->id }}</div>
                                                                        </div>
                                                                        <div
                                                                            class="text-sm font-extrabold text-slate-900 shrink-0">
                                                                            $
                                                                            {{ number_format((float) $ci->precio, 2, ',', '.') }}
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            @endforeach

                                                            @if (($itemsPorCat[$catId] ?? collect())->count() === 0)
                                                                <div class="px-4 py-4 text-sm text-slate-600">Sin items.
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="text-xs text-slate-500">
                                            * Si falta algo, revisá que esté <strong>activo</strong> en Admin → Carta.
                                        </div>

                                        {{-- Inputs hidden --}}
                                        <div id="selectedHiddenInputs" class="hidden"></div>
                                    </div>

                                    {{-- Footer fijo --}}
                                    <div class="p-4 border-t border-slate-200 flex gap-2">
                                        <button type="button" onclick="closeAddItemModal()"
                                            class="flex-1 rounded-xl px-4 py-2 font-semibold border border-slate-200 bg-white hover:bg-slate-50">
                                            Cancelar
                                        </button>
                                        <button type="submit"
                                            class="flex-1 rounded-xl px-4 py-2 font-semibold text-white"
                                            style="background: var(--rf-primary);">
                                            Agregar seleccionados
                                        </button>
                                    </div>
                                </form>
                            @endif
                        @else
                            <div class="p-4 text-slate-700">No hay comanda activa.</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- =========================================================
    MODAL: SOLICITAR CUENTA (MOZO)
========================================================= --}}
            <div id="cuentaModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
                <div class="absolute inset-0 bg-black/40" onclick="closeCuentaModal()"></div>

                <div class="relative h-full w-full p-3 sm:p-6 flex items-end sm:items-center justify-center">
                    <div
                        class="w-[96vw] max-w-lg max-h-[88vh] rounded-2xl bg-white shadow-xl border border-slate-200 overflow-hidden flex flex-col">

                        {{-- Header --}}
                        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                            <div class="min-w-0">
                                <div class="text-xs text-slate-500">Solicitar cuenta</div>
                                <div class="text-lg font-extrabold text-slate-900 truncate">
                                    {{ $mesaSelected->nombre ?? '—' }} · {{ $comanda ? '#' . $comanda->id : '—' }}
                                </div>
                            </div>
                            <button type="button" class="text-slate-500 hover:text-slate-800"
                                onclick="closeCuentaModal()" aria-label="Cerrar">✕</button>
                        </div>

                        @if ($comanda)
                            @php
                                $cuentaPedida = (int) ($comanda->cuenta_solicitada ?? 0) === 1;
                            @endphp

                            <form id="cuentaForm" method="POST"
                                action="{{ route('mozo.comandas.solicitarCuenta', $comanda) }}"
                                class="flex flex-col flex-1 min-h-0">
                                @csrf

                                {{-- Body scroll --}}
                                <div class="p-4 space-y-4 overflow-y-auto min-h-0">

                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="text-xs text-slate-600">Total estimado</div>
                                        <div class="text-2xl font-extrabold text-slate-900" id="cuentaSubtotal">
                                            $ {{ number_format((float) $subtotal, 2, ',', '.') }}
                                        </div>
                                        <div class="text-xs text-slate-500 mt-1">
                                            Caja realizará el cobro y emitirá el ticket.
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700">Nota para caja
                                            (opcional)</label>
                                        <input name="nota" class="mt-1 w-full rounded-xl border-slate-200"
                                            placeholder="Ej: pagan separados / factura A / descuento acordado"
                                            value="{{ old('nota', $comanda->cuenta_solicitada_nota) }}"
                                            @disabled($cuentaPedida)>
                                        <div class="text-xs text-slate-500 mt-2">
                                            Tip: aclaraciones de pago o indicaciones especiales.
                                        </div>
                                    </div>

                                    @if ($cuentaPedida)
                                        <div
                                            class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-emerald-900 text-sm">
                                            Esta comanda ya tiene la cuenta solicitada.
                                        </div>
                                    @endif
                                </div>

                                {{-- Footer fijo --}}
                                <div class="p-4 border-t border-slate-200 flex gap-2">
                                    <button type="button" onclick="closeCuentaModal()"
                                        class="flex-1 rounded-xl px-4 py-2 font-semibold border border-slate-200 bg-white hover:bg-slate-50">
                                        Cancelar
                                    </button>

                                    <button type="submit"
                                        class="flex-1 rounded-xl px-4 py-2 font-semibold text-white disabled:opacity-60 disabled:cursor-not-allowed"
                                        style="background: var(--rf-secondary);" @disabled($cuentaPedida)>
                                        Confirmar solicitud
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="p-4 text-slate-700">No hay comanda activa.</div>
                        @endif
                    </div>
                </div>
            </div>
            {{-- =========================================================
                MODAL: VER RESERVA (OBS) - SOLO LECTURA
                ========================================================= --}}
            <div id="reservaModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
                <div class="absolute inset-0 bg-black/40" onclick="closeReservaModal()"></div>

                <div class="relative h-full w-full p-3 sm:p-6 flex items-end sm:items-center justify-center">
                    <div class="w-[96vw] max-w-md rounded-2xl bg-white shadow-xl border border-slate-200 overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                            <div class="min-w-0">
                                <div class="text-xs text-slate-500">Reserva</div>
                                <div id="reservaMesaTitle" class="text-lg font-extrabold text-slate-900 truncate">Mesa
                                </div>
                            </div>
                            <button type="button" class="text-slate-500 hover:text-slate-800"
                                onclick="closeReservaModal()" aria-label="Cerrar">
                                ✕
                            </button>
                        </div>

                        <div class="px-4 py-4">
                            <div class="text-sm font-semibold text-slate-700">Observación</div>
                            <div id="reservaMesaObs"
                                class="mt-2 rounded-xl border border-slate-200 bg-slate-50 p-3 text-slate-800 text-sm whitespace-pre-line">
                                —
                            </div>

                            <div class="text-xs text-slate-500 mt-3">
                                Tip: la reserva se carga desde “Reservar” con observación (ej: nombre + hora).
                            </div>
                        </div>

                        <div class="px-4 py-3 border-t border-slate-200">
                            <button type="button"
                                class="w-full rounded-xl px-4 py-2 font-semibold border border-slate-200 bg-white hover:bg-slate-50"
                                onclick="closeReservaModal()">
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================================================
    JS (modales + items multi + cuenta) + ESC + lock scroll
========================================================= --}}
            <script>
                // ---------------------------------------
                // Helpers modal (lock scroll + ESC)
                // ---------------------------------------
                function lockBodyScroll(lock) {
                    document.documentElement.classList.toggle('overflow-hidden', !!lock);
                    document.body.classList.toggle('overflow-hidden', !!lock);
                }

                document.addEventListener('keydown', function(e) {
                    if (e.key !== 'Escape') return;

                    if (!document.getElementById('addItemModal')?.classList.contains('hidden')) closeAddItemModal();
                    else if (!document.getElementById('cuentaModal')?.classList.contains('hidden')) closeCuentaModal();
                    else if (!document.getElementById('mesaModal')?.classList.contains('hidden')) closeMesaModal();
                });

                // -------------------------
                // Mesa modal
                // -------------------------
                function openMesaModal(mode) {
                    const modal = document.getElementById('mesaModal');
                    const form = document.getElementById('mesaModalForm');
                    const title = document.getElementById('mesaModalTitle');
                    const obs = document.getElementById('mesaObs');

                    if (!modal || !form || !title) return;

                    if (obs) obs.value = '';

                    if (mode === 'ocupar') {
                        title.textContent = 'Ocupar mesa';
                        form.action = @json($mesaSelected ? route('mozo.mesas.ocupar', $mesaSelected) : '');
                    } else {
                        title.textContent = 'Reservar mesa';
                        form.action = @json($mesaSelected ? route('mozo.mesas.reservar', $mesaSelected) : '');
                    }

                    modal.classList.remove('hidden');
                    lockBodyScroll(true);
                    setTimeout(() => obs?.focus(), 60);
                }

                function closeMesaModal() {
                    document.getElementById('mesaModal')?.classList.add('hidden');
                    lockBodyScroll(false);
                }

                function liberarMesaConfirm() {
                    if (!confirm('¿Liberar la mesa? Esto limpia la observación y la deja en LIBRE.')) return;
                    document.getElementById('formLiberarMesa')?.submit();
                }

                // -------------------------
                // Cuenta modal (MOZO)
                // -------------------------
                function openCuentaModal() {
                    document.getElementById('cuentaModal')?.classList.remove('hidden');
                    lockBodyScroll(true);
                    setTimeout(() => {
                        const inp = document.querySelector('#cuentaForm input[name="nota"]');
                        inp?.focus();
                    }, 60);
                }

                function closeCuentaModal() {
                    document.getElementById('cuentaModal')?.classList.add('hidden');
                    lockBodyScroll(false);
                }

                // =========================================================
                // Add Item Modal (MULTI + Tabs categorías)
                // =========================================================
                let cartaBinded = false;
                let activeCatId = null;

                // { [id_item]: { id_item, nombre, precio, cantidad, nota } }
                let selected = {};

                function openAddItemModal() {
                    document.getElementById('addItemModal')?.classList.remove('hidden');
                    lockBodyScroll(true);

                    // reset
                    selected = {};
                    renderSelected();

                    const search = document.getElementById('itemSearch');
                    if (search) {
                        search.value = '';
                        setTimeout(() => search.focus(), 80);
                    }

                    bindCartaItemButtonsOnce();

                    const firstTab = document.querySelector('#catTabs .cat-tab');
                    if (firstTab) {
                        const cid = parseInt(firstTab.dataset.catid || '0', 10);
                        setActiveCat(cid, firstTab);
                    }

                    filterCartaItems();
                }

                function closeAddItemModal() {
                    document.getElementById('addItemModal')?.classList.add('hidden');
                    lockBodyScroll(false);
                }

                function clearSelectedItems() {
                    selected = {};
                    renderSelected();
                }

                function bindCartaItemButtonsOnce() {
                    if (cartaBinded) return;
                    cartaBinded = true;

                    document.querySelectorAll('.carta-item-btn').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const id = String(btn.dataset.id || '');
                            const nombre = btn.dataset.nombre || '';
                            const precio = num(btn.dataset.precio);

                            if (!id) return;

                            if (!selected[id]) {
                                selected[id] = {
                                    id_item: id,
                                    nombre,
                                    precio,
                                    cantidad: 1,
                                    nota: ''
                                };
                            } else {
                                selected[id].cantidad = num(selected[id].cantidad) + 1;
                            }

                            btn.classList.add('bg-emerald-50');

                            renderSelected();
                        });
                    });

                    document.getElementById('addItemForm')?.addEventListener('submit', function(e) {
                        const keys = Object.keys(selected);
                        if (keys.length === 0) {
                            e.preventDefault();
                            alert('Seleccioná al menos un item.');
                            return;
                        }

                        buildHiddenInputs();
                    });
                }

                function renderSelected() {
                    const wrap = document.getElementById('selectedWrap');
                    const countEl = document.getElementById('selCount');

                    const items = Object.values(selected);

                    if (countEl) countEl.textContent = `${items.length} item(s)`;
                    if (!wrap) return;

                    if (items.length === 0) {
                        wrap.innerHTML = `<div class="text-sm text-slate-600">No hay items seleccionados.</div>`;
                        return;
                    }

                    wrap.innerHTML = items.map(it => {
                        const total = num(it.precio) * num(it.cantidad);

                        return `
                <div class="rounded-xl border border-slate-200 p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-semibold text-slate-900 truncate">${escapeHtml(it.nombre)}</div>
                            <div class="text-xs text-slate-600 mt-0.5">
                                ${moneyAr(it.precio)} · Total: <span class="font-bold">${moneyAr(total)}</span>
                            </div>
                        </div>
                        <button type="button"
                                class="text-red-600 font-extrabold"
                                title="Quitar"
                                onclick="removeSelectedItem('${escapeAttr(it.id_item)}')">✕</button>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Cant.</label>
                            <input type="number" min="0.01" step="0.01"
                                   class="mt-1 w-full rounded-xl border-slate-200 text-sm"
                                   value="${escapeAttr(it.cantidad)}"
                                   oninput="updateSelectedQty('${escapeAttr(it.id_item)}', this.value)">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Nota</label>
                            <input type="text"
                                   class="mt-1 w-full rounded-xl border-slate-200 text-sm"
                                   placeholder="sin sal / extra..."
                                   value="${escapeAttr(it.nota || '')}"
                                   oninput="updateSelectedNota('${escapeAttr(it.id_item)}', this.value)">
                        </div>
                    </div>
                </div>
            `;
                    }).join('');
                }

                function removeSelectedItem(id) {
                    delete selected[String(id)];
                    renderSelected();
                }

                function updateSelectedQty(id, val) {
                    id = String(id);
                    if (!selected[id]) return;

                    const v = Math.max(0.01, num(val));
                    selected[id].cantidad = v;
                    renderSelected();
                }

                function updateSelectedNota(id, val) {
                    id = String(id);
                    if (!selected[id]) return;

                    selected[id].nota = val || '';
                }

                function buildHiddenInputs() {
                    const holder = document.getElementById('selectedHiddenInputs');
                    if (!holder) return;

                    holder.innerHTML = '';
                    const items = Object.values(selected);

                    items.forEach((it, idx) => {
                        holder.insertAdjacentHTML('beforeend', `
                <input type="hidden" name="items[${idx}][id_item]" value="${escapeAttr(it.id_item)}">
                <input type="hidden" name="items[${idx}][cantidad]" value="${escapeAttr(it.cantidad)}">
                <input type="hidden" name="items[${idx}][nota]" value="${escapeAttr(it.nota || '')}">
            `);
                    });
                }

                // Tabs
                function setActiveCat(catId, elBtn) {
                    activeCatId = catId;

                    document.querySelectorAll('#catTabs .cat-tab').forEach(t => {
                        t.classList.remove('bg-emerald-50', 'border-emerald-200', 'text-emerald-900');
                        t.classList.add('bg-white', 'border-slate-200', 'text-slate-700');
                    });

                    if (elBtn) {
                        elBtn.classList.add('bg-emerald-50', 'border-emerald-200', 'text-emerald-900');
                        elBtn.classList.remove('bg-white', 'border-slate-200', 'text-slate-700');
                    }

                    document.querySelectorAll('.cat-panel').forEach(p => {
                        const cid = parseInt(p.dataset.catid || '0', 10);
                        p.classList.toggle('hidden', cid !== catId);
                    });

                    const search = document.getElementById('itemSearch');
                    if (search) search.value = '';

                    filterCartaItems();
                }

                function filterCartaItems() {
                    const q = (document.getElementById('itemSearch')?.value || '').trim().toLowerCase();

                    if (activeCatId === null) {
                        const visiblePanel = document.querySelector('.cat-panel:not(.hidden)');
                        activeCatId = visiblePanel ? parseInt(visiblePanel.dataset.catid || '0', 10) : 0;
                    }

                    const panel = document.querySelector(`.cat-panel[data-catid="${activeCatId}"]`);
                    if (!panel) return;

                    panel.querySelectorAll('.carta-item-btn').forEach(btn => {
                        const nombre = (btn.dataset.nombre || '').toLowerCase();
                        const precio = String(btn.dataset.precio || '').toLowerCase();
                        const ok = q === '' || nombre.includes(q) || precio.includes(q);
                        btn.style.display = ok ? '' : 'none';
                    });
                }

                // -------------------------
                // Utils + Escape
                // -------------------------
                function num(v) {
                    const n = parseFloat(String(v ?? '').replace(',', '.'));
                    return isNaN(n) ? 0 : n;
                }

                function moneyAr(n) {
                    return '$ ' + (Number(n).toLocaleString('es-AR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                }

                function escapeHtml(s) {
                    return String(s ?? '')
                        .replaceAll('&', '&amp;')
                        .replaceAll('<', '&lt;')
                        .replaceAll('>', '&gt;')
                        .replaceAll('"', '&quot;')
                        .replaceAll("'", '&#039;');
                }

                function escapeAttr(s) {
                    return escapeHtml(s);
                }

                async function refreshMesas() {
                    try {
                        const res = await fetch(@json(route('mozo.dashboard.mesas')), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (!res.ok) return;

                        const html = await res.text();
                        const wrap = document.getElementById('mesasWrap');
                        if (!wrap) return;

                        wrap.innerHTML = html;
                    } catch (e) {}
                }

                // cada 5 segundos
                setInterval(refreshMesas, 5000);

                // refrescar al volver a la pestaña
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) refreshMesas();
                });

                function openReservaModal(mesaNombre, observacion) {
                    const modal = document.getElementById('reservaModal');
                    const t = document.getElementById('reservaMesaTitle');
                    const o = document.getElementById('reservaMesaObs');

                    if (!modal) return;

                    if (t) t.textContent = mesaNombre || 'Mesa';
                    if (o) o.textContent = observacion || '—';

                    modal.classList.remove('hidden');
                    lockBodyScroll(true);
                }

                function closeReservaModal() {
                    document.getElementById('reservaModal')?.classList.add('hidden');
                    lockBodyScroll(false);
                }

                // ✅ sumar al ESC
                document.addEventListener('keydown', function(e) {
                    if (e.key !== 'Escape') return;

                    if (!document.getElementById('reservaModal')?.classList.contains('hidden')) closeReservaModal();
                });
            </script>
        @endsection
