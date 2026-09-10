@extends('emails.layout')

@section('content')
    <h2 style="margin:0 0 8px;color:#111827;font-size:16px;">Incidencia en ruta</h2>
    <p style="margin:0 0 16px;color:#374151;font-size:14px;">Se registró una incidencia durante el trayecto del embarque <strong>{{ $incident->shipment?->shipment_number ?? '—' }}</strong>.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;color:#374151;">
        <tr><td style="padding:4px 0;">Tipo:</td><td style="padding:4px 0;text-align:right;"><strong>{{ $incident->type_label }}</strong></td></tr>
        <tr><td style="padding:4px 0;">Fecha:</td><td style="padding:4px 0;text-align:right;"><strong>{{ $incident->occurred_at?->format('d/m/Y H:i') }}</strong></td></tr>
        <tr><td style="padding:4px 0;vertical-align:top;">Descripción:</td><td style="padding:4px 0;text-align:right;"><strong>{{ $incident->description }}</strong></td></tr>
    </table>

    <p style="margin:20px 0 0;">
        <a href="{{ route('viajes.show', $incident->shipment) }}" style="display:inline-block;background-color:#7f1d1d;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:6px;font-size:13px;">Ver viaje</a>
    </p>
@endsection