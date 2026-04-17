{{-- resources/views/mozo/partials/comanda.blade.php --}}
@php
    $isMobile = $isMobile ?? false;
    $mesa = $mesaSelected ?? null;
    $com = $comanda ?? null;
    $sub = $subtotal ?? 0;
    $mozoId = $mozoId ?? auth()->id();

    $cuentaPedida = $com && (int)($com->cuenta_solicitada ?? 0) === 1;
    $mesaTomadaPorOtro = $mesa
        && ($mesa->estado ?? '') !== 'libre'
        && !empty($mesa->atendida_por)
        && (int)$mesa->atendida_por !== (int)$mozoId;

    $nombreMozo = $mesa?->mozoAtendiendo?->name;

    $comandaPrintPendiente = $com && (int)($com->comanda_print_pendiente ?? 0) === 1;
    $comandaPrintImpresaAt = !empty($com?->comanda_print_impreso_at)
        ? \Carbon\Carbon::parse($com->comanda_print_impreso_at)->format('H:i')
        : null;

    $reprintPendiente = $com && (int)($com->reprint_pendiente ?? 0) === 1;
    $reprintPedidoNumero = $com ? (int)($com->reprint_pedido_numero ?? 0) : 0;

    $pedidoActualNumero = $com ? max(1, (int)($com->current_pedido_numero ?? 1)) : 1;

    $itemsOrdenados = $com
        ? $com->items->sortBy([
            ['pedido_numero', 'asc'],
            ['id', 'asc'],
        ])
        : collect();

    $itemsAgrupados = $itemsOrdenados->groupBy(function ($item) {
        return (int)($item->pedido_numero ?? 1);
    });

    $hayPedidos = $com && $itemsOrdenados->count() > 0;

    $itemsPedidoActual = $itemsAgrupados->get($pedidoActualNumero, collect());

    $pedidoActualTieneNuevos = $itemsPedidoActual
        ->where('estado', '!=', 'anulado')
        ->contains(fn($it) => empty($it->impreso_cocina_at));

    $pedidoActualYaFueImpresoCompleto = $itemsPedidoActual->isNotEmpty()
        && $itemsPedidoActual
            ->where('estado', '!=', 'anulado')
            ->every(fn($it) => !empty($it->impreso_cocina_at));

    $puedeImprimirPedidoActual = $com
        && !$cuentaPedida
        && !$mesaTomadaPorOtro
        && !$comandaPrintPendiente
        && $pedidoActualTieneNuevos;

    $textoBotonImpresion = 'Imprimir pedido #' . $pedidoActualNumero;
@endphp

