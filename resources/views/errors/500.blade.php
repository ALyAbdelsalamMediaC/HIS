{{-- resources/views/errors/500.blade.php --}}

@extends('layouts.error')

@section('title', 'Server Error')

@section('content')
<div class="container text-center mt-5">
    <h1 class="display-1">500</h1>
    <h2>Server Error</h2>
    <p>Sorry, something went wrong on our end.</p>
    <a href="{{ url('/') }}" class="btn btn-primary mt-3">Go Back Home</a>
</div>
@endsection
