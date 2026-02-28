{{-- resources/views/mozo/modals/add-item.blade.php --}}
<div id="modalAddItems"
     class="hidden fixed inset-0 z-50 rf-modal-backdrop"
     style="background: rgba(0,0,0,0.45);">

    <div class="min-h-full w-full flex items-end md:items-center justify-center p-0 md:p-4 overflow-y-auto">

        <div class="bg-white w-full md:w-[96%] md:max-w-3xl rounded-t-3xl md:rounded-3xl shadow-xl border animate-fade-in
                    flex flex-col max-h-[92vh] md:max-h-[85vh]"
             style="border-color: var(--rf-border);">

            <div class="p-4 border-b flex items-center justify-between gap-3 shrink-0"
                 style="border-color: var(--rf-border);">
                <div>
                    <h3 class="font-bold text-lg">Agregar items</h3>
                    <p class="text-sm mt-1" style="color: var(--rf-text-light);">
                        Elegí una categoría y luego el item para agregarlo directamente
                    </p>
                </div>

                <button type="button"
                        class="p-2 rounded-xl border rf-hover-lift"
                        style="border-color: var(--rf-border);"
                        data-action="close-modal" data-modal="modalAddItems">✕</button>
            </div>

            <div class="p-4 overflow-y-auto rf-scrollbar grow" data-modal-body="1">

                {{-- ✅ aviso bloqueo --}}
                <div id="rfAddItemsLockedBanner"
                     class="hidden rounded-2xl border p-3 mb-4 text-sm"
                     style="border-color: rgba(245,158,11,0.35); background: rgba(245,158,11,0.10); color: var(--rf-warning);">
                    Cuenta solicitada: el mozo no puede agregar items. Solo administración (caja) puede hacerlo.
                </div>

                <form id="modalAddItemsForm" method="POST" action="#" class="space-y-4">
                    @csrf

                    {{-- Categorías scrolleables --}}
                    <div class="space-y-2">
                        <label class="text-sm font-semibold" style="color: var(--rf-text);">Categorías</label>
                        <div class="flex gap-2 overflow-x-auto pb-2 rf-scrollbar" style="scrollbar-width: thin; -webkit-overflow-scrolling: touch;">
                            @foreach(($cartaCategorias ?? collect()) as $cat)
                                <button type="button"
                                        class="category-btn px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-all rf-hover-lift
                                               @if($loop->first) active-category @endif"
                                        data-category-id="{{ $cat->id }}"
                                        style="border: 1px solid var(--rf-border); background: var(--rf-bg); color: var(--rf-text);">
                                    {{ $cat->nombre }}
                                </button>
                            @endforeach
                            
                            @php
                                $sinCat = ($cartaItems ?? collect())->whereNull('id_categoria');
                            @endphp
                            @if($sinCat->count())
                                <button type="button"
                                        class="category-btn px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-all rf-hover-lift"
                                        data-category-id="sin-categoria"
                                        style="border: 1px solid var(--rf-border); background: var(--rf-bg); color: var(--rf-text);">
                                    Sin categoría
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Items de la categoría seleccionada --}}
                    <div class="space-y-2">
                        <label class="text-sm font-semibold" style="color: var(--rf-text);">Items</label>
                        <div id="itemsGrid" class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto p-1 rf-scrollbar">
                            {{-- Los items se cargarán dinámicamente --}}
                        </div>
                    </div>

                    {{-- Nota global para el próximo item (opcional) --}}
                    <div class="space-y-2">
                        <label class="text-sm font-semibold" style="color: var(--rf-text);">Nota (opcional)</label>
                        <input id="rfAddItemNote" type="text" placeholder="Ej: sin sal, punto de más..." 
                               class="w-full rounded-xl border px-3 py-2 text-sm"
                               style="border-color: var(--rf-border);">
                    </div>

                    {{-- Líneas agregadas --}}
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

            <div class="p-4 border-t shrink-0 flex items-center justify-end gap-2"
                 style="border-color: var(--rf-border);">
                <button type="button"
                        class="px-4 py-2 rounded-xl text-sm font-semibold border"
                        style="border-color: var(--rf-border);"
                        data-action="close-modal" data-modal="modalAddItems">
                    Cancelar
                </button>

                <button id="rfSaveAddItemsBtn"
                        type="submit"
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
    function qsa(selector){ return document.querySelectorAll(selector); }

    // Elementos principales
    const note = qs('rfAddItemNote');
    const lines = qs('rfLines');
    const form = qs('modalAddItemsForm');
    const bannerLocked = qs('rfAddItemsLockedBanner');
    const btnSave = qs('rfSaveAddItemsBtn');
    const itemsGrid = qs('itemsGrid');

    if (!lines || !form || !bannerLocked || !btnSave || !itemsGrid) return;

    let idx = 0;
    let locked = false;
    let activeCategoryId = null;
    
    // Datos de items y categorías (pasados desde PHP)
    const categories = @json($cartaCategorias ?? []);
    const allItems = @json($cartaItems ?? []);
    
    // Función para escapar HTML
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

    // Función para formatear precio
    function formatPrice(price) {
        return new Intl.NumberFormat('es-CL', { maximumFractionDigits: 0 }).format(price);
    }

    // Cargar items de una categoría
    function loadItemsForCategory(categoryId) {
        let itemsToShow = [];
        
        if (categoryId === 'sin-categoria') {
            itemsToShow = allItems.filter(item => !item.id_categoria);
        } else {
            itemsToShow = allItems.filter(item => item.id_categoria == categoryId);
        }

        if (itemsToShow.length === 0) {
            itemsGrid.innerHTML = `
                <div class="col-span-2 text-center py-8 text-sm" style="color: var(--rf-text-light);">
                    No hay items en esta categoría
                </div>
            `;
            return;
        }

        itemsGrid.innerHTML = itemsToShow.map(item => `
            <button type="button"
                    class="item-btn p-3 rounded-xl border text-left transition-all rf-hover-lift
                           ${locked ? 'opacity-50 cursor-not-allowed' : ''}"
                    style="border-color: var(--rf-border); background: var(--rf-bg);"
                    data-item-id="${item.id}"
                    data-item-nombre="${escapeHtml(item.nombre)}"
                    data-item-precio="${item.precio}"
                    ${locked ? 'disabled' : ''}>
                <div class="font-medium" style="color: var(--rf-text);">${escapeHtml(item.nombre)}</div>
                <div class="text-sm mt-1" style="color: var(--rf-primary); font-weight: 600;">
                    $${formatPrice(item.precio)}
                </div>
            </button>
        `).join('');
    }

    // Agregar item directamente
    function addItemDirectly(itemId, itemNombre, itemPrecio) {
        if (locked) return;

        const notaVal = (note.value || '').trim();

        // Limpiar el placeholder de "no hay líneas"
        if (lines.children.length === 1 && lines.textContent.includes('No hay líneas')) {
            lines.innerHTML = '';
        }

        // Crear la línea del item
        const row = document.createElement('div');
        row.className = "rounded-xl border p-3 bg-white";
        row.style.borderColor = "var(--rf-border)";

        row.innerHTML = `
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <div class="font-semibold truncate" style="color: var(--rf-text);">${escapeHtml(itemNombre)}</div>
                    <div class="text-sm mt-1" style="color: var(--rf-primary);">$${formatPrice(itemPrecio)}</div>
                    ${notaVal ? `<div class="text-xs mt-2" style="color: var(--rf-text-light);">📝 ${escapeHtml(notaVal)}</div>` : ``}
                </div>
                <div class="shrink-0 text-right">
                    <div class="text-xs" style="color: var(--rf-text-light);">Cant.</div>
                    <div class="font-bold" style="color: var(--rf-text);">1</div>
                </div>
            </div>

            <input type="hidden" name="items[${idx}][id_item]" value="${itemId}">
            <input type="hidden" name="items[${idx}][cantidad]" value="1">
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

        // Limpiar la nota después de agregar (opcional, comentar si no se desea)
        // note.value = '';

        // Scroll al final
        const body = document.querySelector('#modalAddItems [data-modal-body="1"]');
        if (body) body.scrollTo({ top: body.scrollHeight, behavior: 'smooth' });
    }

    // Setear estado de bloqueo
    function setLocked(v){
        locked = !!v;
        bannerLocked.classList.toggle('hidden', !locked);
        
        // Deshabilitar botones de items
        qsa('.item-btn').forEach(btn => {
            btn.disabled = locked;
            btn.classList.toggle('opacity-50', locked);
            btn.classList.toggle('cursor-not-allowed', locked);
        });
        
        // Deshabilitar botones de categorías
        qsa('.category-btn').forEach(btn => {
            btn.disabled = locked;
            btn.classList.toggle('opacity-50', locked);
            btn.classList.toggle('cursor-not-allowed', locked);
        });

        btnSave.disabled = locked;
        note.disabled = locked;

        if (locked){
            btnSave.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            btnSave.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    // Renderizar vacío
    function renderEmpty(){
        lines.innerHTML = `<div class="text-sm" style="color: var(--rf-text-light);">No hay líneas todavía.</div>`;
    }

    // Event listeners
    document.addEventListener('click', function(e) {
        // Click en categoría
        const categoryBtn = e.target.closest('.category-btn');
        if (categoryBtn && !locked) {
            const categoryId = categoryBtn.dataset.categoryId;
            
            // Actualizar estilo de categorías activas
            qsa('.category-btn').forEach(btn => {
                btn.classList.remove('active-category');
                btn.style.background = 'var(--rf-bg)';
                btn.style.color = 'var(--rf-text)';
            });
            
            categoryBtn.style.background = 'var(--rf-primary)';
            categoryBtn.style.color = 'white';
            categoryBtn.style.borderColor = 'var(--rf-primary)';
            
            activeCategoryId = categoryId;
            loadItemsForCategory(categoryId);
        }

        // Click en item (agregar directamente)
        const itemBtn = e.target.closest('.item-btn');
        if (itemBtn && !locked && !itemBtn.disabled) {
            const itemId = itemBtn.dataset.itemId;
            const itemNombre = itemBtn.dataset.itemNombre;
            const itemPrecio = itemBtn.dataset.itemPrecio;
            addItemDirectly(itemId, itemNombre, itemPrecio);
        }

        // Click en quitar línea
        const removeBtn = e.target.closest('[data-remove-line="1"]');
        if (removeBtn && !locked) {
            const row = removeBtn.closest('div.rounded-xl');
            if (row) row.remove();
            if (!lines.children.length) renderEmpty();
        }
    });

    // Cuando se abre el modal
    document.addEventListener('click', function(e){
        const open = e.target.closest('[data-action="add-items"]');
        if (!open) return;

        // Resetear estado
        const isLocked = (open.getAttribute('data-locked') === '1');
        setLocked(isLocked);

        idx = 0;
        renderEmpty();
        note.value = '';
        
        // Limpiar inputs ocultos viejos
        form.querySelectorAll('input[type="hidden"][name^="items["]').forEach(n => n.remove());

        // Activar primera categoría por defecto
        const firstCategory = qsa('.category-btn')[0];
        if (firstCategory) {
            firstCategory.click();
        } else {
            itemsGrid.innerHTML = `
                <div class="col-span-2 text-center py-8 text-sm" style="color: var(--rf-text-light);">
                    No hay categorías disponibles
                </div>
            `;
        }

        // Scroll al inicio
        const body = document.querySelector('#modalAddItems [data-modal-body="1"]');
        if (body) body.scrollTop = 0;
    });

    // Submit del formulario
    form.addEventListener('submit', function(e){
        if (locked){
            e.preventDefault();
            alert('Cuenta solicitada: solo administración puede agregar items.');
            return;
        }

        const hasAny = form.querySelector('input[type="hidden"][name^="items["]');
        if (!hasAny){
            e.preventDefault();
            alert('Agregá al menos un item.');
        }
    });

})();
</script>

<style>
/* Estilo para categoría activa */
.active-category {
    background: var(--rf-primary) !important;
    color: white !important;
    border-color: var(--rf-primary) !important;
}

/* Hover effects para items */
.item-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border-color: var(--rf-primary) !important;
}

/* Scroll personalizado */
.rf-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.rf-scrollbar::-webkit-scrollbar-track {
    background: var(--rf-bg);
    border-radius: 10px;
}

.rf-scrollbar::-webkit-scrollbar-thumb {
    background: var(--rf-border);
    border-radius: 10px;
}

.rf-scrollbar::-webkit-scrollbar-thumb:hover {
    background: var(--rf-text-light);
}
</style>
@endpush