@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card animate-fade-in-up">
                    <div class="card-header"><h4 class="mb-0">Editar tienda</h4></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('tiendas.update', $store) }}">
                            @csrf
                            @method('PUT')
                            @include('tiendas._form')
                            <div class="d-flex justify-content-end gap-2 mt-2">
                                <a href="{{ route('tiendas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
