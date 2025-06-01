<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <form method="POST" action="{{ route('admin.password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label for="email">Email Address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                @error('email')
                    <span>{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="password">New Password</label>
                <input id="password" type="password" name="password" required>
                @error('password')
                    <span>{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="password_confirmation">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required>
            </div>
            <button type="submit">Reset Password</button>
        </form>
        <a href="{{ route('admin.login') }}">Back to Login</a>
    </div>
</body>
</html>