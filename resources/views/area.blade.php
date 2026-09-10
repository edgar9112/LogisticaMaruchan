@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card animate-fade-in-up">
                    <div class="card-body text-center py-5">
                        <div style="font-size:3rem;opacity:0.3;" class="mb-3">&#9733;</div>
                        <h4 class="mb-2">{{ $title }}</h4>
                        <p class="text-muted mb-0">{{ $description }}</p>
                        <hr>
                        <p class="text-muted small mb-0">Este modulo se implementara en la siguiente fase del proyecto.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
