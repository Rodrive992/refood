@extends('layouts.app')

@section('content')
@php
    $mesa = $comanda->mesa;
    $mozo = $comanda->mozo;

    // ✅ Turno/caja
    $hayCaja = !empty($cajaAbierta);
@endphp

<div class="max-w-7xl mx-auto px-4 md:px-6 py-6">

    {{-- Flash / errores --}}
    @if (session('ok'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-900 px-4 py-3">
            {{ session('ok') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-900 px-4 py-3">
            {{ session('error') }}
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

    {{-- ✅ Banner turno/caja --}}
    <div class="mb-4 rounded-2xl border border-slate-200 bg-white shadow-sm p-4">
        @if($hayCaja)
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div>
                    <div class="text-xs font-bold text-slate-500">Turno activo</div>
                    <div class="text-lg font-extrabold text-slate-900">
                        Caja ABIERTA · Turno #{{ $cajaAbierta->turno }} · {{ optional($cajaAbierta->fecha)->format('d/m/Y') }}
                    </div>
                    <div class="text-sm text-slate-600 mt-1">
                        Apertura: $ {{ number_format((float)$cajaAbierta->efectivo_apertura, 2, ',', '.') }}
                        · Ingreso: $ {{ number_format((float)$cajaAbierta->ingreso_efectivo, 2, ',', '.') }}
                        · Salida: $ {{ number_format((float)$cajaAbierta->salida_efectivo, 2, ',', '.') }}
                        · Efectivo turno: <span class="font-extrabold text-slate-900">$ {{ number_format((float)$cajaAbierta->efectivo_turno, 2, ',', '.') }}</span>
                    </div>
                </div>

                <div class="text-xs font-extrabold px-3 py-2 rounded-full bg-emerald-100 text-emerald-800 inline-flex w-fit">
                    OK para cobrar
                </div>
            </div>
        @else
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div>
                    <div class="text-xs font-bold text-slate-500">Turno</div>
                    <div class="text-lg font-extrabold text-slate-900">No hay caja abierta</div>
                    <div class="text-sm text-slate-600 mt-1">
                        Para cobrar, primero abrí un turno en
                        <a href="{{ route('admin.caja.index') }}" class="font-extrabold underline">Caja</a>.
                    </div>
                </div>

                <div class="text-xs font-extrabold px-3 py-2 rounded-full bg-amber-100 text-amber-800 inline-flex w-fit">
                    Bloqueado
                </div>
            </div>
        @endif
    </div>

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl md:text-2xl font-extrabold text-slate-900">
                Caja · Cobrar comanda #{{ $comanda->id }}
            </h1>
            <p class="text-sm text-slate-600">
                Mesa: <span class="font-semibold text-slate-800">{{ $mesa->nombre ?? 'Sin mesa' }}</span>
                · Mozo: <span class="font-semibold text-slate-800">{{ $mozo->name ?? '—' }}</span>
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.caja.index') }}"
               class="rounded-xl px-4 py-2 font-semibold border border-slate-200 bg-white hover:bg-slate-50">
                ← Volver a caja
            </a>

            <a href="{{ route('admin.caja.cuenta', $comanda) }}" target="_blank"
               class="rounded-xl px-4 py-2 font-semibold text-white"
               style="background: var(--rf-secondary);">
                Ver cuenta (pre-ticket)
            </a>

            {{-- + Agregar items (bloqueado si no hay caja) --}}
            <button type="button"
                    class="rounded-xl px-4 py-2 font-semibold text-white {{ !$hayCaja ? 'opacity-50 cursor-not-allowed' : '' }}"
                    style="background: var(--rf-primary);"
                    {{ !$hayCaja ? 'disabled' : '' }}
                    data-action="open-modal"
                    data-modal="modalAddItemsAdmin">
                + Agregar items
            </button>
        </div>
    </div>

    {{-- Layout: Detalle ancho + Cobro --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

        {{-- ======================
            COLUMNA DETALLE (ANCHA)
        ====================== --}}
        <section class="lg:col-span-7 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-4 py-4 border-b border-slate-200">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-extrabold text-slate-900">Detalle</h2>
                    <div class="text-xs font-semibold px-2 py-1 rounded-full bg-slate-100 text-slate-700">
                        Cuenta solicitada:
                        {{ $comanda->cuenta_solicitada_at ? \Carbon\Carbon::parse($comanda->cuenta_solicitada_at)->format('d/m H:i') : '—' }}
                    </div>
                </div>

                @if (!empty($comanda->cuenta_solicitada_nota))
                    <div class="mt-2 text-sm text-slate-700">
                        <span class="font-semibold">Nota mozo:</span>
                        {{ $comanda->cuenta_solicitada_nota }}
                    </div>
                @endif
            </div>

            <div class="p-4">
                <div class="rounded-2xl border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 flex justify-between text-sm font-semibold text-slate-700">
                        <div>Item</div>
                        <div class="flex gap-6">
                            <div class="w-16 text-right">Cant</div>
                            <div class="w-24 text-right">Total</div>
                            <div class="w-20 text-right">Acción</div>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-200">
                        @forelse($comanda->items->where('estado','!=','anulado') as $it)
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
                                            $ {{ number_format((float) $it->precio_snapshot * (float) $it->cantidad, 2, ',', '.') }}
                                        </div>
                                    </div>

                                    {{-- Eliminar item (bloqueado si no hay caja) --}}
                                    <div class="w-20 text-right">
                                        <form method="POST"
                                              action="{{ route('admin.caja.items.delete', $it) }}"
                                              onsubmit="return confirm('¿Eliminar este item?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    {{ !$hayCaja ? 'disabled' : '' }}
                                                    class="rounded-xl px-3 py-2 text-xs font-extrabold border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 {{ !$hayCaja ? 'opacity-50 cursor-not-allowed' : '' }}">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="px-4 py-4 text-sm text-slate-600">
                                No hay items.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <div class="text-slate-600 text-sm">Subtotal</div>
                    <div class="text-xl font-extrabold text-slate-900" id="subtotalText">
                        $ {{ number_format((float) $subtotal, 2, ',', '.') }}
                    </div>
                </div>
            </div>
        </section>

        {{-- ======================
            COLUMNA COBRO
        ====================== --}}
        <section class="lg:col-span-5 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-4 py-4 border-b border-slate-200">
                <h2 class="text-lg font-extrabold text-slate-900">Cobro</h2>
                <p class="text-sm text-slate-600 mt-1">Pagos dinámicos (efectivo / débito / transferencia)</p>
            </div>

            {{-- ✅ aviso si no hay caja --}}
            @if(!$hayCaja)
                <div class="p-4">
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 text-amber-900 px-4 py-3">
                        <div class="font-extrabold">No podés cobrar sin turno abierto.</div>
                        <div class="text-sm mt-1">
                            Volvé a <a class="underline font-extrabold" href="{{ route('admin.caja.index') }}">Caja</a> y abrí el turno.
                        </div>
                    </div>
                </div>
            @endif

            <form id="cobroForm"
                  class="p-4 space-y-4 {{ !$hayCaja ? 'opacity-60 pointer-events-none select-none' : '' }}"
                  method="POST"
                  action="{{ route('admin.caja.cobrar', $comanda) }}">
                @csrf

                {{-- Ajustes --}}
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Descuento</label>
                        <input type="number" step="0.01" min="0" name="descuento"
                               class="mt-1 w-full rounded-xl border-slate-200"
                               placeholder="0,00"
                               value="{{ old('descuento', '0') }}"
                               oninput="recalcTotales()">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Recargo</label>
                        <input type="number" step="0.01" min="0" name="recargo"
                               class="mt-1 w-full rounded-xl border-slate-200"
                               placeholder="0,00"
                               value="{{ old('recargo', '0') }}"
                               oninput="recalcTotales()">
                    </div>
                </div>

                {{-- Totales --}}
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-slate-600">Total a cobrar</div>
                        <div class="text-xl font-extrabold text-slate-900" id="totalCobrarText"></div>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <div class="text-sm text-slate-600">Pagado</div>
                        <div class="text-base font-bold text-slate-900" id="pagadoText"></div>
                    </div>
                    <div class="mt-1 flex items-center justify-between">
                        <div class="text-sm text-slate-600">Vuelto</div>
                        <div class="text-base font-bold text-slate-900" id="vueltoText"></div>
                    </div>

                    <div class="mt-2 text-xs text-slate-500">
                        Si el pagado es menor al total, el sistema no permite confirmar.
                    </div>
                </div>

                {{-- Pagos dinámicos --}}
                <div class="rounded-2xl border border-slate-200 p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-extrabold text-slate-900">Pagos</div>
                            <div class="text-xs text-slate-500">Agregá uno o varios pagos (mixto)</div>
                        </div>

                        <button type="button"
                                class="rounded-xl px-3 py-2 font-semibold text-white"
                                style="background: var(--rf-primary);"
                                onclick="addPagoRow()">
                            + Agregar pago
                        </button>
                    </div>

                    <div id="pagosWrap" class="mt-3 space-y-2"></div>
                </div>

                {{-- Nota --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Nota (opcional)</label>
                    <input name="nota" class="mt-1 w-full rounded-xl border-slate-200"
                           placeholder="Ej: descuento acordado / factura A / etc"
                           value="{{ old('nota') }}">
                </div>

                <button type="submit"
                        class="w-full rounded-xl px-4 py-3 font-extrabold text-white"
                        style="background: var(--rf-secondary);">
                    Cobrar e imprimir ticket
                </button>

                <div class="text-xs text-slate-500">
                    Al confirmar: registra venta, guarda pagos, cierra comanda, libera mesa, imprime y vuelve a caja.
                </div>
            </form>
        </section>
    </div>
</div>

{{-- =========================================================
   MODAL: Agregar items (ADMIN CAJA)
========================================================= --}}
<div id="modalAddItemsAdmin"
     class="hidden fixed inset-0 z-50 rf-modal-backdrop"
     style="background: rgba(0,0,0,0.45);">

    <div class="min-h-full w-full flex items-end md:items-center justify-center p-0 md:p-4 overflow-y-auto">
        <div class="bg-white w-full md:w-[96%] md:max-w-3xl rounded-t-3xl md:rounded-3xl shadow-xl border animate-fade-in
                    flex flex-col max-h-[92vh] md:max-h-[85vh]"
             style="border-color: var(--rf-border);">

            <div class="p-4 border-b flex items-center justify-between gap-3 shrink-0"
                 style="border-color: var(--rf-border);">
                <div>
                    <h3 class="font-bold text-lg">Agregar items (Caja)</h3>
                    <p class="text-sm mt-1" style="color: var(--rf-text-light);">
                        Elegí un item y cantidad. Podés agregar varias líneas.
                    </p>
                </div>

                <button type="button"
                        class="p-2 rounded-xl border rf-hover-lift"
                        style="border-color: var(--rf-border);"
                        data-action="close-modal" data-modal="modalAddItemsAdmin">✕</button>
            </div>

            <div class="p-4 overflow-y-auto rf-scrollbar grow" data-modal-body="1">
                <form id="modalAddItemsAdminForm"
                      method="POST"
                      action="{{ route('admin.caja.items.add', $comanda) }}"
                      class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <div class="sm:col-span-6">
                            <label class="text-sm font-semibold" style="color: var(--rf-text);">Item</label>

                            <select id="rfAdminAddItemSelect"
                                    class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"
                                    style="border-color: var(--rf-border);">
                                <option value="">— Seleccionar —</option>

                                @foreach(($cartaCategorias ?? collect()) as $cat)
                                    <optgroup label="{{ $cat->nombre }}">
                                        @foreach(($cartaItems ?? collect())->where('id_categoria', $cat->id) as $it)
                                            <option value="{{ $it->id }}" data-precio="{{ $it->precio }}">
                                                {{ $it->nombre }} ({{ number_format((float)$it->precio, 0, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach

                                @php
                                    $sinCat = ($cartaItems ?? collect())->whereNull('id_categoria');
                                @endphp
                                @if($sinCat->count())
                                    <optgroup label="Sin categoría">
                                        @foreach($sinCat as $it)
                                            <option value="{{ $it->id }}" data-precio="{{ $it->precio }}">
                                                {{ $it->nombre }} ({{ number_format((float)$it->precio, 0, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="text-sm font-semibold" style="color: var(--rf-text);">Cant.</label>
                            <input id="rfAdminAddItemQty" type="number" min="0.01" step="0.01" value="1"
                                   class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"
                                   style="border-color: var(--rf-border);">
                        </div>

                        <div class="sm:col-span-4">
                            <label class="text-sm font-semibold" style="color: var(--rf-text);">Nota</label>
                            <input id="rfAdminAddItemNote" type="text" placeholder="Opcional"
                                   class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"
                                   style="border-color: var(--rf-border);">
                        </div>

                        <div class="sm:col-span-12 flex justify-end">
                            <button id="rfAdminAddLineBtn" type="button"
                                    class="px-4 py-2 rounded-xl text-sm font-semibold rf-hover-lift"
                                    style="background: var(--rf-secondary); color: white;">
                                + Agregar línea
                            </button>
                        </div>
                    </div>

                    <div class="rounded-2xl border p-3"
                         style="border-color: var(--rf-border); background: var(--rf-bg);">
                        <div class="text-sm font-bold mb-2" style="color: var(--rf-text);">Detalle</div>

                        <div id="rfAdminLines" class="space-y-2">
                            <div class="text-sm" style="color: var(--rf-text-light);">
                                No hay líneas todavía.
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="p-4 border-t shrink-0 flex items-center justify-end gap-2"
                 style="border-color: var(--rf-border);">
                <button type="button"
                        class="px-4 py-2 rounded-xl text-sm font-semibold border"
                        style="border-color: var(--rf-border);"
                        data-action="close-modal" data-modal="modalAddItemsAdmin">
                    Cancelar
                </button>

                <button type="submit"
                        form="modalAddItemsAdminForm"
                        class="px-4 py-2 rounded-xl text-sm font-semibold"
                        style="background: var(--rf-primary); color: white;">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // ==========================
    // Helpers
    // ==========================
    const subtotalBase = Number(@json((float)$subtotal));
    const hayCaja = Boolean(@json($hayCaja));

    function toNumber(v) {
        const n = parseFloat(String(v ?? '').replace(',', '.'));
        return isNaN(n) ? 0 : n;
    }

    function moneyAr(n) {
        return '$ ' + (Number(n).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }

    // ==========================
    // Pagos dinámicos
    // ==========================
    let pagoIndex = 0;

    function pagoRowHtml(idx, tipo = 'efectivo', monto = '', ref = '') {
        return `
        <div class="pago-row rounded-xl border border-slate-200 bg-white p-3" data-idx="${idx}">
            <div class="grid grid-cols-12 gap-2 items-end">
                <div class="col-span-12 sm:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700">Tipo</label>
                    <select name="pagos[${idx}][tipo]" class="mt-1 w-full rounded-xl border-slate-200">
                        <option value="efectivo" ${tipo === 'efectivo' ? 'selected' : ''}>Efectivo</option>
                        <option value="debito" ${tipo === 'debito' ? 'selected' : ''}>Débito</option>
                        <option value="transferencia" ${tipo === 'transferencia' ? 'selected' : ''}>Transferencia</option>
                    </select>
                </div>

                <div class="col-span-12 sm:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700">Monto</label>
                    <input type="number" step="0.01" min="0.01"
                           name="pagos[${idx}][monto]"
                           class="pago-monto mt-1 w-full rounded-xl border-slate-200"
                           placeholder="0,00"
                           value="${monto}"
                           oninput="recalcTotales()"
                           required>
                </div>

                <div class="col-span-10 sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700">Referencia</label>
                    <input type="text" maxlength="120"
                           name="pagos[${idx}][referencia]"
                           class="mt-1 w-full rounded-xl border-slate-200"
                           placeholder="N° op / alias / etc"
                           value="${ref}">
                </div>

                <div class="col-span-2 sm:col-span-1">
                    <button type="button"
                            class="w-full rounded-xl px-3 py-2 font-extrabold border border-red-200 bg-red-50 text-red-700 hover:bg-red-100"
                            title="Quitar"
                            onclick="removePagoRow(${idx})">✕</button>
                </div>
            </div>
        </div>
        `;
    }

    function addPagoRow(tipo, monto, ref) {
        const wrap = document.getElementById('pagosWrap');
        wrap.insertAdjacentHTML('beforeend', pagoRowHtml(pagoIndex, tipo, monto, ref));
        pagoIndex++;
        recalcTotales();
    }

    function removePagoRow(idx) {
        const row = document.querySelector(`.pago-row[data-idx="${idx}"]`);
        if (row) row.remove();
        recalcTotales();
    }

    // ==========================
    // Totales
    // ==========================
    function recalcTotales() {
        const descuento = toNumber(document.querySelector('input[name="descuento"]')?.value);
        const recargo   = toNumber(document.querySelector('input[name="recargo"]')?.value);

        const total = Math.max(0, subtotalBase - descuento + recargo);
        window.__totalCobrar = total;

        let pagado = 0;
        document.querySelectorAll('.pago-monto').forEach(inp => {
            pagado += toNumber(inp.value);
        });

        const vuelto = Math.max(0, pagado - total);

        document.getElementById('totalCobrarText').textContent = moneyAr(total);
        document.getElementById('pagadoText').textContent      = moneyAr(pagado);
        document.getElementById('vueltoText').textContent      = moneyAr(vuelto);
    }

    // ==========================
    // Validación antes de enviar
    // ==========================
    document.getElementById('cobroForm')?.addEventListener('submit', function (e) {
        if (!hayCaja) {
            e.preventDefault();
            alert('No hay turno de caja abierto. Abrí caja antes de cobrar.');
            return;
        }

        recalcTotales();

        const total = Number(window.__totalCobrar ?? 0);

        const montos = Array.from(document.querySelectorAll('.pago-monto'));
        if (montos.length === 0) {
            e.preventDefault();
            alert('Agregá al menos un pago para poder cobrar.');
            return;
        }

        let pagado = 0;
        montos.forEach(inp => pagado += toNumber(inp.value));

        if (pagado + 0.00001 < total) {
            e.preventDefault();
            alert('El monto pagado es menor al total a cobrar. Corregí los montos antes de confirmar.');
            return;
        }

        if (!confirm('¿Confirmar cobro e imprimir ticket?')) {
            e.preventDefault();
        }
    });

    // ==========================
    // MODAL util (open/close)
    // ==========================
    function openModal(id){
        if (!hayCaja) {
            alert('No hay turno abierto. Abrí caja para poder modificar/cobrar.');
            return;
        }
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('hidden');
        el.classList.add('flex');
    }
    function closeModal(id){
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.add('hidden');
        el.classList.remove('flex');
    }

    document.addEventListener('click', function(e){
        const open = e.target.closest('[data-action="open-modal"]');
        if (open) {
            const mid = open.getAttribute('data-modal');
            if (mid) openModal(mid);
        }
        const close = e.target.closest('[data-action="close-modal"]');
        if (close) {
            const mid = close.getAttribute('data-modal');
            if (mid) closeModal(mid);
        }
    });

    // ==========================
    // MODAL Add items (Admin)
    // ==========================
    (function(){
        function qs(id){ return document.getElementById(id); }

        const sel = qs('rfAdminAddItemSelect');
        const qty = qs('rfAdminAddItemQty');
        const note = qs('rfAdminAddItemNote');
        const btn = qs('rfAdminAddLineBtn');
        const lines = qs('rfAdminLines');
        const form = qs('modalAddItemsAdminForm');

        if (!sel || !qty || !note || !btn || !lines || !form) return;

        let idx = 0;

        function renderEmpty(){
            lines.innerHTML = `<div class="text-sm" style="color: var(--rf-text-light);">No hay líneas todavía.</div>`;
        }

        function modalBodyEl(){
            return document.querySelector('#modalAddItemsAdmin [data-modal-body="1"]');
        }

        function escapeHtml(s){
            return String(s || '')
                .replaceAll('&','&amp;')
                .replaceAll('<','&lt;')
                .replaceAll('>','&gt;')
                .replaceAll('"','&quot;')
                .replaceAll("'","&#039;");
        }
        function escapeAttr(s){
            return String(s || '').replaceAll('"','&quot;');
        }

        function addLine(){
            if (!hayCaja) return alert('No hay turno abierto.');

            const idItem = parseInt(sel.value || '0', 10);
            if (!idItem) return alert('Seleccioná un item.');

            const opt = sel.options[sel.selectedIndex];
            const label = opt ? opt.textContent.trim() : ('Item #' + idItem);

            const cantidad = parseFloat(qty.value || '1');
            if (!cantidad || cantidad < 0.01) return alert('Cantidad inválida.');

            const notaVal = (note.value || '').trim();

            if (lines.children.length === 1 && lines.textContent.includes('No hay líneas')) {
                lines.innerHTML = '';
            }

            const row = document.createElement('div');
            row.className = "rounded-xl border p-3 bg-white";
            row.style.borderColor = "var(--rf-border)";

            row.innerHTML = `
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold truncate" style="color: var(--rf-text);">${escapeHtml(label)}</div>
                        ${notaVal ? `<div class="text-xs mt-1" style="color: var(--rf-text-light);">Nota: ${escapeHtml(notaVal)}</div>` : ``}
                    </div>
                    <div class="shrink-0 text-right">
                        <div class="text-xs" style="color: var(--rf-text-light);">Cant.</div>
                        <div class="font-bold" style="color: var(--rf-text);">${cantidad}</div>
                    </div>
                </div>

                <input type="hidden" name="items[${idx}][id_item]" value="${idItem}">
                <input type="hidden" name="items[${idx}][cantidad]" value="${cantidad}">
                <input type="hidden" name="items[${idx}][nota]" value="${escapeAttr(notaVal)}">

                <div class="mt-3 flex justify-end">
                    <button type="button" class="px-3 py-2 rounded-xl text-xs font-semibold border"
                        style="border-color: var(--rf-border); color: var(--rf-error);"
                        data-remove-line="1">
                        Quitar
                    </button>
                </div>
            `;

            idx++;
            lines.appendChild(row);

            sel.value = '';
            qty.value = '1';
            note.value = '';

            const body = modalBodyEl();
            if (body) body.scrollTo({ top: body.scrollHeight, behavior: 'smooth' });
        }

        btn.addEventListener('click', addLine);

        lines.addEventListener('click', function(e){
            const rm = e.target.closest('[data-remove-line="1"]');
            if (!rm) return;
            const row = rm.closest('div.rounded-xl');
            if (row) row.remove();
            if (!lines.children.length) renderEmpty();
        });

        // reset cuando se abre
        document.addEventListener('click', function(e){
            const open = e.target.closest('[data-action="open-modal"][data-modal="modalAddItemsAdmin"]');
            if (!open) return;

            idx = 0;
            renderEmpty();
            form.querySelectorAll('input[type="hidden"][name^="items["]').forEach(n => n.remove());

            sel.value = '';
            qty.value = '1';
            note.value = '';

            const body = modalBodyEl();
            if (body) body.scrollTop = 0;
        });

        form.addEventListener('submit', function(e){
            if (!hayCaja){
                e.preventDefault();
                alert('No hay turno abierto.');
                return;
            }
            const hasAny = form.querySelector('input[type="hidden"][name^="items["]');
            if (!hasAny){
                e.preventDefault();
                alert('Agregá al menos una línea.');
            }
        });

    })();

    // ==========================
    // Init
    // ==========================
    document.addEventListener('DOMContentLoaded', () => {
        addPagoRow('efectivo', '', '');
        recalcTotales();
    });
</script>
@endsection