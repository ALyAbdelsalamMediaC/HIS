@extends('layouts.app')
@section('title', 'HIS | Change Password')
@section('content')

  <section>
    <div class="gap-3 d-flex align-items-center">
    <div class="arrow-back-btn" onclick="window.history.back()">
      <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
    </div>

    <div>
      <h2 class="h2-semibold" style="color:#35758C;">Change Password</h2>
      <p class="h5-ragular" style="color:#ADADAD;">Update your account password</p>
    </div>
    </div>

    <form method="POST" action="{{ route('settings.showChangePasswordForm') }}" class="mt-4" novalidate>
    @csrf

    <div class="form-infield">
      <x-text_label for="current_password" :required="true">Current Password</x-text_label>
      <x-text_input type="password" id="current_password" name="current_password"
      placeholder="Enter your current password" data-required="true" data-name="Current Password" />
      <div id="current_password-error-container">
      <x-input-error :messages="$errors->get('current_password')" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="new_password" :required="true">New Password</x-text_label>
      <x-text_input type="password" id="new_password" name="new_password" placeholder="Enter your new password"
      data-required="true" data-name="New Password" data-validate="password" data-validate="password" />
      <div id="new_password-error-container">
      <x-input-error :messages="$errors->get('new_password')" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="new_password_confirmation" :required="true">Confirm New Password</x-text_label>
      <x-text_input type="password" id="new_password_confirmation" name="new_password_confirmation"
      placeholder="Confirm your new password" data-required="true" data-name="Confirm New Password" />
    </div>

    <div class="mt-3 d-flex justify-content-end">
      <x-button type="submit">Update Password</x-button>
    </div>
    </form>
  </section>

@endsection

@push('scripts')
  <script src="{{ asset('js/validations.js') }}"></script>
@endpush