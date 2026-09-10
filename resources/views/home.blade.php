@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card animate-fade-in-up">
                <div class="card-header"><h4 class="mb-0">{{ __('Dashboard') }}</h4></div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="text-center py-4">
                        <div style="font-size:3rem;opacity:0.3;" class="mb-3">&#10003;</div>
                        <h5>{{ __('You are logged in!') }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
