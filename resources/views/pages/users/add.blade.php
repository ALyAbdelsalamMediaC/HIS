@extends('layouts.app')
@section('title', 'HIS | Admin Registration')
@section('content')
  <section>
    <div class="gap-3 d-flex align-items-center">
    <div class="arrow-back-btn" onclick="window.history.back()">
      <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
    </div>

    <div>
      <h2 class="h2-semibold" style="color:#35758C;">Admin Registration</h2>
      <p class="h5-ragular" style="color:#ADADAD;">Create a new admin account</p>
    </div>
    </div>

    <form method="POST" action="{{ route('admin.register') }}" class="mt-4" novalidate>
    @csrf

    <div class="form-infield">
      <x-text_label for="role" :required="true">Role</x-text_label>
      <x-select id="role" name="role" :options="[
    'admin' => 'Admin',
    'reviewer' => 'Reviewer'
    ]" placeholder="Select Role" data-required="true" data-name="Role" />
      <div id="role-error-container">
      <x-input-error :messages="$errors->get('role')" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="username" :required="true">Username</x-text_label>
      <x-text_input type="text" id="username" name="username" value="{{ old('username') }}"
      placeholder="Enter your username" data-required="true" data-name="Username" />
      <div id="username-error-container">
      <x-input-error :messages="$errors->get('username')" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="name" :required="true">Name</x-text_label>
      <x-text_input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Enter your name"
      data-required="true" data-name="Name" />
      <div id="name-error-container">
      <x-input-error :messages="$errors->get('name')" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="email" :required="true">Email</x-text_label>
      <x-text_input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Enter your email"
      data-required="true" data-name="Email" />
      <div id="email-error-container">
      <x-input-error :messages="$errors->get('email')" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="phone" :required="true">Phone number</x-text_label>
      <x-text_input type="text" id="phone" name="phone" placeholder="Phone number" data-required="true"
      data-name="Phone number" data-validate="phone" />
      <div id="phone-error-container">
      <x-input-error :messages="$errors->get('phone')" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="password" :required="true">Password</x-text_label>
      <x-text_input type="password" id="password" name="password" placeholder="Enter your password" data-required="true"
      data-name="Password" data-validate="password" />
      <div id="password-error-container">
      <x-input-error :messages="$errors->get('password')" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="password_confirmation" :required="true">Confirm Password</x-text_label>
      <x-text_input type="password" id="password_confirmation" name="password_confirmation"
      placeholder="Confirm your password" data-required="true" data-name="Confirm Password" />
    </div>

    <div class="mt-3 d-flex justify-content-end">
      <x-button type="submit">Register</x-button>
    </div>
    </form>
  </section>
@endsection

@push('scripts')
  <script src="{{ asset('js/validations.js') }}"></script>
@endpush