@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="mb-4">Admin Registration</h2>

                    <form method="POST" action="{{ route('admin.register') }}">
                        @csrf

                        {{-- Name --}}
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" value="{{ old('name') }}"
                                   class="form-control @error('name') is-invalid @enderror">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Role --}}
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror">
                                <option value="">Select Role</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="reviewer" {{ old('role') == 'reviewer' ? 'selected' : '' }}>Reviewer</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Username --}}
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" value="{{ old('username') }}"
                                   class="form-control @error('username') is-invalid @enderror">
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Confirm Password --}}
                        <div class="mb-4">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation"
                                   class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Register
                        </button>
                    </form>

                    <p class="mt-4 text-center">
                        Already have an account?
                        <a href="{{ route('login') }}">Login here</a>
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
