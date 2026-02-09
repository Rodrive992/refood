{{-- Modal Solicitar Cuenta --}}
<div id="cuentaModal" class="rf-modal-backdrop fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl animate-fade-in">
        <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-green-50 to-white">
            <div>
                <div class="text-xs text-gray-500">Solicitar cuenta</div>
                <div class="text-xl font-bold text-gray-900">
                    {{ $mesaSelected->nombre ?? '—' }} · #{{ $comanda->id ?? '—' }}
                </div>
            </div>
            <button type="button" onclick="closeCuentaModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        @if($comanda)
            <form id="cuentaForm" method="POST" action="{{ route('mozo.comandas.solicitarCuenta', $comanda) }}" class="p-6">
                @csrf

                <div class="mb-6 p-4 bg-gray-50 rounded-xl">
                    <div class="text-center">
                        <div class="text-xs text-gray-500 mb-1">Total estimado</div>
                        <div class="text-3xl font-bold text-gray-900" id="cuentaSubtotal">
                            $ {{ number_format((float) $subtotal, 2, ',', '.') }}
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nota para caja (opcional)</label>
                    <textarea name="nota" rows="3"
                              class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none transition resize-none"
                              placeholder="Ej: pagan separados / factura A / descuento acordado"></textarea>
                    <div class="text-xs text-gray-500 mt-2">Tip: aclaraciones de pago o indicaciones especiales.</div>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeCuentaModal()"
                            class="flex-1 px-4 py-3 text-gray-700 font-semibold border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-3 text-white font-semibold rounded-xl rf-gradient-primary hover:shadow-lg transition-all">
                        Confirmar solicitud
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
