@extends('layouts.app')
@section('title', 'HIS | Dashboard')
@section('content')
    <h1>Welcome to Admin Dashboard</h1>

    <p>Hello, {{ auth()->user()->name }}!</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
    </body>

    </html>
@endsection

@push('scripts')
@endpush