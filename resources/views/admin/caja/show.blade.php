@extends('layouts.app')

@section('content')
@php
    $mesa = $comanda->mesa;
    $mozo = $comanda->mozo;
@endphp

<div class="max-w-7xl mx-auto px-4 md:px-6 py-6">

    {{-- Flash / errores --}}
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

        <div class="flex gap-2">
            <a href="{{ route('admin.caja.index') }}"
               class="rounded-xl px-4 py-2 font-semibold border border-slate-200 bg-white hover:bg-slate-50">
                ← Volver a caja
            </a>

            <a href="{{ route('admin.caja.cuenta', $comanda) }}" target="_blank"
               class="rounded-xl px-4 py-2 font-semibold text-white"
               style="background: var(--rf-secondary);">
                Ver cuenta (pre-ticket)
            </a>
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

            <form id="cobroForm" class="p-4 space-y-4" method="POST" action="{{ route('admin.caja.cobrar', $comanda) }}">
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

<script>
    // ==========================
    // Helpers
    // ==========================
    const subtotalBase = Number(@json((float)$subtotal));

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
        recalcTotales();

        const total = Number(window.__totalCobrar ?? 0);

        // Debe haber al menos 1 pago
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
    // Init
    // ==========================
    document.addEventListener('DOMContentLoaded', () => {
        // Arrancamos con 1 pago (por eso antes te aparecían 3 fijos: ahora NO)
        addPagoRow('efectivo', '', '');

        recalcTotales();
    });
</script>
@endsection
