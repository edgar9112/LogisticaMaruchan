@extends('emails.layout')

@section('content')
    <h2 style="margin:0 0 8px;color:#111827;font-size:16px;">Embarque en ruta</h2>
    <p style="margin:0 0 16px;color:#374151;font-size:14px;">El embarque <strong>{{ $shipment->shipment_number }}</strong> salió con destino a la tienda.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;color:#374151;">
        <tr><td style="padding:4px 0;">Destino:</td><td style="padding:4px 0;text-align:right;"><strong>{{ $shipment->destinationStore?->name }}</strong></td></tr>
        <tr><td style="padding:4px 0;">Vehículo:</td><td style="padding:4px 0;text-align:right;"><strong>{{ $shipment->vehicle?->plate ?? '—' }}</strong></td></tr>
        <tr><td style="padding:4px 0;">Conductor:</td><td style="padding:4px 0;text-align:right;"><strong>{{ $shipment->driver_name ?: ($shipment->vehicle?->driver_name ?? '—') }}</strong></td></tr>
        <tr><td style="padding:4px 0;">Fecha de salida:</td><td style="padding:4px 0;text-align:right;"><strong>{{ $shipment->departed_at?->format('d/m/Y H:i') }}</strong></td></tr>
        <tr><td style="padding:4px 0;">Pedidos:</td><td style="padding:4px 0;text-align:right;"><strong>{{ $shipment->orders->pluck('order_number')->join(', ') }}</strong></td></tr>
    </table>

    <p style="margin:20px 0 0;">
        <a href="{{ route('viajes.show', $shipment) }}" style="display:inline-block;background-color:#7f1d1d;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:6px;font-size:13px;">Ver viaje</a>
    </p>
@endsection