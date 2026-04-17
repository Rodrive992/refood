{{-- resources/views/admin/comandas/reprint.blade.php --}}

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>La Piscala - Reimpresión de Comanda</title>

    <style>
        @page { margin: 8mm; }

        body {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 12px;
            color: #111;
            margin: 0;
            padding: 0;
        }

        .center { text-align: center; }
        .muted { color: #555; }
        .hr { border-top: 1px dashed #999; margin: 10px 0; }
        .row { display: flex; justify-content: space-between; gap: 10px; }
        .items { margin-top: 8px; }
        .item { padding: 6px 0; border-bottom: 1px dotted #bbb; }
        .item:last-child { border-bottom: 0; }
        .qty { width: 44px; }
        .name { flex: 1; padding-right: 10px; }
        .nowrap { white-space: nowrap; }

        .pedido-badge {
            display: inline-block;
            padding: 4px 8px;
            border: 1px dashed #444;
            border-radius: 6px;
            font-weight: 700;
            margin-top: 6px;
        }

        .reprint-badge {
            display: inline-block;
            padding: 4px 8px;
            border: 1px solid #dc2626;
            color: #dc2626;
            border-radius: 6px;
            font-weight: 800;
            margin-top: 6px;
        }

        .nota {
            margin-top: 3px;
            padding-left: 44px;
        }
    </style>
</head>
<body>

@php
    $printedAt = $printedAt ?? now()->timezone('America/Argentina/Buenos_Aires');
    $mesaNombre = $comanda->mesa->nombre ?? 'Sin mesa';
    $mozoNombre = $comanda->mozo->name ?? '—';
    $obs = trim((string)($comanda->observacion ?? ''));
    $pedidoNumero = isset($pedidoNumero) ? (int) $pedidoNumero : 1;
    $itemsPedido = collect($itemsPedido ?? []);
@endphp

<div class="center">
    <div style="font-weight: 800; font-size: 14px;">La Piscala - Comanda para Cocina</div>
    <div class="reprint-badge">REIMPRESIÓN</div>
    <div class="pedido-badge">Pedido #{{ $pedidoNumero }}</div>
    <div class="muted" style="margin-top: 4px;">
        {{ $printedAt->format('d/m/Y H:i') }} (AR)
    </div>
</div>

<div class="hr"></div>

<div class="row">
    <div><b>Comanda:</b> #{{ $comanda->id }}</div>
    <div class="nowrap"><b>Mesa:</b> {{ $mesaNombre }}</div>
</div>

<div style="margin-top: 4px;">
    <b>Mozo:</b> {{ $mozoNombre }}
</div>

@if($obs !== '')
    <div style="margin-top: 6px;">
        <b>Obs. general:</b> {{ $obs }}
    </div>
@endif

<div class="hr"></div>

<div class="items">
    @forelse($itemsPedido as $it)
        @php
            $qty = rtrim(rtrim(number_format((float)($it->cantidad ?? 0), 2, '.', ''), '0'), '.');
            $nombre = $it->nombre_snapshot ?? 'Item';
            $nota = trim((string)($it->nota ?? ''));
        @endphp

        <div class="item">
            <div class="row">
                <div class="qty"><b>x{{ $qty }}</b></div>
                <div class="name">{{ $nombre }}</div>
            </div>

            @if($nota !== '')
                <div class="muted nota">- {{ $nota }}</div>
            @endif
        </div>
    @empty
        <div class="muted">Sin items en este pedido.</div>
    @endforelse
</div>

<div class="hr"></div>

<div class="center muted">
    — FIN REIMPRESIÓN PEDIDO #{{ $pedidoNumero }} —
</div>

<script>
(function () {
    function notifyParent() {
        try {
            window.parent && window.parent.postMessage({
                type: 'RF_REPRINT_DONE',
                comanda_id: {{ (int) $comanda->id }},
                pedido_numero: {{ (int) $pedidoNumero }}
            }, '*');
        } catch (e) {}
    }

    window.addEventListener('load', function () {
        setTimeout(function () {
            try { window.focus(); } catch (e) {}
            window.print();
            notifyParent();
        }, 80);
    });

    window.addEventListener('afterprint', function () {
        notifyParent();
    });
})();
</script>

</body>
</html>