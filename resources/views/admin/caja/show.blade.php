{{-- resources/views/admin/caja/show.blade.php --}}

@extends('layouts.app')

@section('content')
@php
    $mesa = $comanda->mesa;
    $mozo = $comanda->mozo;

    // ✅ Turno/caja
    $hayCaja = !empty($cajaAbierta);
@endphp

<div class="max-w-7xl mx-auto px-4 md:px-6 py-6">

    {{-- Flash / errores compactos --}}
    @if (session('ok'))
        <div class="mb-4 rounded-xl px-4 py-2.5 text-sm flex items-center gap-2" style="background: #ECFDF5; border: 1px solid #D1FAE5; color: #065F46;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('ok') }}
        </div>
    @endif

    @if (session('error') || $errors->any())
        <div class="mb-4 rounded-xl px-4 py-2.5 text-sm" style="background: #FEF2F2; border: 1px solid #FEE2E2; color: #991B1B;">
            @if (session('error'))
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="flex items-start gap-2 mt-1">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    {{-- Banner turno compacto --}}
    <div class="mb-5 rounded-xl p-4" style="background: white; border: 1px solid #E2E8F0;">
        @if($hayCaja)
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-3">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-xs font-medium uppercase tracking-wider" style="color: #64748B;">Turno #{{ $cajaAbierta->turno }}</span>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <span style="color: #475569;">Apertura: <strong style="color: #0F172A;">${{ number_format((float)$cajaAbierta->efectivo_apertura, 0, ',', '.') }}</strong></span>
                    <span style="color: #475569;">Ingresos: <strong class="text-emerald-600">${{ number_format((float)$cajaAbierta->ingreso_efectivo, 0, ',', '.') }}</strong></span>
                    <span style="color: #475569;">Salidas: <strong class="text-red-600">${{ number_format((float)$cajaAbierta->salida_efectivo, 0, ',', '.') }}</strong></span>
                    <span style="color: #475569;">Efectivo: <strong style="color: #0F172A;">${{ number_format((float)$cajaAbierta->efectivo_turno, 0, ',', '.') }}</strong></span>
                </div>
                <div class="ml-auto">
                    <span class="px-3 py-1.5 rounded-full text-xs font-medium" style="background: #ECFDF5; color: #065F46;">
                        ✅ OK para cobrar
                    </span>
                </div>
            </div>
        @else
            <div class="flex flex-wrap items-center gap-4">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                <span class="text-sm" style="color: #64748B;">No hay turno activo</span>
                <a href="{{ route('admin.caja.index') }}" class="text-sm font-medium ml-auto" style="color: #DC2626;">
                    Abrir turno →
                </a>
            </div>
        @endif
    </div>

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl md:text-2xl font-extrabold" style="color: #0F172A;">
                    Cobrar comanda #{{ $comanda->id }}
                </h1>
                <span class="px-3 py-1 rounded-full text-xs font-medium" style="background: #FEF3C7; color: #92400E;">
                    {{ optional($comanda->cuenta_solicitada_at)->format('d/m H:i') }}
                </span>
            </div>
            <p class="text-sm mt-1" style="color: #64748B;">
                Mesa: <span class="font-medium" style="color: #0F172A;">{{ $mesa->nombre ?? 'Sin mesa' }}</span>
                · Mozo: <span class="font-medium" style="color: #0F172A;">{{ $mozo->name ?? '—' }}</span>
            </p>
            @if (!empty($comanda->cuenta_solicitada_nota))
                <p class="text-xs mt-2 italic" style="color: #64748B;">📝 {{ $comanda->cuenta_solicitada_nota }}</p>
            @endif
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.caja.index') }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-1"
               style="background: white; border: 1px solid #E2E8F0; color: #475569;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </a>

            <a href="{{ route('admin.caja.cuenta.print', $comanda) }}" 
               target="_blank"
               class="js-print-preticket px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-1"
               style="background: #F8FAFC; border: 1px solid #E2E8F0; color: #475569;"
               data-print-url="{{ route('admin.caja.cuenta.print', $comanda) }}"
               data-comanda-id="{{ $comanda->id }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Pre-ticket
            </a>

            <button type="button"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-1 {{ !$hayCaja ? 'opacity-50 cursor-not-allowed' : '' }}"
                    style="background: #0F172A; color: white;"
                    {{ !$hayCaja ? 'disabled' : '' }}
                    data-action="open-modal"
                    data-modal="modalAddItemsAdmin">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Agregar
            </button>
        </div>
    </div>

    {{-- Grid principal --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        {{-- DETALLE --}}
        <section class="lg:col-span-7 rounded-xl overflow-hidden" style="background: white; border: 1px solid #E2E8F0;">
            <div class="px-4 py-3 flex items-center justify-between" style="border-bottom: 1px solid #F1F5F9;">
                <h2 class="font-bold" style="color: #0F172A;">Detalle de la comanda</h2>
                <span class="text-xs font-medium px-2 py-1 rounded-full" style="background: #F1F5F9; color: #475569;">
                    {{ $comanda->items->where('estado','!=','anulado')->count() }} items
                </span>
            </div>

            <div class="p-4">
                {{-- Items --}}
                <div class="space-y-2">
                    @forelse($comanda->items->where('estado','!=','anulado') as $it)
                        <div class="flex items-start justify-between gap-3 p-2 rounded-lg hover:bg-slate-50 transition-colors">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-sm" style="color: #0F172A;">{{ $it->nombre_snapshot }}</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full" 
                                          style="background: #F1F5F9; color: #475569;">
                                        {{ $it->estado }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 mt-1 text-xs" style="color: #64748B;">
                                    <span>${{ number_format((float) $it->precio_snapshot, 0, ',', '.') }} c/u</span>
                                    <span>× {{ rtrim(rtrim(number_format((float) $it->cantidad, 2, '.', ''), '0'), '.') }}</span>
                                    @if ($it->nota)
                                        <span class="italic">📝 {{ $it->nota }}</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-sm" style="color: #0F172A;">
                                    ${{ number_format((float) $it->precio_snapshot * (float) $it->cantidad, 0, ',', '.') }}
                                </span>
                                
                                <form method="POST" action="{{ route('admin.caja.items.delete', $it) }}"
                                      onsubmit="return confirm('¿Eliminar este item?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            {{ !$hayCaja ? 'disabled' : '' }}
                                            class="p-1 rounded-lg text-xs {{ !$hayCaja ? 'opacity-30 cursor-not-allowed' : 'hover:bg-red-50' }}"
                                            style="color: #DC2626;">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full mb-3" style="background: #F1F5F9;">
                                <svg class="w-6 h-6" style="color: #94A3B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <p class="text-sm" style="color: #64748B;">No hay items en esta comanda</p>
                        </div>
                    @endforelse
                </div>

                {{-- Subtotal --}}
                <div class="mt-4 pt-3 flex items-center justify-between border-t" style="border-color: #F1F5F9;">
                    <span class="text-sm" style="color: #64748B;">Subtotal</span>
                    <span class="text-lg font-bold" style="color: #0F172A;" id="subtotalText">
                        ${{ number_format((float) $subtotal, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </section>

        {{-- COBRO --}}
        <section class="lg:col-span-5 rounded-xl overflow-hidden" style="background: white; border: 1px solid #E2E8F0;">
            <div class="px-4 py-3" style="border-bottom: 1px solid #F1F5F9;">
                <h2 class="font-bold" style="color: #0F172A;">Cobro</h2>
                <p class="text-xs mt-1" style="color: #64748B;">Pagos dinámicos (efectivo / débito / transferencia)</p>
            </div>

            @if(!$hayCaja)
                <div class="p-4">
                    <div class="rounded-lg p-3 text-sm" style="background: #FEF2F2; border: 1px solid #FEE2E2; color: #991B1B;">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            No podés cobrar sin turno abierto
                        </div>
                    </div>
                </div>
            @endif

            <form id="cobroForm"
                  class="p-4 space-y-4 {{ !$hayCaja ? 'opacity-60 pointer-events-none select-none' : '' }}"
                  method="POST"
                  action="{{ route('admin.caja.cobrar', $comanda) }}">
                @csrf

                {{-- Ajustes en grid compacto --}}
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color: #475569;">Descuento</label>
                        <div class="relative">
                            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-xs" style="color: #94A3B8;">$</span>
                            <input type="number" step="0.01" min="0" name="descuento"
                                   class="w-full pl-6 pr-2 py-1.5 rounded-lg text-xs"
                                   style="border: 1px solid #E2E8F0; background: white;"
                                   placeholder="0"
                                   value="{{ old('descuento', '0') }}"
                                   oninput="recalcTotales()">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1" style="color: #475569;">Recargo</label>
                        <div class="relative">
                            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-xs" style="color: #94A3B8;">$</span>
                            <input type="number" step="0.01" min="0" name="recargo"
                                   class="w-full pl-6 pr-2 py-1.5 rounded-lg text-xs"
                                   style="border: 1px solid #E2E8F0; background: white;"
                                   placeholder="0"
                                   value="{{ old('recargo', '0') }}"
                                   oninput="recalcTotales()">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1" style="color: #475569;">Propina</label>
                        <div class="relative">
                            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-xs" style="color: #94A3B8;">$</span>
                            <input type="number" step="0.01" min="0" name="propina"
                                   class="w-full pl-6 pr-2 py-1.5 rounded-lg text-xs"
                                   style="border: 1px solid #E2E8F0; background: white;"
                                   placeholder="0"
                                   value="{{ old('propina', '0') }}"
                                   oninput="recalcTotales()">
                        </div>
                    </div>
                </div>

                {{-- Totales compactos --}}
                <div class="rounded-lg p-3" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-sm">
                            <span style="color: #475569;">Total a cobrar</span>
                            <span class="font-bold" style="color: #0F172A;" id="totalCobrarText"></span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span style="color: #64748B;">Propina</span>
                            <span style="color: #D97706;" id="propinaText"></span>
                        </div>
                        <div class="flex items-center justify-between text-xs pt-1 border-t" style="border-color: #E2E8F0;">
                            <span style="color: #64748B;">Pagado</span>
                            <span style="color: #0F172A;" id="pagadoText"></span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span style="color: #64748B;">Vuelto</span>
                            <span style="color: #0F172A;" id="vueltoText"></span>
                        </div>
                    </div>
                </div>

                {{-- Pagos dinámicos --}}
                <div class="rounded-lg p-3" style="background: white; border: 1px solid #E2E8F0;">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium" style="color: #475569;">Pagos</span>
                        <button id="btnAddPago" type="button"
                                class="px-2 py-1 rounded-lg text-xs font-medium flex items-center gap-1"
                                style="background: #0F172A; color: white;">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Agregar
                        </button>
                    </div>
                    <div id="pagosWrap" class="space-y-2"></div>
                </div>

                {{-- Nota --}}
                <input name="nota" class="w-full px-3 py-2 rounded-lg text-xs" 
                       style="border: 1px solid #E2E8F0; background: white;"
                       placeholder="Nota (opcional) · Ej: descuento acordado"
                       value="{{ old('nota') }}">

                <button type="submit"
                        class="w-full py-2.5 rounded-lg text-sm font-bold text-white transition-all hover:shadow-lg"
                        style="background: #059669;">
                    Cobrar e imprimir ticket
                </button>

                <p class="text-[10px] text-center" style="color: #94A3B8;">
                    Al confirmar: registra venta, guarda pagos, cierra comanda y libera mesa
                </p>
            </form>
        </section>
    </div>
</div>

{{-- MODAL: Agregar items --}}
<div id="modalAddItemsAdmin"
     class="hidden fixed inset-0 z-50"
     style="background: rgba(0,0,0,0.45); backdrop-filter: blur(2px);">

    <div class="min-h-full w-full flex items-end md:items-center justify-center p-0 md:p-4 overflow-y-auto">
        <div class="bg-white w-full md:w-[96%] md:max-w-3xl rounded-t-2xl md:rounded-2xl shadow-xl flex flex-col max-h-[92vh] md:max-h-[85vh]"
             style="border: 1px solid #E2E8F0;">

            <div class="px-4 py-3 border-b flex items-center justify-between" style="border-color: #F1F5F9;">
                <div>
                    <h3 class="font-bold" style="color: #0F172A;">Agregar items</h3>
                    <p class="text-xs mt-0.5" style="color: #64748B;">Elegí un item y cantidad. Podés agregar varias líneas.</p>
                </div>
                <button type="button"
                        class="p-1.5 rounded-lg hover:bg-slate-100 transition-colors"
                        data-action="close-modal" data-modal="modalAddItemsAdmin">
                    <svg class="w-5 h-5" style="color: #64748B;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-4 overflow-y-auto rf-scrollbar grow" data-modal-body="1">
                <form id="modalAddItemsAdminForm"
                      method="POST"
                      action="{{ route('admin.caja.items.add', $comanda) }}"
                      class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <div class="sm:col-span-6">
                            <label class="block text-xs font-medium mb-1" style="color: #475569;">Item</label>
                            <select id="rfAdminAddItemSelect"
                                    class="w-full px-3 py-2 rounded-lg text-sm"
                                    style="border: 1px solid #E2E8F0; background: white;">
                                <option value="">— Seleccionar —</option>
                                @foreach(($cartaCategorias ?? collect()) as $cat)
                                    <optgroup label="{{ $cat->nombre }}">
                                        @foreach(($cartaItems ?? collect())->where('id_categoria', $cat->id) as $it)
                                            <option value="{{ $it->id }}" data-precio="{{ $it->precio }}">
                                                {{ $it->nombre }} (${{ number_format((float)$it->precio, 0, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium mb-1" style="color: #475569;">Cant.</label>
                            <input id="rfAdminAddItemQty" type="number" min="0.01" step="0.01" value="1"
                                   class="w-full px-3 py-2 rounded-lg text-sm"
                                   style="border: 1px solid #E2E8F0; background: white;">
                        </div>

                        <div class="sm:col-span-4">
                            <label class="block text-xs font-medium mb-1" style="color: #475569;">Nota</label>
                            <input id="rfAdminAddItemNote" type="text" placeholder="Opcional"
                                   class="w-full px-3 py-2 rounded-lg text-sm"
                                   style="border: 1px solid #E2E8F0; background: white;">
                        </div>

                        <div class="sm:col-span-12 flex justify-end">
                            <button id="rfAdminAddLineBtn" type="button"
                                    class="px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-1"
                                    style="background: #059669; color: white;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Agregar línea
                            </button>
                        </div>
                    </div>

                    <div class="rounded-lg p-3" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                        <div class="text-xs font-medium mb-2" style="color: #475569;">Líneas a agregar</div>
                        <div id="rfAdminLines" class="space-y-2">
                            <div class="text-xs text-center py-4" style="color: #94A3B8;">
                                No hay líneas todavía.
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="px-4 py-3 border-t flex items-center justify-end gap-2" style="border-color: #F1F5F9;">
                <button type="button"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                        style="background: white; border: 1px solid #E2E8F0; color: #475569;"
                        data-action="close-modal" data-modal="modalAddItemsAdmin">
                    Cancelar
                </button>
                <button type="submit"
                        form="modalAddItemsAdminForm"
                        class="px-4 py-2 rounded-lg text-sm font-medium text-white"
                        style="background: #0F172A;">
                    Guardar cambios
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Toast container (si no existe en layout) --}}
<div id="rfCajaToast"
     class="fixed bottom-5 right-5 z-50 pointer-events-none opacity-0 translate-y-2 transition duration-200 ease-out">
    <div class="pointer-events-auto rounded-2xl border border-emerald-200 bg-white shadow-lg px-4 py-3 flex items-start gap-3">
        <div class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
            ✅
        </div>
        <div class="min-w-0">
            <div class="font-extrabold text-slate-900" id="rfCajaToastTitle">Listo</div>
            <div class="text-sm text-slate-600" id="rfCajaToastMsg">Impreso.</div>
        </div>
        <button type="button" id="rfCajaToastClose" class="ml-2 text-slate-400 hover:text-slate-700 font-bold">
            ✕
        </button>
    </div>
</div>

<script>
(function(){
    const subtotalBase = Number(@json((float)$subtotal));
    const hayCaja = Boolean(@json($hayCaja));

    // Toast functions
    const toast = document.getElementById('rfCajaToast');
    const toastTitle = document.getElementById('rfCajaToastTitle');
    const toastMsg = document.getElementById('rfCajaToastMsg');
    const toastClose = document.getElementById('rfCajaToastClose');
    let toastTimer = null;

    function showToast(title, msg) {
        if (!toast) return;
        toastTitle.textContent = title || 'Listo';
        toastMsg.textContent = msg || '';
        toast.classList.remove('opacity-0', 'translate-y-2');
        toast.classList.add('opacity-100', 'translate-y-0');
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(hideToast, 2600);
    }

    function hideToast() {
        if (!toast) return;
        toast.classList.add('opacity-0', 'translate-y-2');
        toast.classList.remove('opacity-100', 'translate-y-0');
    }

    toastClose?.addEventListener('click', hideToast);

    // Notificaciones de impresión
    window.notifyPreticket = function(comandaId) {
        showToast('Pre-ticket impreso', 'Comanda #' + comandaId + ' enviada a impresión.');
    };

    window.notifyFinal = function(ventaId) {
        showToast('Ticket final impreso', 'Venta #' + ventaId + ' enviada a impresión.');
    };

    window.notifyTurno = function(turnoId) {
        showToast('Cierre de turno impreso', 'Turno #' + turnoId + ' enviado a impresión.');
    };

    // Escuchar mensajes de la ventana de impresión
    window.addEventListener('message', function(ev) {
        const data = ev.data || {};
        if (data.type !== 'RF_PRINT_DONE') return;

        console.log('Mensaje recibido:', data);

        if (data.mode === 'preticket' && data.comanda_id) {
            window.notifyPreticket(parseInt(data.comanda_id, 10));
        }

        if (data.mode === 'final' && data.venta_id) {
            window.notifyFinal(parseInt(data.venta_id, 10));
        }

        if (data.mode === 'turno' && data.turno_id) {
            window.notifyTurno(parseInt(data.turno_id, 10));
        }
    });

    function toNumber(v) {
        const n = parseFloat(String(v ?? '').replace(',', '.'));
        return isNaN(n) ? 0 : n;
    }

    function moneyAr(n) {
        return '$ ' + (Number(n).toLocaleString('es-AR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }));
    }

    let pagoIndex = 0;

    function pagoRowHtml(idx, tipo = 'efectivo', monto = '', ref = '') {
        return `
        <div class="pago-row rounded-lg p-2" data-idx="${idx}" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
            <div class="grid grid-cols-12 gap-2">
                <div class="col-span-4">
                    <select name="pagos[${idx}][tipo]" class="w-full px-2 py-1.5 rounded-lg text-xs" style="border: 1px solid #E2E8F0; background: white;">
                        <option value="efectivo" ${tipo === 'efectivo' ? 'selected' : ''}>Efectivo</option>
                        <option value="debito" ${tipo === 'debito' ? 'selected' : ''}>Débito</option>
                        <option value="transferencia" ${tipo === 'transferencia' ? 'selected' : ''}>Transferencia</option>
                    </select>
                </div>
                <div class="col-span-3">
                    <div class="relative">
                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-xs" style="color: #94A3B8;">$</span>
                        <input type="number" step="0.01" min="0.01"
                               name="pagos[${idx}][monto]"
                               class="pago-monto w-full pl-6 pr-2 py-1.5 rounded-lg text-xs"
                               style="border: 1px solid #E2E8F0; background: white;"
                               placeholder="0"
                               value="${monto}"
                               oninput="recalcTotales()"
                               required>
                    </div>
                </div>
                <div class="col-span-4">
                    <input type="text" maxlength="120"
                           name="pagos[${idx}][referencia]"
                           class="w-full px-2 py-1.5 rounded-lg text-xs"
                           style="border: 1px solid #E2E8F0; background: white;"
                           placeholder="Referencia"
                           value="${ref}">
                </div>
                <div class="col-span-1">
                    <button type="button"
                            class="w-full p-1.5 rounded-lg text-xs font-medium"
                            style="background: #FEF2F2; color: #DC2626;"
                            title="Quitar"
                            data-remove-pago="${idx}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        `;
    }

    window.recalcTotales = function recalcTotales() {
        const descuento = toNumber(document.querySelector('input[name="descuento"]')?.value);
        const recargo   = toNumber(document.querySelector('input[name="recargo"]')?.value);
        const propina   = toNumber(document.querySelector('input[name="propina"]')?.value);

        const total = Math.max(0, subtotalBase - descuento + recargo);
        window.__totalCobrar = total;
        window.__propina = propina;

        let pagado = 0;
        document.querySelectorAll('.pago-monto').forEach(inp => {
            pagado += toNumber(inp.value);
        });

        const vuelto = Math.max(0, pagado - total);

        const t1 = document.getElementById('totalCobrarText');
        const tProp = document.getElementById('propinaText');
        const t2 = document.getElementById('pagadoText');
        const t3 = document.getElementById('vueltoText');

        if (t1) t1.textContent = moneyAr(total);
        if (tProp) tProp.textContent = moneyAr(propina);
        if (t2) t2.textContent = moneyAr(pagado);
        if (t3) t3.textContent = moneyAr(vuelto);
    };

    window.addPagoRow = function addPagoRow(tipo, monto, ref) {
        const wrap = document.getElementById('pagosWrap');
        if (!wrap) return;
        wrap.insertAdjacentHTML('beforeend', pagoRowHtml(pagoIndex, tipo || 'efectivo', monto || '', ref || ''));
        pagoIndex++;
        window.recalcTotales();
    };

    window.removePagoRow = function removePagoRow(idx) {
        const row = document.querySelector(`.pago-row[data-idx="${idx}"]`);
        if (row) row.remove();
        window.recalcTotales();
    };

    document.getElementById('btnAddPago')?.addEventListener('click', function(){
        window.addPagoRow('efectivo', '', '');
    });

    document.addEventListener('click', function(e){
        const btn = e.target.closest('[data-remove-pago]');
        if(!btn) return;
        const idx = parseInt(btn.getAttribute('data-remove-pago') || '0', 10);
        window.removePagoRow(idx);
    });

    document.getElementById('cobroForm')?.addEventListener('submit', function (e) {
        if (!hayCaja) {
            e.preventDefault();
            alert('No hay turno de caja abierto. Abrí caja antes de cobrar.');
            return;
        }

        window.recalcTotales();

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

    // Código del modal de items
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
            lines.innerHTML = `<div class="text-xs text-center py-4" style="color: #94A3B8;">No hay líneas todavía.</div>`;
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
            row.className = "rounded-lg p-2";
            row.style.background = "white";
            row.style.border = "1px solid #E2E8F0";

            row.innerHTML = `
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="font-medium text-sm" style="color: #0F172A;">${escapeHtml(label)}</div>
                        ${notaVal ? `<div class="text-xs mt-1" style="color: #64748B;">📝 ${escapeHtml(notaVal)}</div>` : ``}
                    </div>
                    <div class="shrink-0 text-right">
                        <div class="text-xs" style="color: #64748B;">Cant. ${cantidad}</div>
                    </div>
                </div>

                <input type="hidden" name="items[${idx}][id_item]" value="${idItem}">
                <input type="hidden" name="items[${idx}][cantidad]" value="${cantidad}">
                <input type="hidden" name="items[${idx}][nota]" value="${escapeAttr(notaVal)}">

                <div class="mt-2 flex justify-end">
                    <button type="button" class="text-xs px-2 py-1 rounded-lg flex items-center gap-1"
                        style="color: #DC2626;"
                        data-remove-line="1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
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
            const row = rm.closest('div.rounded-lg');
            if (row) row.remove();
            if (!lines.children.length) renderEmpty();
        });

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

    document.addEventListener('DOMContentLoaded', () => {
        window.addPagoRow('efectivo', '', '');
        window.recalcTotales();
    });
})();
</script>
@endsection