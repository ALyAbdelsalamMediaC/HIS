@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Create Category</h2>

    @include('partials.flash')

    <form action="{{ route('categories.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Category Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            @error('name') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description (optional)</label>
            <textarea name="description" class="form-control">{{ old('description') }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Create</button>
        <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
