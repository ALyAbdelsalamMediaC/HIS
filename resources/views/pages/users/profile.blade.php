<!-- show $user , $content data -->
<form action="{{ route('users.change-password') }}" method="POST">
    @csrf
    <div>
        <label>Current Password</label>
        <input type="password" name="current_password" required>
    </div>
    <div>
        <label>New Password</label>
        <input type="password" name="new_password" required>
    </div>
    <div>
        <label>Confirm New Password</label>
        <input type="password" name="new_password_confirmation" required>
    </div>
    <button type="submit">Change Password</button>
</form>

@if (session('success'))
    <div style="color: green;">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div style="color: red;">{{ session('error') }}</div>
@endif