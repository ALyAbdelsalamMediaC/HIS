@extends('layouts.app')
@section('title', 'HIS | Profile')
@section('content')
  <section>
    <div class="gap-3 d-flex align-items-center">
    <a href="{{ url()->previous() }}" class="arrow-back-btn">
      <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
    </a>

    <div>
      <h2 class="h2-semibold" style="color:#35758C;">Profile</h2>
      <p class="h5-ragular" style="color:#ADADAD;">Update your profile information</p>
    </div>
    </div>

    <form method="POST" action="{{ route('settings.updateProfile') }}" class="mt-4" novalidate>
    @csrf
    @method('PUT')

    <div class="form-infield">
      <x-text_label for="username" :required="true">Username</x-text_label>
      <x-text_input type="text" id="username" name="username" value="{{ old('username', auth()->user()->username) }}"
      placeholder="Enter your username" data-required="true" data-name="Username" />
      <div id="username-error-container">
      <x-input-error :messages="$errors->get('username')" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="name" :required="true">Name</x-text_label>
      <x-text_input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}"
      placeholder="Enter your name" data-required="true" data-name="Name" />
      <div id="name-error-container">
      <x-input-error :messages="$errors->get('name')" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="email" :required="true">Email</x-text_label>
      <x-text_input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}"
      placeholder="Enter your email" data-required="true" data-name="Email" />
      <div id="email-error-container">
      <x-input-error :messages="$errors->get('email')" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="phone" :required="true">Phone number</x-text_label>
      <x-text_input type="text" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}"
      placeholder="Phone number" data-required="true" data-name="Phone number" data-validate="phone" />
      <div id="phone-error-container">
      <x-input-error :messages="$errors->get('phone')" />
      </div>
    </div>

    <div class="mt-3 d-flex justify-content-end">
      <x-button type="submit">Update Profile</x-button>
    </div>
    </form>
  </section>
@endsection

@push('scripts')
  <script src="{{ asset('js/validations.js') }}"></script>
@endpush