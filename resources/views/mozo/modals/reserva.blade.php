{{-- Modal Ver Reserva --}}
<div id="reservaModal" class="rf-modal-backdrop fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl animate-fade-in">
        <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-blue-50 to-white">
            <div>
                <div class="text-xs text-gray-500">Detalles de reserva</div>
                <div id="reservaMesaTitle" class="text-xl font-bold text-gray-900">Mesa</div>
            </div>
            <button type="button" onclick="closeReservaModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="p-6">
            <div class="mb-4">
                <div class="text-sm font-semibold text-gray-700 mb-2">Observación de la reserva</div>
                <div id="reservaMesaObs" class="p-4 bg-gray-50 rounded-xl text-gray-800 whitespace-pre-line">—</div>
            </div>

            <div class="text-xs text-gray-500">
                💡 La reserva se carga desde "Reservar" con observación (ej: nombre + hora).
            </div>
        </div>

        <div class="p-6 border-t border-gray-200">
            <button type="button" onclick="closeReservaModal()"
                    class="w-full px-4 py-3 text-gray-700 font-semibold border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                Cerrar
            </button>
        </div>
    </div>
</div>
