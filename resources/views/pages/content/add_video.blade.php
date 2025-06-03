@extends('layouts.app')
@section('title', 'HIS | Add Video')
@section('content')

    <section>
        <form method="POST" action="{{ route('content.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-control" name="category_id" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" name="title" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description (optional)</label>
                <textarea class="form-control" name="description"></textarea>
            </div>



            <div class="mb-3">
                <label for="file" class="form-label">Media Video</label>
                <input type="file" class="form-control" name="file" required>
            </div>
            <div class="mb-3">
                <label for="pdf" class="form-label">Media pdf</label>
                <input type="file" class="form-control" name="pdf" required>
            </div>

            <div class="mb-3">
                <label for="thumbnail" class="form-label">Image</label>
                <input type="file" class="form-control" name="thumbnail">
            </div>

            <div class="mb-2 form-check">
                <input class="form-check-input" type="checkbox" name="is_featured" value="1">
                <label class="form-check-label">Featured</label>
            </div>

            <div class="mb-3 form-check">
                <input class="form-check-input" type="checkbox" name="is_recommended" value="1">
                <label class="form-check-label">Recommended</label>
            </div>

            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </section>

@endsection

@push('scripts')
@endpush