@extends('layouts.error')
@section('title', 'Error 404')
@section('content')
@section('navbar-title', '404')
@section('navbar-subtitle', 'Error')
    <section>
        <div class="d-flex flex-column align-items-center justify-content-center w-100" style="height: 80vh;">

            <h2 class="h1-semibold ">404</h2>
            <p class="mt-3 notfound-text">
                We couldn`t find any Data matching the provided details
            </p>
            <x-link_btn href="{{ url('/') }}" class="mt-3 btn btn-primary">Go Back Home</x-link_btn>
        </div>
    </section>
@endsection

<!-- Your scripts here to push it to the main app layout -->
@push('scripts')

@endpush