@extends('layouts.app')

@section('content')
@php
    // subtotal viene del controller (Admin\ComandaController@show)
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

        <!-- COBRO -->
        <aside class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-4 border-b border-gray-200">
                <h2 class="font-bold text-gray-900">Cobrar</h2>
                <p class="text-xs text-gray-500 mt-1">Pago mixto, descuento/recargo y ticket.</p>
            </div>

            @if($esCerrada)
                <div class="p-5 text-sm text-gray-700">
                    Esta comanda está <span class="font-bold">{{ $comanda->estado }}</span>.
                    No se puede cobrar.
                </div>
            @else
                <form method="POST" action="{{ route('admin.comandas.cobrar', $comanda) }}" class="p-4 space-y-4"
                      x-data="cobroPos({{ (float)$subtotal }})">
                    @csrf

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-bold text-gray-700">Descuento</label>
                            <input type="number" step="0.01" min="0" name="descuento"
                                   x-model.number="descuento"
                                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                                   placeholder="0">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-700">Recargo</label>
                            <input type="number" step="0.01" min="0" name="recargo"
                                   x-model.number="recargo"
                                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                                   placeholder="0">
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-700">Nota</label>
                        <input type="text" name="nota" maxlength="255"
                               class="mt-1 w-full rounded-lg border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                               placeholder="Ej: paga con transferencia">
                    </div>

                    <!-- TOTAL -->
                    <div class="rounded-xl border border-gray-200 p-3 bg-gray-50">
                        <div class="flex items-center justify-between text-sm text-gray-700">
                            <span>Subtotal</span>
                            <span>$ <span x-text="fmt(subtotal)"></span></span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-gray-700 mt-1">
                            <span>Descuento</span>
                            <span>- $ <span x-text="fmt(descuento)"></span></span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-gray-700 mt-1">
                            <span>Recargo</span>
                            <span>+ $ <span x-text="fmt(recargo)"></span></span>
                        </div>
                        <div class="h-px bg-gray-200 my-2"></div>
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-gray-900">TOTAL</span>
                            <span class="text-xl font-extrabold text-gray-900">
                                $ <span x-text="fmt(total)"></span>
                            </span>
                        </div>
                    </div>

                    <!-- PAGOS -->
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-gray-700">Pagos</label>
                            <button type="button"
                                    class="text-xs font-bold px-3 py-1.5 rounded-lg bg-white border border-gray-200 hover:bg-gray-50"
                                    @click="addPago()">
                                + Agregar pago
                            </button>
                        </div>

                        <template x-for="(p, idx) in pagos" :key="idx">
                            <div class="mt-2 rounded-xl border border-gray-200 p-3">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="text-[11px] font-bold text-gray-700">Tipo</label>
                                        <select class="mt-1 w-full rounded-lg border-gray-300"
                                                :name="`pagos[${idx}][tipo]`"
                                                x-model="p.tipo">
                                            <option value="efectivo">Efectivo</option>
                                            <option value="debito">Débito</option>
                                            <option value="transferencia">Transferencia</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-[11px] font-bold text-gray-700">Monto</label>
                                        <input type="number" step="0.01" min="0.01"
                                               class="mt-1 w-full rounded-lg border-gray-300"
                                               :name="`pagos[${idx}][monto]`"
                                               x-model.number="p.monto"
                                               placeholder="0">
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <label class="text-[11px] font-bold text-gray-700">Referencia (opcional)</label>
                                    <input type="text" maxlength="120"
                                           class="mt-1 w-full rounded-lg border-gray-300"
                                           :name="`pagos[${idx}][referencia]`"
                                           x-model="p.referencia"
                                           placeholder="Alias / Nº comprobante">
                                </div>

                                <div class="mt-2 flex justify-end">
                                    <button type="button"
                                            class="text-xs font-bold px-3 py-1.5 rounded-lg bg-red-50 text-red-700 border border-red-200 hover:bg-red-100"
                                            @click="removePago(idx)"
                                            x-show="pagos.length > 1">
                                        Quitar
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- RESUMEN PAGO -->
                    <div class="rounded-xl border border-gray-200 p-3">
                        <div class="flex items-center justify-between text-sm text-gray-700">
                            <span>Pagado</span>
                            <span>$ <span x-text="fmt(pagado)"></span></span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-gray-700 mt-1">
                            <span>Vuelto</span>
                            <span>$ <span x-text="fmt(vuelto)"></span></span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-gray-700 mt-1">
                            <span>Falta</span>
                            <span>$ <span x-text="fmt(falta)"></span></span>
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full px-4 py-3 rounded-xl bg-green-600 text-white font-extrabold hover:opacity-95"
                            :disabled="!puedeCobrar"
                            :class="!puedeCobrar ? 'opacity-50 cursor-not-allowed' : ''">
                        Confirmar cobro y emitir ticket
                    </button>

                    <p class="text-xs text-gray-500 leading-relaxed">
                        El cobro crea una <b>Venta</b> + <b>Pagos</b>, cierra la comanda y libera la mesa.
                    </p>
                </form>

                <script>
                    function cobroPos(subtotalInicial) {
                        return {
                            subtotal: Number(subtotalInicial || 0),
                            descuento: 0,
                            recargo: 0,
                            pagos: [
                                { tipo: 'efectivo', monto: 0, referencia: '' },
                            ],

                            fmt(n) {
                                n = Number(n || 0);
                                // Formato AR simple (sin depender de Intl)
                                return n.toFixed(2).replace('.', ',');
                            },

                            get total() {
                                const t = Math.max(0, this.subtotal - Number(this.descuento || 0) + Number(this.recargo || 0));
                                return Number(t);
                            },

                            get pagado() {
                                return this.pagos.reduce((acc, p) => acc + Number(p.monto || 0), 0);
                            },

                            get vuelto() {
                                return Math.max(0, this.pagado - this.total);
                            },

                            get falta() {
                                return Math.max(0, this.total - this.pagado);
                            },

                            get puedeCobrar() {
                                // Permitimos igual si falta = 0 y total > 0 (o total==0)
                                if (this.total <= 0) return this.pagado >= 0;
                                return this.falta <= 0.00001 && this.pagos.length >= 1 && this.pagos.some(p => Number(p.monto || 0) > 0);
                            },

                            addPago() {
                                this.pagos.push({ tipo: 'efectivo', monto: 0, referencia: '' });
                            },

                            removePago(idx) {
                                if (this.pagos.length <= 1) return;
                                this.pagos.splice(idx, 1);
                            }
                        }
                    }
                </script>
            @endif
        </aside>

    </div>
</div>
@endsection
