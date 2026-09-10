@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="page-header animate-fade-in-up">
            <h3 class="mb-1">Trazabilidad</h3>
            <p class="text-muted mb-0">Consulta en que punto del flujo se encuentra un pedido.</p>
        </div>

        <div class="card mb-4 animate-fade-in-up" style="animation-delay:0.1s;">
            <div class="card-body">
                <form method="GET" action="{{ route('trazabilidad.index') }}" class="row g-2">
                    <div class="col-md-6 col-lg-5">
                        <label class="form-label fw-medium">Folio del pedido</label>
                        <div class="search-wrapper">
                            <input type="text" name="order_number" class="form-control"
                                value="{{ $search }}" placeholder="Ej. PED-20260910-0001" autofocus
                                style="padding-left:2.25rem;">
                            <span class="search-icon">&#8982;</span>
                        </div>
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <button class="btn btn-primary">Consultar</button>
                    </div>
                </form>
                <div class="form-text mt-2">
                    Puedes escribir el folio o <strong>escanearlo</strong> con la camara de tu celular.
                </div>
            </div>
        </div>

        @if (request()->has('order_number'))
            @if ($order)
                <div class="row g-3 animate-fade-in-up">
                    <div class="col-lg-9">
                        @include('partials.order_timeline', ['order' => $order])
                    </div>
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-header"><strong>Codigo QR</strong></div>
                            <div class="card-body text-center">
                                <div class="animate-scale-in">
                                    <img src="{{ $qr }}" alt="QR del pedido" class="mb-2 rounded" width="160"
                                        style="box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                                </div>
                                <p class="text-muted small mb-0 mt-2">{{ $order->order_number }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-warning animate-fade-in-up">
                    No se encontro ningun pedido con el folio <strong>{{ $search }}</strong>.
                </div>
            @endif
        @endif
    </div>
@endsection
