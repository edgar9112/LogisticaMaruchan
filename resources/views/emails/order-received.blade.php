@extends('emails.layout')

@section('content')
    <h2 style="margin:0 0 8px;color:#111827;font-size:16px;">Pedido recibido en almacén</h2>
    <p style="margin:0 0 16px;color:#374151;font-size:14px;">El pedido <strong>{{ $order->order_number }}</strong> ya entró al almacén.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;color:#374151;">
        <tr><td style="padding:4px 0;">Tienda:</td><td style="padding:4px 0;text-align:right;"><strong>{{ $order->store?->name }}</strong></td></tr>
        <tr><td style="padding:4px 0;">Almacén:</td><td style="padding:4px 0;text-align:right;"><strong>{{ $order->warehouse?->name ?? '—' }}</strong></td></tr>
        <tr><td style="padding:4px 0;">Fecha de recepción:</td><td style="padding:4px 0;text-align:right;"><strong>{{ $order->received_at?->format('d/m/Y H:i') }}</strong></td></tr>
        <tr><td style="padding:4px 0;">Unidades recibidas:</td><td style="padding:4px 0;text-align:right;"><strong>{{ $order->items->sum('quantity_received') }}</strong></td></tr>
    </table>

    <p style="margin:20px 0 0;">
        <a href="{{ route('almacen.pedidos.show', $order) }}" style="display:inline-block;background-color:#7f1d1d;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:6px;font-size:13px;">Ver pedido</a>
    </p>
@endsection