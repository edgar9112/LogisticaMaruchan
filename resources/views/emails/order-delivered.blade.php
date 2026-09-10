@extends('emails.layout')

@section('content')
    <h2 style="margin:0 0 8px;color:#111827;font-size:16px;">Pedido entregado en tienda</h2>
    <p style="margin:0 0 16px;color:#374151;font-size:14px;">El pedido <strong>{{ $order->order_number }}</strong> fue entregado y recibido en tienda.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;color:#374151;">
        <tr><td style="padding:4px 0;">Tienda:</td><td style="padding:4px 0;text-align:right;"><strong>{{ $order->store?->name }}</strong></td></tr>
        <tr><td style="padding:4px 0;">Fecha de entrega:</td><td style="padding:4px 0;text-align:right;"><strong>{{ now()->format('d/m/Y H:i') }}</strong></td></tr>
        <tr><td style="padding:4px 0;">Estado:</td><td style="padding:4px 0;text-align:right;"><strong>{{ str_replace('_', ' ', $order->status) }}</strong></td></tr>
    </table>

    <p style="margin:20px 0 0;">
        <a href="{{ route('almacen.pedidos.show', $order) }}" style="display:inline-block;background-color:#7f1d1d;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:6px;font-size:13px;">Ver pedido</a>
    </p>
@endsection