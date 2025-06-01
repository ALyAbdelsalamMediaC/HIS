<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif
        <form method="POST" action="{{ route('admin.password.email') }}">
            @csrf
            <div>
                <label for="email">Email Address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                @error('email')
                    <span>{{ $message }}</span>
                @enderror
            </div>
            <button type="submit">Send Password Reset Link</button>
        </form>
        <a href="{{ route('admin.login') }}">Back to Login</a>
    </div>
</body>
</html>