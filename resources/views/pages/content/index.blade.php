<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Library</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6 text-center">Media Library</h1>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('pages.content.getall') }}" class="mb-8 bg-white p-6 rounded-lg shadow-md">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Search by Title</label>
                    <input type="text" name="title" id="title" value="{{ request('title') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category" id="category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->name }}" {{ request('category') == $category->name ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">From Date</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">To Date</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Apply Filters
                </button>
            </div>
        </form>

        <!-- Error Message -->
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <!-- Media Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse ($media as $item)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    @if ($item->thumbnail_path)
                        <img src="{{ asset('storage/' . $item->thumbnail_path) }}" alt="{{ $item->title }}" class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-500">No Thumbnail</span>
                        </div>
                    @endif
                    <div class="p-4">
                        <h2 class="text-xl font-semibold mb-2">{{ $item->title }}</h2>
                        <p class="text-gray-600 mb-2">Category: {{ $item->category->name }}</p>
                        <p class="text-gray-500 text-sm">Uploaded: {{ $item->created_at->format('M d, Y') }}</p>
                        @if ($item->is_featured)
                            <span class="inline-block bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full mt-2">Featured</span>
                        @endif
                        @if ($item->is_recommended)
                            <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full mt-2">Recommended</span>
                        @endif
                        <a href="{{ route('pages.content.getone', $item->id) }}" class="mt-4 inline-block text-indigo-600 hover:text-indigo-800">View Details</a>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 col-span-full">No media found.</p>
            @endforelse
        </div>

        <!-- Reviewer Assignment (Optional, if needed) -->
        @if ($reviewers->count() > 0)
            <div class="mt-8 bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-4">Assign Reviewer</h2>
                <form action="{{ route('assign.reviewer') }}" method="POST">
                    @csrf
                    <div class="flex items-center space-x-4">
                        <select name="reviewer_id" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Reviewer</option>
                            @foreach ($reviewers as $reviewer)
                                <option value="{{ $reviewer->id }}">{{ $reviewer->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                            Assign
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</body>
</html>