@extends('layouts.app')

@section('content')
@php
    $mesaNombre = $comanda->mesa->nombre ?? 'Sin mesa';
    $mozoNombre = $comanda->mozo->name ?? ('Mozo #' . ($comanda->id_mozo ?? '-'));

    $esCerrada = in_array($comanda->estado, ['cerrada','anulada'], true);
    $cuentaSolicitada = (int)($comanda->cuenta_solicitada ?? 0) === 1;
@endphp

<div class="max-w-7xl mx-auto px-4 md:px-6 py-6">

    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-extrabold text-gray-900">Comanda #{{ $comanda->id }}</h1>

                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                    {{ $esCerrada ? 'bg-gray-200 text-gray-800' : 'bg-green-100 text-green-800' }}">
                    {{ $comanda->estado }}
                </span>

                @if($cuentaSolicitada && !$esCerrada)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800">
                        cuenta solicitada
                    </span>
                @endif
            </div>

            <div class="text-sm text-gray-600 mt-1">
                <span class="font-semibold">Mesa:</span> {{ $mesaNombre }}
                <span class="mx-2">·</span>
                <span class="font-semibold">Mozo:</span> {{ $mozoNombre }}
                <span class="mx-2">·</span>
                <span class="font-semibold">Apertura:</span> {{ optional($comanda->opened_at)->format('d/m H:i') }}
                @if($esCerrada)
                    <span class="mx-2">·</span>
                    <span class="font-semibold">Cierre:</span> {{ optional($comanda->closed_at)->format('d/m H:i') }}
                @endif
            </div>

            @if(!empty($comanda->observacion))
                <div class="mt-2 text-sm text-gray-700 italic">“{{ $comanda->observacion }}”</div>
            @endif

            @if($cuentaSolicitada && !empty($comanda->cuenta_solicitada_nota))
                <div class="mt-2 text-sm text-gray-700">
                    <span class="font-semibold">Nota cuenta:</span>
                    <span class="italic">“{{ $comanda->cuenta_solicitada_nota }}”</span>
                </div>
            @endif

            @if($cuentaSolicitada && !$esCerrada)
                <div class="mt-3 text-xs px-3 py-2 rounded-xl bg-orange-50 border border-orange-200 text-orange-800">
                    ✅ Esta comanda tiene <b>cuenta solicitada</b>, pero <b>Administración</b> puede seguir agregando/eliminando items.
                    El total estimado se recalcula automáticamente.
                </div>
            @endif
        </div>

        <div class="shrink-0 flex flex-col sm:flex-row gap-2">
            <a href="{{ route('admin.comandas.index') }}"
               class="px-4 py-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-sm font-semibold">
                Volver
            </a>

            <a href="{{ route('admin.caja.cuenta', $comanda) }}"
               class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:opacity-90 text-sm font-semibold">
                Imprimir cuenta
            </a>

            @if(!$esCerrada)
                <button type="button"
                        class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-semibold"
                        onclick="openAdminAddItems()">
                    + Agregar items
                </button>
            @endif

            <a href="{{ route('admin.caja.show', $comanda) }}"
               class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 text-sm font-semibold">
                Abrir en caja
            </a>
        </div>
    </div>

    @if(session('ok'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
            {{ session('ok') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
            <div class="font-bold mb-1">Revisá estos errores:</div>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- ITEMS -->
        <section class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-4 md:px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="font-bold text-gray-900">Items</h2>
                <div class="text-sm text-gray-600">
                    Total items: <span class="font-bold">{{ $comanda->items->sum('cantidad') }}</span>
                </div>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($comanda->items as $it)
                    @php
                        $importe = (float)$it->precio_snapshot * (float)$it->cantidad;
                    @endphp
                    <div class="p-4 md:p-5 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900 leading-tight">
                                {{ $it->nombre_snapshot }}
                            </div>

                            @if(!empty($it->nota))
                                <div class="text-sm text-gray-600 mt-1 italic">“{{ $it->nota }}”</div>
                            @endif

                            <div class="text-xs text-gray-500 mt-2">
                                Estado item: <span class="font-bold">{{ $it->estado }}</span>
                            </div>

                            @if(!$esCerrada)
                                <div class="mt-3">
                                    <form method="POST"
                                          action="{{ route('admin.comandas.items.delete', $it) }}"
                                          onsubmit="return confirm('¿Eliminar este item?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-2 rounded-lg text-xs font-bold bg-red-50 text-red-700 border border-red-200 hover:bg-red-100">
                                            Eliminar item
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        <div class="shrink-0 text-right">
                            <div class="text-sm text-gray-700">
                                <span class="font-bold">{{ rtrim(rtrim(number_format((float)$it->cantidad, 2, '.', ''), '0'), '.') }}</span>
                                × $ {{ number_format((float)$it->precio_snapshot, 0, ',', '.') }}
                            </div>
                            <div class="text-lg font-extrabold text-gray-900 mt-1">
                                $ {{ number_format($importe, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-sm text-gray-600">No hay items todavía.</div>
                @endforelse
            </div>

            <div class="px-4 md:px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-600">Subtotal</div>
                <div class="text-xl font-extrabold text-gray-900">
                    $ {{ number_format((float)$subtotal, 0, ',', '.') }}
                </div>
            </div>
        </section>

        <!-- PANEL -->
        <aside class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-4 border-b border-gray-200">
                <h2 class="font-bold text-gray-900">Acciones</h2>
                <p class="text-xs text-gray-500 mt-1">Administración / Caja.</p>
            </div>

            <div class="p-5 space-y-3 text-sm text-gray-700">
                <div class="rounded-xl border border-gray-200 p-3 bg-gray-50">
                    <div class="text-xs text-gray-500">Total estimado (si cuenta solicitada)</div>
                    <div class="text-lg font-extrabold text-gray-900">
                        $ {{ number_format((float)($comanda->total_estimado ?? $subtotal), 0, ',', '.') }}
                    </div>
                </div>

                <a href="{{ route('admin.caja.show', $comanda) }}"
                   class="w-full inline-flex justify-center px-4 py-3 rounded-xl bg-emerald-600 text-white font-extrabold hover:bg-emerald-700">
                    Ir a caja (cobrar)
                </a>

                <a href="{{ route('admin.caja.cuenta', $comanda) }}"
                   class="w-full inline-flex justify-center px-4 py-3 rounded-xl bg-gray-900 text-white font-extrabold hover:opacity-90">
                    Imprimir cuenta
                </a>

                @if(!$esCerrada)
                    <button type="button"
                            class="w-full inline-flex justify-center px-4 py-3 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 font-extrabold hover:bg-emerald-100"
                            onclick="openAdminAddItems()">
                        + Agregar items
                    </button>
                @endif
            </div>
        </aside>

    </div>
</div>

{{-- =========================
    MODAL ADMIN: AGREGAR ITEMS (funcional)
========================== --}}
<div id="adminAddItemsModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl w-full max-w-3xl shadow-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-500">Administración</div>
                <div class="text-lg font-extrabold text-gray-900">Agregar items · Comanda #{{ $comanda->id }}</div>
                <div class="text-xs text-gray-500 mt-1">Podés agregar varias líneas antes de guardar.</div>
            </div>
            <button type="button" onclick="closeAdminAddItems()" class="p-2 rounded-lg hover:bg-gray-100">✕</button>
        </div>

        <form id="adminAddItemsForm" method="POST" action="{{ route('admin.comandas.items.add', $comanda) }}" class="p-5 space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-6">
                    <label class="text-sm font-semibold text-gray-800">Item</label>

                    <select id="adAddItemSelect" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm border-gray-300">
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

                        @php $sinCat = ($cartaItems ?? collect())->whereNull('id_categoria'); @endphp
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
                    <label class="text-sm font-semibold text-gray-800">Cant.</label>
                    <input id="adAddItemQty" type="number" min="0.01" step="0.01" value="1"
                           class="mt-1 w-full rounded-xl border px-3 py-2 text-sm border-gray-300">
                </div>

                <div class="sm:col-span-4">
                    <label class="text-sm font-semibold text-gray-800">Nota</label>
                    <input id="adAddItemNote" type="text" placeholder="Opcional"
                           class="mt-1 w-full rounded-xl border px-3 py-2 text-sm border-gray-300">
                </div>

                <div class="sm:col-span-12 flex justify-end">
                    <button id="adAddLineBtn" type="button"
                            class="px-4 py-2 rounded-xl text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700">
                        + Agregar línea
                    </button>
                </div>
            </div>

            <div class="rounded-2xl border p-3 bg-gray-50 border-gray-200">
                <div class="text-sm font-bold mb-2 text-gray-800">Detalle</div>

                <div id="adLines" class="space-y-2">
                    <div class="text-sm text-gray-500">No hay líneas todavía.</div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeAdminAddItems()"
                        class="px-4 py-2 rounded-xl border border-gray-300 text-sm font-semibold hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit"
                        class="px-4 py-2 rounded-xl bg-gray-900 text-white text-sm font-extrabold hover:opacity-90">
                    Guardar items
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function(){
    function qs(id){ return document.getElementById(id); }

    const sel = qs('adAddItemSelect');
    const qty = qs('adAddItemQty');
    const note = qs('adAddItemNote');
    const btn = qs('adAddLineBtn');
    const lines = qs('adLines');
    const form = qs('adminAddItemsForm');

    if (!sel || !qty || !note || !btn || !lines || !form) return;

    let idx = 0;

    function renderEmpty(){
        lines.innerHTML = `<div class="text-sm text-gray-500">No hay líneas todavía.</div>`;
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
        row.className = "rounded-xl border p-3 bg-white border-gray-200";

        row.innerHTML = `
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="font-semibold truncate text-gray-900">${escapeHtml(label)}</div>
                    ${notaVal ? `<div class="text-xs mt-1 text-gray-500">Nota: ${escapeHtml(notaVal)}</div>` : ``}
                </div>
                <div class="shrink-0 text-right">
                    <div class="text-xs text-gray-500">Cant.</div>
                    <div class="font-extrabold text-gray-900">${cantidad}</div>
                </div>
            </div>

            <input type="hidden" name="items[${idx}][id_item]" value="${idItem}">
            <input type="hidden" name="items[${idx}][cantidad]" value="${cantidad}">
            <input type="hidden" name="items[${idx}][nota]" value="${escapeAttr(notaVal)}">

            <div class="mt-3 flex justify-end">
                <button type="button"
                        class="px-3 py-2 rounded-xl text-xs font-bold bg-red-50 text-red-700 border border-red-200 hover:bg-red-100"
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
    }

    btn.addEventListener('click', addLine);

    lines.addEventListener('click', function(e){
        const rm = e.target.closest('[data-remove-line="1"]');
        if (!rm) return;
        const row = rm.closest('div.rounded-xl');
        if (row) row.remove();
        if (!lines.children.length) renderEmpty();
    });

    form.addEventListener('submit', function(e){
        const hasAny = form.querySelector('input[type="hidden"][name^="items["]');
        if (!hasAny){
            e.preventDefault();
            alert('Agregá al menos una línea.');
        }
    });

    window.openAdminAddItems = function(){
        const m = document.getElementById('adminAddItemsModal');
        if(!m) return;

        // reset cada vez que abre
        idx = 0;
        renderEmpty();
        form.querySelectorAll('input[type="hidden"][name^="items["]').forEach(n => n.remove());

        sel.value = '';
        qty.value = '1';
        note.value = '';

        m.classList.remove('hidden');
        m.classList.add('flex');
    }

    window.closeAdminAddItems = function(){
        const m = document.getElementById('adminAddItemsModal');
        if(!m) return;
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
})();
</script>
@endsection