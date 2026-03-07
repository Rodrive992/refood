{{-- resources/views/admin/ventas/ticket.blade.php --}}
{{-- Ticket final: propina separada del total de la venta --}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Ticket - Venta #{{ $venta->id }}</title>
    <style>
        body{ font-family: Arial, sans-serif; margin:0; padding:16px; background:#f8fafc; }
        .wrap{ max-width:420px; margin:0 auto; background:#fff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden; }
        .head{ padding:16px 18px; border-bottom:1px dashed #e5e7eb; }
        .body{ padding:14px 18px; }
        .muted{ color:#64748b; font-size:12px; }
        .mono{ font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace; }
        table{ width:100%; border-collapse:collapse; margin-top:10px; }
        th,td{ text-align:left; padding:6px 0; border-bottom:1px dashed #e5e7eb; font-size:13px; vertical-align:top; }
        .right{ text-align:right; }
        .total{ font-size:18px; font-weight:800; }
        .btnbar{ display:flex; justify-content:space-between; gap:8px; max-width:420px; margin:0 auto 10px; }
        .btn{ padding:10px 12px; border-radius:12px; border:1px solid #e5e7eb; background:#fff; font-weight:800; cursor:pointer; }
        .btnPrimary{ background:#0f172a; color:#fff; border-color:#0f172a; }
        @media print{
            .no-print{ display:none !important; }
            body{ padding:0; background:#fff !important; }
            .wrap{ border:none !important; border-radius:0 !important; }
            .head{ border-bottom:1px dashed #000 !important; }
        }
    </style>
</head>
<body>

    <div id="btnbar" class="no-print btnbar">
        <button class="btn btnPrimary" onclick="window.print()">🖨️ Imprimir</button>
        <a class="btn" href="{{ request('back') ?: route('admin.caja.index') }}" style="text-decoration:none; color:#0f172a;">Volver</a>
    </div>

    @php
        $impreso = now()->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i');
        $mesaNombre = $venta->mesa->nombre ?? 'Sin mesa';
        $mozoNombre = $venta->mozo->name ?? ('#'.$venta->id_mozo);
        $sold = $venta->sold_at
            ? \Carbon\Carbon::parse($venta->sold_at)->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i')
            : $impreso;
    @endphp

    <div class="wrap">
        <div class="head">
            <div class="muted">Ticket (final)</div>
            <div class="mono" style="font-size:20px; font-weight:900; margin-top:2px;">LA PISCALA</div>
            <div class="mono" style="font-size:18px; font-weight:900;">VENTA #{{ $venta->id }}</div>

            <div style="margin-top:10px; font-size:13px;">
                <div><b>Mesa:</b> {{ $mesaNombre }}</div>
                <div><b>Mozo:</b> {{ $mozoNombre }}</div>
                <div class="muted">Fecha: {{ $sold }}</div>
            </div>
        </div>

        <div class="body">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="right">Cant</th>
                        <th class="right">Importe</th>
                    </tr>
                </thead>
                <tbody>
                @foreach(($venta->comanda?->items ?? collect())->where('estado','!=','anulado') as $it)
                    @php $imp = (float)$it->precio_snapshot * (float)$it->cantidad; @endphp
                    <tr>
                        <td>
                            <div style="font-weight:800;">{{ $it->nombre_snapshot }}</div>
                            @if(!empty($it->nota))
                                <div class="muted">{{ $it->nota }}</div>
                            @endif
                        </td>
                        <td class="right">{{ rtrim(rtrim(number_format((float)$it->cantidad, 2, '.', ''), '0'), '.') }}</td>
                        <td class="right">$ {{ number_format($imp, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div style="margin-top:10px; display:grid; gap:6px;">
                <div style="display:flex; justify-content:space-between;">
                    <div class="muted">Subtotal</div>
                    <div><b>$ {{ number_format((float)$venta->subtotal, 2, ',', '.') }}</b></div>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <div class="muted">Descuento</div>
                    <div><b>$ {{ number_format((float)$venta->descuento, 2, ',', '.') }}</b></div>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <div class="muted">Recargo</div>
                    <div><b>$ {{ number_format((float)$venta->recargo, 2, ',', '.') }}</b></div>
                </div>
            </div>

            <div style="margin-top:10px; display:flex; justify-content:space-between; align-items:center;">
                <div class="muted">Total</div>
                <div class="total">$ {{ number_format((float)$venta->total, 2, ',', '.') }}</div>
            </div>

            @if((float)($venta->propina ?? 0) > 0)
                <div style="margin-top:8px; display:flex; justify-content:space-between; align-items:center;">
                    <div class="muted">Propina</div>
                    <div><b>$ {{ number_format((float)$venta->propina, 2, ',', '.') }}</b></div>
                </div>
            @endif

            <div style="margin-top:10px; display:grid; gap:6px;">
                <div style="display:flex; justify-content:space-between;">
                    <div class="muted">Pagado</div>
                    <div><b>$ {{ number_format((float)$venta->pagado_total, 2, ',', '.') }}</b></div>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <div class="muted">Vuelto</div>
                    <div><b>$ {{ number_format((float)$venta->vuelto, 2, ',', '.') }}</b></div>
                </div>
            </div>

            @if(!empty($venta->nota))
                <div class="muted" style="margin-top:12px;">
                    Nota: {{ $venta->nota }}
                </div>
            @endif

            <div class="muted" style="margin-top:10px;">
                Impreso: {{ $impreso }} (AR)
            </div>
        </div>
    </div>

    <script>
        (function(){
            try {
                if (window.top && window !== window.top) {
                    const bar = document.getElementById('btnbar');
                    if (bar) bar.style.display = 'none';
                }
            } catch (e) {}

            let notified = false;

            function notifyParent(){
                if (notified) return;
                notified = true;

                try {
                    if (window.parent) {
                        window.parent.postMessage({
                            type: 'RF_PRINT_DONE',
                            mode: 'final',
                            venta_id: {{ (int)$venta->id }}
                        }, '*');
                    }
                } catch(e){}
            }

            window.addEventListener('afterprint', function(){
                notifyParent();
            });

            setTimeout(notifyParent, 3000);
        })();
    </script>
</body>
</html>