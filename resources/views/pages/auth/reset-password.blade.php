@extends('layouts.auth')

@section('content')
    <section class="auth-flex">
        {{-- left --}}
        <div class="login-img">
            <img src="{{ asset('images/global/login-img.png') }}" alt="Login Illustration">
        </div>

        {{-- right --}}
        <div class="log-form-side">
            <div class="login-logo">
                <img src="{{ asset('images/logo/his-login.png') }}" alt="HIS Logo">
            </div>

            <div class="login-form-con">
                <div class="login-form-head">
                    <h1 class="h1-semibold">Reset Password</h1>
                    <p class="h4-ragular">Please enter your new password below.</p>
                </div>

                <form method="POST" action="{{ route('admin.password.update') }}" novalidate>
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="form-infield-focus">
                        <x-text_input type="email" id="email" name="email" 
                            :value="old('email')" data-required="true" data-name="email" class="input-form-inner-login" />
                        <x-text_label for="email" class="label-form-inner-login">Email</x-text_label>
                        <div id="email-error-container">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                    </div>

                    <div class="form-infield-focus">
                        <div class="input-icon">
                            <x-text_input type="password" id="password" name="password"
                                :value="old('password')" data-required="true" data-name="password" class="input-form-inner-login" />
                        <x-text_label for="password" class="label-form-inner-login">New Password</x-text_label>

                            <div class="input-icon-eye" id="togglePassword">
                                <x-svg-icon name="eye" size="20" color="#000" class="eye-icon eye-show d-none" />
                                <x-svg-icon name="eye2" size="20" color="#000" class="eye-icon eye-hide" />
                            </div>
                        </div>
                        <div id="password-error-container">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                    </div>
                    
                                        <div class="form-infield-focus">
                        <div class="input-icon">
                            <x-text_input type="password" id="password_confirmation" name="password_confirmation"
                                :value="old('password_confirmation')" data-required="true" data-name="Confirm Password" class="input-form-inner-login" />
                        <x-text_label for="password_confirmation" class="label-form-inner-login">Confirm Password</x-text_label>

                            <div class="input-icon-eye" id="toggleConfirmPassword">
                                <x-svg-icon name="eye" size="20" color="#000" class="eye-icon eye-show d-none" />
                                <x-svg-icon name="eye2" size="20" color="#000" class="eye-icon eye-hide" />
                            </div>
                        </div>
                        <div id="password_confirmation-error-container">
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>
                    </div>

                    <button type="submit" class="w-100 button-login">
                        <span class="h4-semibold">Reset Password</span>
                        <x-svg-icon name="right-arrow" size="18" color="#fff" />
                    </button>
                </form>

                <div class="mt-3 forget-pass">
                    <a href="{{ route('login') }}" class="h5-semibold">Back to Login</a>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/validations.js') }}"></script>
    <script src="{{ asset('js/inputFocus.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Toggle password visibility for both password fields
            ['password', 'password_confirmation'].forEach(fieldId => {
                const toggleButton = document.getElementById(`toggle${fieldId.charAt(0).toUpperCase() + fieldId.slice(1)}`);
                const passwordInput = document.getElementById(fieldId);
                const eyeShow = toggleButton.querySelector('.eye-show');
                const eyeHide = toggleButton.querySelector('.eye-hide');

                toggleButton.addEventListener('click', function () {
                    const isHidden = passwordInput.type === 'password';
                    passwordInput.type = isHidden ? 'text' : 'password';
                    eyeShow.classList.toggle('d-none');
                    eyeHide.classList.toggle('d-none');
                });
            });
        });
    </script>
@endpush