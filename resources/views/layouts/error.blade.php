<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HIS')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap CSS -->
    <link href="{{ asset('plugins/bootstrap/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body>

    <div class="d-flex">
        <div class="w-100 min-vh-100 d-flex justify-content-between flex-column">
            <div>
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

    <!-- custom scripts -->
    <script src="{{ asset('js/loadingOverlay.js') }}"></script>

    @stack('scripts')

</body>

</html>