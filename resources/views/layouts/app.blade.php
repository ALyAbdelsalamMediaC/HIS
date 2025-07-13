<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HIS')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap CSS -->
    <link href="{{ asset('plugins/bootstrap/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Select 2 -->
    <link href="{{ asset('plugins/select2/select2.min.css') }}" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <!-- QuillJS CSS (should be after custom CSS) -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
</head>

<body>
    <!-- Success Message -->
    @if(session('success'))
        <x-toast :messages="session('success')" type="success" />
    @endif

    @if(session('status'))
        <x-toast :messages="session('status')" type="success" />
    @endif

    <!-- Error Messages -->
    @if($errors->any())
        <x-toast :messages="$errors->all()" type="danger" />
    @endif

    @if (session('error'))
        <x-toast :messages="session('error')" type="danger" />
    @endif

    <!-- Loading Overlay -->
    <x-loading_overlay />

    <div class="d-flex">
        @include('layouts.sidebar')
        <div class="main-content w-100 min-vh-100 d-flex justify-content-between flex-column position-relative">
            <div>
                @if(Route::is('dashboard.index'))
                    @include('layouts.navbarHome')
                @else
                    @include('layouts.navbar')
                @endif

                <main class="mt-4 mb-5 container-fluid container-fix">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.js"
        integrity="sha512-8Z5++K1rB3U+USaLKG6oO8uWWBhdYsM3hmdirnOEWp8h2B1aOikj5zBzlXs8QOrvY9OxEnD2QDkbSKKpfqcIWw=="
        crossorigin="anonymous"></script>

    <!-- Bootstrap Bundle JS -->
    <script src="{{ asset('plugins/bootstrap/bootstrap.bundle.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/select2.min.js') }}"></script>

    <!-- custom scripts -->
    <script src="{{ asset('js/loadingOverlay.js') }}"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- QuillJS JS -->
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <!-- Resumable.js for chunked uploads -->
    <script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.min.js"></script>

    @stack('scripts')

</body>

</html>