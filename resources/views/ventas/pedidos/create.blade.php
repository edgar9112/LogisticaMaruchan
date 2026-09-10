@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card mb-3 animate-fade-in-up">
                    <div class="card-header"><h4 class="mb-0">Nuevo pedido</h4></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('ventas.pedidos.store') }}" id="order-form">
                            @csrf

                            <div class="row mb-3 g-3">
                                <div class="col-md-6">
                                    <label for="store_id" class="form-label fw-medium">Tienda destino *</label>
                                    <select id="store_id" name="store_id" class="form-select @error('store_id') is-invalid @enderror">
                                        <option value="">&mdash; Selecciona la tienda &mdash;</option>
                                        @foreach ($stores as $store)
                                            <option value="{{ $store->id }}" @selected(old('store_id') == $store->id)>
                                                {{ $store->name }} ({{ $store->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('store_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="notes" class="form-label fw-medium">Notas (opcional)</label>
                                    <input type="text" id="notes" name="notes" class="form-control"
                                        value="{{ old('notes') }}" placeholder="Comentarios generales">
                                </div>
                            </div>

                            <h5 class="fw-bold mb-3">Productos</h5>
                            <div class="table-responsive">
                                <table class="table align-middle" id="items-table">
                                    <thead>
                                        <tr>
                                            <th style="width:60%">Presentacion</th>
                                            <th style="width:30%">Cantidad</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="item-row">
                                            <td>
                                                <select name="items[0][presentation_id]" class="form-select presentation-select @error('items.*.presentation_id') is-invalid @enderror">
                                                    <option value="">&mdash; Selecciona presentacion &mdash;</option>
                                                    @foreach ($presentations as $presentation)
                                                        <option value="{{ $presentation->id }}"
                                                            @selected(old('items.0.presentation_id') == $presentation->id)>
                                                            {{ $presentation->presentation_type }} &middot; {{ $presentation->flavor }} &mdash; {{ $presentation->sku }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][quantity]" min="1"
                                                    class="form-control quantity-input @error('items.*.quantity') is-invalid @enderror"
                                                    value="{{ old('items.0.quantity') }}" placeholder="0">
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-row" disabled>Quitar</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between mb-3">
                                <button type="button" class="btn btn-outline-primary" id="add-row">+ Agregar producto</button>
                                <div>
                                    @error('items')<span class="text-danger d-block">{{ $message }}</span>@enderror
                                    @error('items.*.presentation_id')<span class="text-danger d-block">{{ $message }}</span>@enderror
                                    @error('items.*.quantity')<span class="text-danger d-block">{{ $message }}</span>@enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('ventas.pedidos') }}" class="btn btn-outline-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Crear pedido</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <template id="presentation-option-template">
        @foreach ($presentations as $presentation)
            <option value="{{ $presentation->id }}">{{ $presentation->presentation_type }} &middot; {{ $presentation->flavor }} &mdash; {{ $presentation->sku }}</option>
        @endforeach
    </template>

    <script>
        (function () {
            let index = 1;
            const table = document.getElementById('items-table');

            document.getElementById('add-row').addEventListener('click', function () {
                const row = document.querySelector('.item-row').cloneNode(true);
                row.querySelector('.presentation-select').value = '';
                row.querySelector('.quantity-input').value = '';
                row.querySelector('.remove-row').disabled = false;
                row.querySelector('.presentation-select').name = `items[${index}][presentation_id]`;
                row.querySelector('.quantity-input').name = `items[${index}][quantity]`;
                row.style.opacity = '0';
                row.style.transform = 'translateY(8px)';
                table.querySelector('tbody').appendChild(row);
                // Animate in
                requestAnimationFrame(function () {
                    row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                });
                index++;
            });

            table.querySelector('tbody').addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-row')) {
                    const row = e.target.closest('tr');
                    row.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(20px)';
                    setTimeout(function () { row.remove(); }, 200);
                }
            });
        })();
    </script>
@endsection
