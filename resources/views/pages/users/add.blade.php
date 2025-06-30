@extends('layouts.app')
@section('title', 'HIS | Admin Registration')
@section('content')
  <section>
    <div class="gap-3 d-flex align-items-center">
    <a href="{{ url()->previous() }}" class="arrow-back-btn">
      <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
    </a>

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
    'reviewer' => 'Reviewer',
    'user' => 'User'
    ]" placeholder="Select Role" data-required="true" data-name="Role" />
      <div id="role-error-container">
      <x-input-error :messages="$errors->get('role')" />
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
      <x-text_label for="profile_image">Upload Profile Image</x-text_label>
      <div style="position: relative;">
        <x-text_input type="file" id="profile_image" name="profile_image"
          placeholder="Choose a profile image from your gallery" accept="image/jpeg,image/jpg,image/png"
          style="color: transparent; cursor: pointer;" onchange="updateProfileImageFileName(this)" />
        <div style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); padding-right: 16px;">
          <x-button type="button" onclick="document.getElementById('profile_image').click()">Choose file</x-button>
        </div>
      </div>
      <div id="profile_image-error-container">
        <x-input-error :messages="$errors->get('profile_image')" class="mt-2" />
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


@push('scripts')
  <script src="{{ asset('js/validations.js') }}"></script>
  <script src="{{ asset('js/showToast.js') }}"></script>
  <script>
    function validateProfileImageFile(input, expectedType, maxSizeBytes, errorMessage, defaultPlaceholder) {
      const file = input.files[0];
      if (file) {
        const validTypes = expectedType.split(',');
        if (!validTypes.includes(file.type)) {
          showToast('Please select a valid image (JPEG, JPG, PNG) file', 'danger');
          input.value = '';
          input.setAttribute('data-placeholder', defaultPlaceholder);
          return;
        }
        if (file.size > maxSizeBytes) {
          showToast(errorMessage, 'danger');
          input.value = '';
          input.setAttribute('data-placeholder', defaultPlaceholder);
          return;
        }
        input.setAttribute('data-placeholder', file.name);
      } else {
        input.setAttribute('data-placeholder', defaultPlaceholder);
      }
    }

    function updateProfileImageFileName(input) {
      validateProfileImageFile(input, 'image/jpeg,image/jpg,image/png', 3 * 1024 * 1024, 'Image size exceeds 3MB. Please choose a smaller image.', 'Choose a profile image from your gallery');
    }

    document.addEventListener('DOMContentLoaded', function () {
      const input = document.getElementById('profile_image');
      if (input) {
        input.style.setProperty('--webkit-file-upload-button', 'none');
        input.style.setProperty('--file-selector-button', 'none');
        if (!input.files || input.files.length === 0) {
          input.setAttribute('data-placeholder', 'Choose a profile image from your gallery');
        }
      }
      const style = document.createElement('style');
      style.textContent = `
        input[type="file"]::-webkit-file-upload-button,
        input[type="file"]::file-selector-button {
          display: none;
        }
        input[type="file"] {
          color: transparent;
        }
        input[type="file"]::before {
          content: attr(data-placeholder);
          color: #6c757d;
          position: absolute;
          padding-left: 11px;
          left: 12px;
          top: 50%;
          transform: translateY(-50%);
          pointer-events: none;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
          max-width: calc(100% - 130px);
        }
      `;
      document.head.appendChild(style);
    });
  </script>
@endpush