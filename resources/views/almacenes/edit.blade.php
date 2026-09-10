@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card mb-4 animate-fade-in-up">
                    <div class="card-header"><h4 class="mb-0">Editar almacen</h4></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('almacenes.update', $warehouse) }}">
                            @csrf
                            @method('PUT')
                            @include('almacenes._form')
                            <div class="d-flex justify-content-end gap-2 mt-2">
                                <a href="{{ route('almacenes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card animate-fade-in-up" style="animation-delay:0.1s;">
                    <div class="card-header"><h5 class="mb-0">Ubicaciones del almacen</h5></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('almacenes.locations.store', $warehouse) }}" class="row g-2 mb-3">
                            @csrf
                            <div class="col-md-4">
                                <input type="text" name="code" class="form-control" placeholder="Ej. ZONA-E" required>
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="description" class="form-control" placeholder="Descripcion (opcional)">
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary w-100">Agregar</button>
                            </div>
                        </form>

                        @if ($warehouse->locations->isEmpty())
                            <div class="empty-state">
                                <div class="empty-state-icon">&#9878;</div>
                                <p class="mb-0">Este almacen aun no tiene ubicaciones.</p>
                            </div>
                        @else
                            <table class="table table-hover table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Descripcion</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($warehouse->locations as $location)
                                        <tr class="stagger-child">
                                            <td><code>{{ $location->code }}</code></td>
                                            <td>{{ $location->description ?? '—' }}</td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="event.preventDefault(); if(confirm('Eliminar esta ubicacion?')) document.getElementById('remove-loc-{{ $location->id }}').submit();">
                                                    Eliminar
                                                </button>
                                                <form id="remove-loc-{{ $location->id }}" method="POST"
                                                    action="{{ route('almacenes.locations.destroy', $location) }}" class="d-none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
