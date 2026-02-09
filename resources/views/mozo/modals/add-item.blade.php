{{-- resources/views/mozo/modals/add-item.blade.php --}}
<div id="modalAddItems"
     class="hidden fixed inset-0 z-50 rf-modal-backdrop"
     style="background: rgba(0,0,0,0.45);">

    {{-- Wrapper: permite scroll del backdrop en pantallas chicas --}}
    <div class="min-h-full w-full flex items-end md:items-center justify-center p-0 md:p-4 overflow-y-auto">

        {{-- Card / Sheet --}}
        <div class="bg-white w-full md:w-[96%] md:max-w-3xl rounded-t-3xl md:rounded-3xl shadow-xl border animate-fade-in
                    flex flex-col max-h-[92vh] md:max-h-[85vh]"
             style="border-color: var(--rf-border);">

            {{-- Header fijo --}}
            <div class="p-4 border-b flex items-center justify-between gap-3 shrink-0"
                 style="border-color: var(--rf-border);">
                <div>
                    <h3 class="font-bold text-lg">Agregar items</h3>
                    <p class="text-sm mt-1" style="color: var(--rf-text-light);">
                        Elegí un item y cantidad. Podés agregar varias líneas.
                    </p>
                </div>

                <button type="button"
                        class="p-2 rounded-xl border rf-hover-lift"
                        style="border-color: var(--rf-border);"
                        data-action="close-modal" data-modal="modalAddItems">✕</button>
            </div>

            {{-- Body scrolleable --}}
            <div class="p-4 overflow-y-auto rf-scrollbar grow" data-modal-body="1">
                <form id="modalAddItemsForm" method="POST" action="#" class="space-y-4">
                    @csrf

                    {{-- selector --}}
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <div class="sm:col-span-6">
                            <label class="text-sm font-semibold" style="color: var(--rf-text);">Item</label>

                            <select id="rfAddItemSelect"
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
                            <input id="rfAddItemQty" type="number" min="0.01" step="0.01" value="1"
                                   class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"
                                   style="border-color: var(--rf-border);">
                        </div>

                        <div class="sm:col-span-4">
                            <label class="text-sm font-semibold" style="color: var(--rf-text);">Nota</label>
                            <input id="rfAddItemNote" type="text" placeholder="Opcional"
                                   class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"
                                   style="border-color: var(--rf-border);">
                        </div>

                        <div class="sm:col-span-12 flex justify-end">
                            <button id="rfAddLineBtn" type="button"
                                    class="px-4 py-2 rounded-xl text-sm font-semibold rf-hover-lift"
                                    style="background: var(--rf-secondary); color: white;">
                                + Agregar línea
                            </button>
                        </div>
                    </div>

                    {{-- líneas --}}
                    <div class="rounded-2xl border p-3"
                         style="border-color: var(--rf-border); background: var(--rf-bg);">
                        <div class="text-sm font-bold mb-2" style="color: var(--rf-text);">Detalle</div>

                        <div id="rfLines" class="space-y-2">
                            <div class="text-sm" style="color: var(--rf-text-light);">
                                No hay líneas todavía.
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Footer fijo (botones siempre visibles) --}}
            <div class="p-4 border-t shrink-0 flex items-center justify-end gap-2"
                 style="border-color: var(--rf-border);">
                <button type="button"
                        class="px-4 py-2 rounded-xl text-sm font-semibold border"
                        style="border-color: var(--rf-border);"
                        data-action="close-modal" data-modal="modalAddItems">
                    Cancelar
                </button>

                <button type="submit"
                        form="modalAddItemsForm"
                        class="px-4 py-2 rounded-xl text-sm font-semibold"
                        style="background: var(--rf-primary); color: white;">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function(){
    function qs(id){ return document.getElementById(id); }

    const sel = qs('rfAddItemSelect');
    const qty = qs('rfAddItemQty');
    const note = qs('rfAddItemNote');
    const btn = qs('rfAddLineBtn');
    const lines = qs('rfLines');
    const form = qs('modalAddItemsForm');

    if (!sel || !qty || !note || !btn || !lines || !form) return;

    let idx = 0;

    function renderEmpty(){
        lines.innerHTML = `<div class="text-sm" style="color: var(--rf-text-light);">No hay líneas todavía.</div>`;
    }

    function modalBodyEl(){
        return document.querySelector('#modalAddItems [data-modal-body="1"]');
    }

    function addLine(){
        const idItem = parseInt(sel.value || '0', 10);
        if (!idItem) return alert('Seleccioná un item.');

        const opt = sel.options[sel.selectedIndex];
        const label = opt ? opt.textContent.trim() : ('Item #' + idItem);

        const cantidad = parseFloat(qty.value || '1');
        if (!cantidad || cantidad < 0.01) return alert('Cantidad inválida.');

        const notaVal = (note.value || '').trim();

        // si estaba vacío, limpiamos
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

        // reset inputs
        sel.value = '';
        qty.value = '1';
        note.value = '';

        // ✅ scrolleo al final del body del modal (para ver la línea nueva)
        const body = modalBodyEl();
        if (body) body.scrollTo({ top: body.scrollHeight, behavior: 'smooth' });
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

    btn.addEventListener('click', addLine);

    lines.addEventListener('click', function(e){
        const rm = e.target.closest('[data-remove-line="1"]');
        if (!rm) return;
        const row = rm.closest('div.rounded-xl');
        if (row) row.remove();
        if (!lines.children.length) renderEmpty();
    });

    // cuando se abre el modal, lo dejamos limpio
    document.addEventListener('click', function(e){
        const open = e.target.closest('[data-action="add-items"]');
        if (!open) return;

        idx = 0;
        renderEmpty();
        form.querySelectorAll('input[type="hidden"][name^="items["]').forEach(n => n.remove());

        // reset inputs visibles
        sel.value = '';
        qty.value = '1';
        note.value = '';

        // scroll arriba
        const body = modalBodyEl();
        if (body) body.scrollTop = 0;
    });

    // seguridad: si envían sin líneas
    form.addEventListener('submit', function(e){
        const hasAny = form.querySelector('input[type="hidden"][name^="items["]');
        if (!hasAny){
            e.preventDefault();
            alert('Agregá al menos una línea.');
        }
    });

})();
</script>
@endpush
