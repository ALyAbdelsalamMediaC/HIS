<!DOCTYPE html>
<html>
<head>
    <title>Upload Content</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Upload Content</h2>

    @if(session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> Please fix the following issues:
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ url('/media/upload') }}" enctype="multipart/form-data">
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

        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="is_featured" value="1">
            <label class="form-check-label">Featured</label>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_recommended" value="1">
            <label class="form-check-label">Recommended</label>
        </div>

        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
</div>
</body>
</html>
