<!-- resources/views/layouts/auth_layout.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'HIS')</title>

  <!-- Link to Bootstrap CSS -->
  <link href="{{ asset('plugins/bootstrap/bootstrap.min.css') }}" rel="stylesheet">

  <!-- Custome CSS -->
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">
  <!-- <link href="{{ asset('css/app.min.css') }}" rel="stylesheet"> -->
</head>

<body class="auth-body">

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

  <div class="auth-container">
    @yield('content')
  </div>

  <!-- jQuery -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.js"
    integrity="sha512-8Z5++K1rB3U+USaLKG6oO8uWWBhdYsM3hmdirnOEWp8h2B1aOikj5zBzlXs8QOrvY9OxEnD2QDkbSKKpfqcIWw=="
    crossorigin="anonymous"></script>

  <!-- Include Bootstrap and other scripts -->
  <script src="{{ asset('plugins/bootstrap/bootstrap.bundle.min.js') }}"></script>

  @stack('scripts')
</body>

</html>