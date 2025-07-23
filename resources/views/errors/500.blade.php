{{-- resources/views/errors/500.blade.php --}}

@extends('layouts.error')
@section('title', 'Error 500')
@section('content')
@section('navbar-title', '500')
@section('navbar-subtitle', 'Server Error')
    <section>
        <div class="d-flex flex-column align-items-center justify-content-center w-100" style="height: 80vh;">

            <h2 class="h1-semibold">500</h2>
            <h2 class="h3-semibold">Server Error</h2>
            <p class="mt-3 notfound-text">
                Sorry, something went wrong on our end.
            </p>
            <x-link_btn href="{{ url('/') }}" class="mt-3 btn btn-primary">Go Back Home</x-link_btn>
        </div>
    </section>
@endsection
