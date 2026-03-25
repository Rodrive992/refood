{{-- resources/views/admin/caja/cuenta_print.blade.php --}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Preticket #{{ $comanda->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            background: white;
            padding: 12px;
            width: 80mm;
            margin: 0 auto;
            font-size: 11px;
            line-height: 1.3;
        }
        .header { text-align: center; margin-bottom: 10px; }
        .restaurant { font-size: 16px; font-weight: bold; letter-spacing: 1px; }
        .comanda { font-size: 14px; font-weight: bold; margin: 4px 0; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .info { margin: 6px 0; }
        .info-row { display: flex; justify-content: space-between; margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0; }
        th { text-align: left; border-bottom: 1px dashed #000; padding: 4px 0; font-size: 10px; }
        td { padding: 4px 0; vertical-align: top; }
        .item-name { font-weight: bold; }
        .item-note { font-size: 9px; color: #444; margin-left: 4px; }
        .qty { text-align: center; }
        .price { text-align: right; }
        .total-row { font-weight: bold; font-size: 12px; border-top: 1px dashed #000; padding-top: 6px; margin-top: 4px; }
        .footer { font-size: 9px; text-align: center; margin-top: 12px; color: #444; }
        .line { border-top: 1px dashed #000; margin: 6px 0; }

        @media print {
            body { padding: 0; }
        }
    </style>
</head>
<body>
    @php
        $mesaNombre = $comanda->mesa->nombre ?? 'Sin mesa';
        $mozoNombre = $comanda->mozo->name ?? ('#'.$comanda->id_mozo);
        $sol = $comanda->cuenta_solicitada_at
            ? \Carbon\Carbon::parse($comanda->cuenta_solicitada_at)->timezone('America/Argentina/Buenos_Aires')->format('d/m H:i')
            : '—';
        $impreso = now()->timezone('America/Argentina/Buenos_Aires')->format('d/m H:i');
        $autoPrint = (int) request('autoprint', 1) === 1;
    @endphp

    <div class="header">
        <div class="restaurant">LA PISCALA</div>
        <div class="comanda">COMANDA #{{ $comanda->id }}</div>
    </div>

    <div class="divider"></div>

    <div class="info">
        <div class="info-row">
            <span>Mesa: <strong>{{ $mesaNombre }}</strong></span>
            <span>Mozo: <strong>{{ $mozoNombre }}</strong></span>
        </div>
        <div class="info-row">
            <span>Solicitada: {{ $sol }}</span>
        </div>
        @if(!empty($comanda->cuenta_solicitada_nota))
            <div class="info-row">
                <span>Nota: {{ $comanda->cuenta_solicitada_nota }}</span>
            </div>
        @endif
    </div>

    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="qty">Cant</th>
                <th class="price">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($comanda->items->where('estado','!=','anulado') as $it)
                @php $imp = (float)$it->precio_snapshot * (float)$it->cantidad; @endphp
                <tr>
                    <td>
                        <span class="item-name">{{ $it->nombre_snapshot }}</span>
                        @if(!empty($it->nota))
                            <div class="item-note">↳ {{ $it->nota }}</div>
                        @endif
                    </td>
                    <td class="qty">{{ rtrim(rtrim(number_format((float)$it->cantidad, 2, '.', ''), '0'), '.') }}</td>
                    <td class="price">${{ number_format($imp, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <div class="info-row total-row">
        <span>TOTAL ESTIMADO</span>
        <span>${{ number_format((float)$subtotal, 0, ',', '.') }}</span>
    </div>

    <div class="line"></div>

    <div class="footer">
        <div>* Pre-ticket - No válido como factura *</div>
        <div>Impreso: {{ $impreso }}</div>
        <div style="margin-top: 4px;">¡Gracias por su visita!</div>
    </div>

    <script>
        (function(){
            const autoPrint = @json($autoPrint);

            let printed = false;
            let notified = false;

            function notifyParent() {
                if (notified) return;
                notified = true;

                try {
                    if (window.parent && window.parent !== window) {
                        window.parent.postMessage({
                            type: 'RF_PRINT_DONE',
                            mode: 'preticket',
                            comanda_id: {{ (int)$comanda->id }}
                        }, '*');
                    }
                } catch (e) {
                    console.log('Error notificando:', e);
                }
            }

            function doPrint() {
                if (printed) return;
                printed = true;

                setTimeout(function() {
                    try {
                        window.focus();
                        window.print();
                    } catch (e) {
                        console.log('Error al imprimir preticket:', e);
                        notifyParent();
                    }
                }, 150);
            }

            window.addEventListener('afterprint', function() {
                notifyParent();
            });

            window.addEventListener('load', function() {
                if (autoPrint) {
                    doPrint();

                    setTimeout(function() {
                        notifyParent();
                    }, 3500);
                }
            });

            if (document.readyState === 'complete' && autoPrint) {
                doPrint();

                setTimeout(function() {
                    notifyParent();
                }, 3500);
            }
        })();
    </script>
</body>
</html>