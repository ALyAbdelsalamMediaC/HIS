@extends('layouts.app')
@section('title', 'Google Authentication')
@section('navbar-title', 'Google Authentication')
@section('navbar-subtitle', 'Authorize Access')
@section('content')
    <section>
        <div class="d-flex flex-column align-items-center justify-content-center w-100">
            @if (isset($error))
                <p class="text-danger">{{ $error }}</p>
                <x-link_btn href="{{ url('/') }}" class="btn btn-primary mt-3">Go Back Home</x-link_btn>
            @else
                <p>Open this link in your browser to authorize access:</p>
                <p><a href="{{ $authUrl }}" target="_blank" class="btn btn-primary">{{ $authUrl }}</a></p>
            @endif
        </div>
    </section>
@endsection