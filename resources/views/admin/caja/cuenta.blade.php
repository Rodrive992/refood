<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Cuenta - Comanda #{{ $comanda->id }}</title>
    <style>
        body{ font-family: Arial, sans-serif; margin:0; padding:16px; }
        h1,h2,h3{ margin:0; }
        .muted{ color:#666; font-size:12px; }
        table{ width:100%; border-collapse: collapse; margin-top:10px; }
        th,td{ text-align:left; padding:6px 0; border-bottom:1px dashed #ddd; font-size:13px; }
        .right{ text-align:right; }
        .total{ font-size:18px; font-weight:700; }
        @media print { .no-print{ display:none; } body{ padding:0; } }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom:10px;">
        <button onclick="window.print()">Imprimir</button>
        <a href="{{ route('admin.caja.index') }}" style="margin-left:10px;">Volver</a>
    </div>

    <h2>REFOOD</h2>
    <div class="muted">Cuenta (pre-ticket)</div>

    <div style="margin-top:10px;">
        <div><strong>Mesa:</strong> {{ $comanda->mesa->nombre ?? 'Sin mesa' }}</div>
        <div><strong>Comanda:</strong> #{{ $comanda->id }}</div>
        <div><strong>Mozo:</strong> {{ $comanda->mozo->name ?? ('#'.$comanda->id_mozo) }}</div>
        <div class="muted">Solicitada: {{ optional($comanda->cuenta_solicitada_at)->format('d/m/Y H:i') }}</div>
        @if(!empty($comanda->cuenta_solicitada_nota))
            <div class="muted">Nota: {{ $comanda->cuenta_solicitada_nota }}</div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="right">Cant</th>
                <th class="right">Importe</th>
            </tr>
        </thead>
        <tbody>
        @foreach($comanda->items as $it)
            @php
                $imp = (float)$it->precio_snapshot * (float)$it->cantidad;
            @endphp
            <tr>
                <td>
                    {{ $it->nombre_snapshot }}
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

    <div style="margin-top:10px; display:flex; justify-content:space-between;">
        <div class="muted">Total estimado</div>
        <div class="total">$ {{ number_format((float)$subtotal, 0, ',', '.') }}</div>
    </div>

    <div class="muted" style="margin-top:14px;">
        * Este comprobante no es factura. Sujeto a cambios hasta el cobro.
    </div>

</body>
</html>
