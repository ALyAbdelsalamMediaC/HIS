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
                    <h1 class="h1-semibold">Forgot Password</h1>
                    <p class="h4-ragular">Enter your email address and we'll send you a link to reset your password.</p>
                </div>

                <form method="POST" action="{{ route('admin.password.email') }}" novalidate>
                    @csrf
                    <div class="form-infield-focus">
                        <x-text_input type="email" id="email" name="email" 
                            :value="old('email')" data-required="true" data-name="email" class="input-form-inner-login" />
                        <x-text_label for="email" class="label-form-inner-login">Email</x-text_label>
                        <div id="email-error-container">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                    </div>


                    <button type="submit" class="w-100 button-login">
                        <span class="h4-semibold">Send Password Reset Link</span>
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
@endpush