@extends('layouts.app')
@section('title', 'Google Authentication')
@section('navbar-title', 'Google Authentication')
@section('navbar-subtitle', 'Authorize Access')
@section('content')
    <section>
        <div class="d-flex flex-column align-items-center justify-content-center w-100">
            @if (isset($error))
                <p class="text-danger">{{ $error }}</p>
                <x-link_btn href="{{ url('/') }}" class="mt-3 btn btn-primary">Go Back Home</x-link_btn>
            @else
                <script>
                    window.location.href = "{{ $authUrl }}";
                </script>
                <noscript>
                    <p>JavaScript is required. <a href="{{ $authUrl }}" class="btn btn-primary">Click here to authorize</a></p>
                </noscript>
            @endif
        </div>
    </section>
@endsection