@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-3 animate-fade-in-up">
            <div>
                <h3 class="mb-0">Nuevo embarque</h3>
                <p class="text-muted mb-0 small">Asigna pedidos preparados a un vehiculo para su traslado a tienda.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('embarques.store') }}">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Almacen de origen</label>
                    <select name="origin_warehouse_id" class="form-select" required>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('origin_warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Tienda destino</label>
                    <select name="destination_store_id" id="destination_store" class="form-select" required>
                        <option value="">— Seleccionar —</option>
                        @foreach ($stores as $store)
                            <option value="{{ $store->id }}" @selected(old('destination_store_id') == $store->id)>{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Vehiculo</label>
                    <select name="vehicle_id" class="form-select" required>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected(old('vehicle_id') == $vehicle->id)>
                                {{ $vehicle->code }} — {{ $vehicle->plate }} ({{ $vehicle->driver_name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Conductor</label>
                    <input type="text" name="driver_name" class="form-control"
                        value="{{ old('driver_name') }}" placeholder="Nombre del conductor">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Fecha programada</label>
                    <input type="datetime-local" name="scheduled_at" class="form-control"
                        value="{{ old('scheduled_at') }}">
                </div>
            </div>

            <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.1s;">
                <div class="card-header"><strong>Pedidos a embarcar</strong></div>
                <div class="card-body">
                    @foreach ($orders as $storeId => $storeOrders)
                        @php $store = $storeOrders->first()->store; @endphp
                        <h6 class="mt-2 mb-2 fw-bold">{{ $store->name }}</h6>
                        @foreach ($storeOrders as $order)
                            <div class="form-check order-checkbox mb-1" data-store="{{ $storeId }}">
                                <input class="form-check-input order-input" type="checkbox"
                                    name="order_ids[]" value="{{ $order->id }}"
                                    id="order-{{ $order->id }}"
                                    @checked(is_array(old('order_ids')) && in_array($order->id, old('order_ids')))>
                                <label class="form-check-label" for="order-{{ $order->id }}">
                                    <strong>{{ $order->order_number }}</strong>
                                    — {{ $order->items->sum('quantity_prepared') }} pzas &middot;
                                    {{ $order->prepared_at?->format('d/m/Y') }}
                                </label>
                            </div>
                        @endforeach
                    @endforeach

                    @if ($orders->isEmpty())
                        <div class="empty-state">
                            <div class="empty-state-icon">&#9654;</div>
                            <p class="mb-0">No hay pedidos preparados para embarcar.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('embarques.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                <button class="btn btn-primary" id="btn-submit">Crear embarque</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const storeSelect = document.getElementById('destination_store');

            function filterOrders() {
                const storeId = storeSelect.value;
                document.querySelectorAll('.order-checkbox').forEach(function (el) {
                    el.style.display = (storeId && storeId === el.dataset.store) ? '' : 'none';
                });
            }

            storeSelect.addEventListener('change', filterOrders);
            filterOrders();
        });
    </script>
@endsection