<div class="bg-white rounded-2xl border rf-scrollbar" style="border-color: var(--rf-border);">
    <div class="p-4 flex items-start justify-between gap-3">
        <div>
            <h2 class="font-bold text-lg">Comanda</h2>

            @if(!$mesa)
                <p class="text-sm mt-1" style="color: var(--rf-text-light);">
                    Seleccioná una mesa para ver su comanda.
                </p>
            @else
                <div class="mt-1 flex items-center gap-2 flex-wrap">
                    <span class="text-sm font-semibold" style="color: var(--rf-text);">
                        Mesa: {{ $mesa->nombre }}
                    </span>

                    @php
                        $estadoMesa = $mesa->estado ?? '';
                        $badgeMesa = match($estadoMesa){
                            'ocupada' => ['bg'=>'var(--rf-primary-soft)','tx'=>'var(--rf-primary-hover)','label'=>'Ocupada'],
                            'reservada' => ['bg'=>'rgba(59,130,246,0.12)','tx'=>'var(--rf-info)','label'=>'Reservada'],
                            'libre' => ['bg'=>'var(--rf-secondary-soft)','tx'=>'var(--rf-secondary-hover)','label'=>'Libre'],
                            default => ['bg'=>'rgba(107,114,128,0.12)','tx'=>'var(--rf-text-light)','label'=>ucfirst($estadoMesa)],
                        };
                    @endphp

                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs"
                          style="background: {{ $badgeMesa['bg'] }}; color: {{ $badgeMesa['tx'] }};">
                        {{ $badgeMesa['label'] }}
                    </span>

                    @if($mesaTomadaPorOtro)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs"
                              style="background: rgba(239,68,68,0.12); color: #dc2626;">
                            Atendida por otro mozo
                        </span>
                    @endif

                    @if(!empty($mesa->observacion))
                        <span class="text-xs px-2 py-1 rounded-full"
                              style="background: var(--rf-border-light); color: var(--rf-text-light);">
                            {{ $mesa->observacion }}
                        </span>
                    @endif
                </div>

                @if($nombreMozo && ($mesa->estado ?? '') !== 'libre')
                    <div class="mt-2 text-xs font-semibold"
                         style="color: {{ $mesaTomadaPorOtro ? '#dc2626' : '#15803d' }};">
                        {{ $mesaTomadaPorOtro ? 'Atiende:' : 'Mozo asignado:' }} {{ $nombreMozo }}
                    </div>
                @endif
            @endif
        </div>

        @if($mesa)
            <div class="flex items-center gap-2 flex-wrap justify-end">
                @if(($mesa->estado ?? '') === 'libre')
                    <button
                        type="button"
                        class="px-4 py-2 rounded-xl text-sm font-semibold rf-hover-lift"
                        style="background: var(--rf-primary); color: white;"
                        data-action="ocupar"
                        data-mesa-id="{{ $mesa->id }}"
                        data-mesa-nombre="{{ $mesa->nombre }}"
                    >
                        Ocupar
                    </button>
                @else
                    @if($mesaTomadaPorOtro)
                        <button
                            type="button"
                            disabled
                            class="px-4 py-2 rounded-xl text-sm font-semibold border"
                            style="border-color: var(--rf-border); background: var(--rf-border-light); color: var(--rf-text-light);"
                            title="Esta mesa está siendo atendida por otro mozo"
                        >
                            Bloqueada
                        </button>
                    @else
                        <button
                            type="button"
                            class="px-4 py-2 rounded-xl text-sm font-semibold rf-hover-lift"
                            style="background: var(--rf-secondary); color: white;"
                            data-action="add-items"
                            data-mesa-id="{{ $mesa->id }}"
                            data-locked="{{ $cuentaPedida ? '1' : '0' }}"
                            title="{{ $cuentaPedida ? 'Cuenta solicitada: solo administración puede agregar.' : '' }}"
                        >
                            Agregar items
                        </button>

                        @if($com)
                            @if($comandaPrintPendiente)
                                <button
                                    type="button"
                                    disabled
                                    class="px-4 py-2 rounded-xl text-sm font-semibold border"
                                    style="border-color: var(--rf-primary); background: rgba(249,115,22,0.10); color: var(--rf-primary);">
                                    Pedido #{{ $pedidoActualNumero }} enviado
                                </button>
                            @elseif($puedeImprimirPedidoActual)
                                <form method="POST"
                                      action="{{ route('mozo.comandas.pedirImpresionCocina', $com) }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="px-4 py-2 rounded-xl text-sm font-semibold rf-hover-lift border"
                                        style="border-color: #0F172A; background: white; color: #0F172A;">
                                        {{ $textoBotonImpresion }}
                                    </button>
                                </form>
                            @else
                                <button
                                    type="button"
                                    disabled
                                    class="px-4 py-2 rounded-xl text-sm font-semibold border"
                                    style="border-color: var(--rf-border); background: var(--rf-border-light); color: var(--rf-text-light);"
                                    title="No hay items nuevos para imprimir en el pedido actual">
                                    {{ $textoBotonImpresion }}
                                </button>
                            @endif
                        @endif
                    @endif
                @endif
            </div>
        @endif
    </div>

    <div class="px-4 pb-4">
        @if(!$mesa)
            <div class="rounded-2xl border p-4 text-sm"
                 style="border-color: var(--rf-border); background: var(--rf-bg); color: var(--rf-text-light);">
                👈 Elegí una mesa para empezar.
            </div>
        @else
            <div class="rounded-2xl border p-4"
                 style="border-color: var(--rf-border); background: var(--rf-bg);">
                <div class="flex items-center justify-between gap-3">
                    <div class="text-sm">
                        <div class="font-semibold" style="color: var(--rf-text);">
                            {{ $com ? 'Comanda #' . $com->id : 'Sin comanda aún' }}
                        </div>
                        <div class="text-xs mt-1" style="color: var(--rf-text-light);">
                            {{ $com ? ('Estado: ' . ucfirst(str_replace('_',' ', $com->estado))) : 'Agregá items para crearla automáticamente.' }}
                        </div>

                        @if($com)
                            <div class="text-xs mt-1" style="color: var(--rf-text-light);">
                                Pedido actual: #{{ $pedidoActualNumero }}
                            </div>
                        @endif
                    </div>

                    <div class="text-right">
                        <div class="text-xs" style="color: var(--rf-text-light);">Subtotal total</div>
                        <div class="text-lg font-extrabold" style="color: var(--rf-text);">
                            {{ number_format((float)$sub, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                @if($cuentaPedida)
                    <div class="mt-3 text-xs px-3 py-2 rounded-xl"
                         style="background: rgba(245,158,11,0.12); color: var(--rf-warning);">
                        Cuenta solicitada. Solo administración puede agregar items y cerrar en caja.
                    </div>
                @endif

                @if($mesaTomadaPorOtro)
                    <div class="mt-3 text-xs px-3 py-2 rounded-xl"
                         style="background: rgba(239,68,68,0.10); color: #dc2626;">
                        Esta mesa está bloqueada para vos porque la está atendiendo {{ $nombreMozo ?? 'otro mozo' }}.
                    </div>
                @endif

                @if($com && $comandaPrintPendiente)
                    <div class="mt-3 text-xs px-3 py-2 rounded-xl"
                         style="background: rgba(249,115,22,0.10); color: var(--rf-primary);">
                        El pedido #{{ $pedidoActualNumero }} fue enviado a la computadora de administración para imprimir en cocina.
                    </div>
                @elseif($com && $comandaPrintImpresaAt)
                    <div class="mt-3 text-xs px-3 py-2 rounded-xl"
                         style="background: rgba(16,185,129,0.10); color: #059669;">
                        Última impresión de cocina registrada a las {{ $comandaPrintImpresaAt }}.
                    </div>
                @endif

                @if($com && !$comandaPrintPendiente && !$pedidoActualTieneNuevos && $pedidoActualYaFueImpresoCompleto)
                    <div class="mt-3 text-xs px-3 py-2 rounded-xl"
                         style="background: rgba(15,23,42,0.06); color: #475569;">
                        El pedido actual #{{ $pedidoActualNumero }} no tiene items nuevos para imprimir. Para pedidos anteriores usá los botones de reimpresión.
                    </div>
                @endif

                @if($com && $reprintPendiente && $reprintPedidoNumero > 0)
                    <div class="mt-3 text-xs px-3 py-2 rounded-xl"
                         style="background: rgba(59,130,246,0.10); color: #2563eb;">
                        La reimpresión del pedido #{{ $reprintPedidoNumero }} fue enviada a administración.
                    </div>
                @endif
            </div>

            <div class="mt-4">
                <h3 class="font-bold text-sm mb-2" style="color: var(--rf-text);">Pedidos</h3>

                @if(!$hayPedidos)
                    <div class="rounded-2xl border p-4 text-sm"
                         style="border-color: var(--rf-border); color: var(--rf-text-light);">
                        No hay items todavía.
                        @if(($mesa->estado ?? '') !== 'libre')
                            Tocá <b>Agregar items</b> para cargarlos.
                        @else
                            Primero <b>Ocupar</b>.
                        @endif
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($itemsAgrupados as $pedidoNumero => $pedidoItems)
                            @php
                                $pedidoNumero = (int) $pedidoNumero;

                                $pedidoItemsActivos = $pedidoItems->where('estado', '!=', 'anulado');

                                $pedidoSubtotal = $pedidoItemsActivos->sum(function ($it) {
                                    return (float)($it->precio_snapshot ?? 0) * (float)($it->cantidad ?? 0);
                                });

                                $todosImpresos = $pedidoItemsActivos->isNotEmpty()
                                    ? $pedidoItemsActivos->every(fn($it) => !empty($it->impreso_cocina_at))
                                    : false;

                                $tieneSinImprimir = $pedidoItemsActivos->contains(fn($it) => empty($it->impreso_cocina_at));

                                $esPedidoActual = $com && $pedidoNumero === $pedidoActualNumero;
                                $esReprintPendiente = $reprintPendiente && $pedidoNumero === $reprintPedidoNumero;

                                if ($comandaPrintPendiente && $esPedidoActual) {
                                    $estadoPedidoLabel = 'Enviado a cocina';
                                    $estadoPedidoBg = 'rgba(249,115,22,0.10)';
                                    $estadoPedidoColor = 'var(--rf-primary)';
                                } elseif ($esReprintPendiente) {
                                    $estadoPedidoLabel = 'Reimpresión enviada';
                                    $estadoPedidoBg = 'rgba(59,130,246,0.10)';
                                    $estadoPedidoColor = '#2563eb';
                                } elseif ($todosImpresos) {
                                    $estadoPedidoLabel = 'Impreso';
                                    $estadoPedidoBg = 'rgba(16,185,129,0.10)';
                                    $estadoPedidoColor = '#059669';
                                } elseif ($tieneSinImprimir && $esPedidoActual) {
                                    $estadoPedidoLabel = 'Abierto';
                                    $estadoPedidoBg = 'rgba(59,130,246,0.10)';
                                    $estadoPedidoColor = '#2563eb';
                                } else {
                                    $estadoPedidoLabel = 'Pendiente';
                                    $estadoPedidoBg = 'rgba(107,114,128,0.12)';
                                    $estadoPedidoColor = 'var(--rf-text-light)';
                                }

                                $horaImpresionPedido = $pedidoItems
                                    ->pluck('impreso_cocina_at')
                                    ->filter()
                                    ->map(fn($v) => \Carbon\Carbon::parse($v))
                                    ->sortBy(fn($dt) => $dt->timestamp)
                                    ->last();

                                $puedeReimprimir = $com
                                    && !$mesaTomadaPorOtro
                                    && !$cuentaPedida
                                    && $todosImpresos
                                    && !$esReprintPendiente;
                            @endphp

                            <div class="rounded-2xl border overflow-hidden"
                                 style="border-color: var(--rf-border); background: white;">
                                <div class="px-4 py-3 border-b flex items-center justify-between gap-3 flex-wrap"
                                     style="border-color: var(--rf-border); background: var(--rf-bg);">
                                    <div>
                                        <div class="font-bold text-sm" style="color: var(--rf-text);">
                                            Pedido #{{ $pedidoNumero }}
                                        </div>

                                        @if($horaImpresionPedido)
                                            <div class="text-xs mt-1" style="color: var(--rf-text-light);">
                                                Impreso a las {{ $horaImpresionPedido->format('H:i') }}
                                            </div>
                                        @elseif($esPedidoActual)
                                            <div class="text-xs mt-1" style="color: var(--rf-text-light);">
                                                Pedido actual para nuevas cargas
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs"
                                              style="background: {{ $estadoPedidoBg }}; color: {{ $estadoPedidoColor }};">
                                            {{ $estadoPedidoLabel }}
                                        </span>

                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs"
                                              style="background: rgba(15,23,42,0.06); color: #0F172A;">
                                            {{ number_format((float)$pedidoSubtotal, 0, ',', '.') }}
                                        </span>

                                        @if($puedeReimprimir)
                                            <form method="POST"
                                                  action="{{ route('mozo.comandas.reimprimirPedido', [$com, $pedidoNumero]) }}">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="px-3 py-1.5 rounded-xl text-xs font-semibold rf-hover-lift border"
                                                    style="border-color: #2563eb; background: white; color: #2563eb;">
                                                    Reimprimir pedido #{{ $pedidoNumero }}
                                                </button>
                                            </form>
                                        @elseif($esReprintPendiente)
                                            <button
                                                type="button"
                                                disabled
                                                class="px-3 py-1.5 rounded-xl text-xs font-semibold border"
                                                style="border-color: #2563eb; background: rgba(59,130,246,0.08); color: #2563eb;">
                                                Reimpresión en curso
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <div class="p-3 space-y-2">
                                    @foreach($pedidoItems as $it)
                                        @php
                                            $estadoItem = (string)($it->estado ?? 'pendiente');

                                            $badgeItem = match($estadoItem) {
                                                'pendiente' => ['bg' => 'rgba(59,130,246,0.10)', 'tx' => '#2563eb', 'label' => 'Pendiente'],
                                                'en_cocina' => ['bg' => 'rgba(249,115,22,0.10)', 'tx' => 'var(--rf-primary)', 'label' => 'En cocina'],
                                                'listo' => ['bg' => 'rgba(16,185,129,0.10)', 'tx' => '#059669', 'label' => 'Listo'],
                                                'entregado' => ['bg' => 'rgba(22,163,74,0.10)', 'tx' => '#15803d', 'label' => 'Entregado'],
                                                'anulado' => ['bg' => 'rgba(239,68,68,0.10)', 'tx' => '#dc2626', 'label' => 'Anulado'],
                                                default => ['bg' => 'rgba(107,114,128,0.12)', 'tx' => 'var(--rf-text-light)', 'label' => ucfirst($estadoItem)],
                                            };

                                            $fueImpreso = !empty($it->impreso_cocina_at);
                                        @endphp

                                        <div class="rounded-2xl border p-3"
                                             style="border-color: var(--rf-border); {{ $estadoItem === 'anulado' ? 'opacity:.65;' : '' }}">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0 flex-1">
                                                    <div class="font-semibold truncate" style="color: var(--rf-text);">
                                                        {{ $it->nombre_snapshot }}
                                                    </div>

                                                    @if(!empty($it->nota))
                                                        <div class="text-xs mt-1" style="color: var(--rf-text-light);">
                                                            Nota: {{ $it->nota }}
                                                        </div>
                                                    @endif

                                                    <div class="mt-2 flex items-center gap-2 flex-wrap">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs"
                                                              style="background: {{ $badgeItem['bg'] }}; color: {{ $badgeItem['tx'] }};">
                                                            {{ $badgeItem['label'] }}
                                                        </span>

                                                        @if($fueImpreso)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs"
                                                                  style="background: rgba(16,185,129,0.10); color: #059669;">
                                                                Impreso cocina
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="text-right shrink-0">
                                                    <div class="text-xs" style="color: var(--rf-text-light);">Cant.</div>
                                                    <div class="font-bold" style="color: var(--rf-text);">
                                                        {{ rtrim(rtrim(number_format((float)$it->cantidad, 2, '.', ''), '0'), '.') }}
                                                    </div>

                                                    <div class="text-xs mt-2" style="color: var(--rf-text-light);">Total</div>
                                                    <div class="font-extrabold" style="color: var(--rf-text);">
                                                        {{ number_format((float)$it->precio_snapshot * (float)$it->cantidad, 0, ',', '.') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if($isMobile && ($mesa->estado ?? '') !== 'libre' && !$mesaTomadaPorOtro)
                <div class="mt-4 rounded-2xl border p-4"
                     style="border-color: var(--rf-border); background: var(--rf-bg);">
                    <div class="text-sm font-bold" style="color: var(--rf-text);">
                        Carga rápida
                    </div>
                    <div class="text-xs mt-1" style="color: var(--rf-text-light);">
                        Entrá a <b>Agregar items</b>, elegí categoría y tocá items para acumularlos. Guardás al final.
                    </div>
                    @if($com)
                        <div class="text-xs mt-2" style="color: var(--rf-text-light);">
                            Los nuevos items se agregarán al pedido #{{ $pedidoActualNumero }}.
                        </div>
                    @endif
                </div>
            @endif
        @endif
    </div>
</div>