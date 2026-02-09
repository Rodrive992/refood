{{-- resources/views/mozo/partials/modals/mesa.blade.php --}}
<div id="mesaModal" class="rf-modal-backdrop fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl animate-fade-in overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-500">Mesa</div>
                <div id="mesaModalTitle" class="text-xl font-bold text-gray-900">Acción</div>
            </div>

            <button type="button" data-action="close-modal" data-modal="mesaModal"
                class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="p-6">
            <form id="mesaModalForm" method="POST" action="">
                @csrf

                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Observación (opcional)
                </label>
                <input id="mesaObs" name="observacion" type="text" maxlength="255"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 outline-none transition"
                    placeholder="Ej: Juan - 2 personas">

                <div class="flex gap-3 mt-6">
                    <button type="button" data-action="close-modal" data-modal="mesaModal"
                        class="flex-1 px-4 py-3 text-gray-700 font-semibold border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>

                    <button type="submit"
                        class="flex-1 px-4 py-3 text-white font-extrabold rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 hover:shadow-lg transition-all">
                        Confirmar
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>