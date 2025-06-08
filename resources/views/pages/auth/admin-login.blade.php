@extends('layouts.auth')
@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif
@section('content')
    <section class="auth-flex">
        {{-- left --}}
        <div class="login-img">
            <img src="{{ asset('images/global/login-img.png') }}" alt="Login Illustration">
        </div>

        {{-- left --}}
        <div class="log-form-side">
            <div class="login-logo">
                <img src="{{ asset('images/logo/his-login.png') }}" alt="HIS Logo">
            </div>

            <div class="login-form-con">
                <div class="login-form-head">
                    <h1 class="h1-semibold">Welcome To HIS , </h1>
                    <h1 class="h1-semibold">Login Now !</h1>
                    <p class="h4-ragular">Log in to stay connected and manage your videos and articles .</p>
                </div>

                <form action="{{ url('/admin/login') }}" method="POST" novalidate>
                    @csrf
                    <div class="form-infield">
                        <x-text_input type="text" id="login" name="login" 
                            :value="old('login')" data-required="true" data-name="login" />
                        <x-text_label for="login">login</x-text_label>
                        <div id="login-error-container">
                            <x-input-error :messages="$errors->get('login')" class="mt-2" />
                        </div>
                    </div>

                    <div class="form-infield">
                        <div class="input-icon">
                            <x-text_input type="password" id="password" name="password"
                                :value="old('password')" data-required="true" data-name="password" />
                        <x-text_label for="password">Password</x-text_label>

                            <div class="input-icon-eye" id="togglePassword">
                                <x-svg-icon name="eye" size="20" color="#000" class="eye-icon eye-show d-none" />
                                <x-svg-icon name="eye2" size="20" color="#000" class="eye-icon eye-hide" />
                            </div>
                        </div>
                    </div>
                    <div id="password-error-container">
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
            </div>

            <div class="forget-pass ">
                <a href="{{ route('admin.password.request') }}" class="h4-semibold">Forgot Password?</a>
            </div>

            <button type="submit" class="w-100 button-login">
                <span class="h4-semibold">Login</span>
                <x-svg-icon name="right-arrow" size="18" color="#fff" />
            </button>
            </form>

            <!-- <div class="mt-3 text-center">
                <span>Don't have an account?</span>
                <a href="{{ route('admin.register') }}">Register</a>
            </div> -->
        </div>
        </div>
    </section>

@endsection

@push('scripts')
    <script src="{{ asset('js/validations.js') }}"></script>
    <script src="{{ asset('js/inputFocus.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeShow = togglePassword.querySelector('.eye-show');
            const eyeHide = togglePassword.querySelector('.eye-hide');

            togglePassword.addEventListener('click', function () {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                eyeShow.classList.toggle('d-none');
                eyeHide.classList.toggle('d-none');
            });
        });
    </script>
@endpush