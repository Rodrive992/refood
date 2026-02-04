<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Ticket #{{ $venta->id }}</title>
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial; margin: 0; }
    .ticket { width: 300px; padding: 12px; }
    .center { text-align: center; }
    .row { display:flex; justify-content:space-between; gap:8px; }
    .muted { color:#555; font-size:12px; }
    .hr { border-top:1px dashed #999; margin:10px 0; }
    .item { font-size:13px; }
    .bold { font-weight:700; }
    @media print {
      .no-print { display:none; }
      body { margin:0; }
    }
  </style>
</head>
<body>
  <div class="ticket">
    <div class="center">
      <div class="bold">REfood</div>
      <div class="muted">Ticket de venta</div>
    </div>

    <div class="hr"></div>

    <div class="muted">
      <div>Venta: <span class="bold">#{{ $venta->id }}</span></div>
      <div>Comanda: #{{ $venta->id_comanda ?? '—' }}</div>
      <div>Mesa: {{ $venta->mesa->nombre ?? 'Sin mesa' }}</div>
      <div>Mozo: {{ $venta->mozo->name ?? '—' }}</div>
      <div>Fecha: {{ \Carbon\Carbon::parse($venta->sold_at)->format('d/m/Y H:i') }}</div>
    </div>

    <div class="hr"></div>

    @foreach($venta->comanda?->items ?? [] as $it)
      <div class="row item">
        <div style="flex:1">
          {{ $it->nombre_snapshot }}
          @if($it->nota)<div class="muted">* {{ $it->nota }}</div>@endif
        </div>
        <div class="bold">{{ rtrim(rtrim(number_format((float)$it->cantidad, 2, '.', ''), '0'), '.') }}</div>
        <div class="bold">$ {{ number_format((float)$it->precio_snapshot * (float)$it->cantidad, 2, ',', '.') }}</div>
      </div>
    @endforeach

    <div class="hr"></div>

    <div class="row"><div>Subtotal</div><div class="bold">$ {{ number_format((float)$venta->subtotal, 2, ',', '.') }}</div></div>
    <div class="row"><div>Descuento</div><div class="bold">$ {{ number_format((float)$venta->descuento, 2, ',', '.') }}</div></div>
    <div class="row"><div>Recargo</div><div class="bold">$ {{ number_format((float)$venta->recargo, 2, ',', '.') }}</div></div>

    <div class="hr"></div>

    <div class="row" style="font-size:16px;">
      <div class="bold">TOTAL</div>
      <div class="bold">$ {{ number_format((float)$venta->total, 2, ',', '.') }}</div>
    </div>

    <div class="hr"></div>

    <div class="muted bold">Pagos</div>
    @foreach($venta->pagos as $p)
      <div class="row muted">
        <div>{{ $p->tipo }}</div>
        <div>$ {{ number_format((float)$p->monto, 2, ',', '.') }}</div>
      </div>
      @if($p->referencia)
        <div class="muted">Ref: {{ $p->referencia }}</div>
      @endif
    @endforeach

    <div class="row muted"><div>Pagado</div><div class="bold">$ {{ number_format((float)$venta->pagado_total, 2, ',', '.') }}</div></div>
    <div class="row muted"><div>Vuelto</div><div class="bold">$ {{ number_format((float)$venta->vuelto, 2, ',', '.') }}</div></div>

    @if($venta->nota)
      <div class="hr"></div>
      <div class="muted"><span class="bold">Nota:</span> {{ $venta->nota }}</div>
    @endif

    <div class="hr"></div>

    <div class="center muted">Gracias por su compra</div>

    <div class="no-print" style="margin-top:10px;">
      <button onclick="window.print()" style="width:100%; padding:10px; font-weight:700;">
        Imprimir
      </button>

      @php
        $back = request('back') ?: route('admin.caja.index');
      @endphp

      <a href="{{ $back }}" style="display:block; text-align:center; margin-top:8px; color:#111; font-weight:700;">
        Volver a caja
      </a>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const back = @json(request('back') ?: route('admin.caja.index'));

      // Auto imprimir
      setTimeout(() => window.print(), 250);

      // Después de imprimir, volver a caja
      window.onafterprint = () => {
        if (back) window.location.href = back;
      };

      // Fallback por si el navegador no dispara onafterprint
      setTimeout(() => {
        if (back) window.location.href = back;
      }, 4000);
    });
  </script>
</body>
</html>
