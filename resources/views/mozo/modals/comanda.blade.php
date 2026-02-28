{{-- resources/views/mozo/modals/comanda.blade.php --}}
<div id="modalOcupar" class="hidden fixed inset-0 z-50 items-center justify-center rf-modal-backdrop"
     style="background: rgba(0,0,0,0.45);">
    <div class="bg-white w-[92%] max-w-lg rounded-2xl shadow-xl border p-4 animate-fade-in"
         style="border-color: var(--rf-border);">
        <div class="flex items-center justify-between">
            <h3 id="modalOcuparTitle" class="font-bold text-lg">Ocupar mesa</h3>
            <button class="p-2 rounded-xl border"
                style="border-color: var(--rf-border);"
                data-action="close-modal" data-modal="modalOcupar">✕</button>
        </div>

        <form id="modalOcuparForm" method="POST" action="#" class="mt-4 space-y-3">
            @csrf

            <div>
                <label class="text-sm font-semibold" style="color: var(--rf-text);">Observación (opcional)</label>
                <input id="modalOcuparObs" type="text" name="observacion"
                       class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"
                       style="border-color: var(--rf-border);"
                       placeholder="Ej: Juan - 2 personas">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button"
                    class="px-4 py-2 rounded-xl text-sm font-semibold border"
                    style="border-color: var(--rf-border);"
                    data-action="close-modal" data-modal="modalOcupar">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-4 py-2 rounded-xl text-sm font-semibold"
                    style="background: var(--rf-primary); color: white;">
                    Ocupar
                </button>
            </div>
        </form>
    </div>
</div>