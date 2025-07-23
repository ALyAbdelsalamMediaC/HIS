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

    <form method="POST" action="{{ route('settings.updateProfile') }}" class="mt-4" novalidate enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="form-infield">
      <x-text_label for="name" :required="true">Name</x-text_label>
      <x-text_input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}"
      placeholder="Enter your name" data-required="true" data-name="Name" />
      <div id="name-error-container">
      <x-input-error :messages="$errors->get('name')" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="profile_image">Profile Image</x-text_label>
      <div style="position: relative;">
        <x-text_input type="file" id="profile_image" name="profile_image" placeholder="Choose a profile image from your gallery" accept="image/jpeg,image/jpg,image/png" style="color: transparent; cursor: pointer;" onchange="validateProfileImageFile(this, 'image/jpeg,image/jpg,image/png', 3 * 1024 * 1024, 'Image size exceeds 3MB. Please choose a smaller image.', 'Choose a profile image from your gallery'); previewImage(this, 'profile-image-preview')" />
        <div style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); padding-right: 16px;">
          <x-button type="button" onclick="document.getElementById('profile_image').click()">Choose file</x-button>
        </div>
      </div>
      @if(auth()->user()->profile_image)
      <div class="mt-2">
        <img src="{{ asset(auth()->user()->profile_image) }}" alt="Current profile image" style="max-width: 200px;">
      </div>
      @endif
      <!-- New Profile Image Preview -->
      <div id="profile-image-preview" class="mt-2" style="display: none;">
        <h6 class="h6-semibold" style="color:#35758C;">New Profile Image Preview:</h6>
        <img id="profile-image-preview-img" src="" alt="New profile image preview" style="max-width: 200px; border-radius: 8px;">
      </div>
      <div id="profile_image-error-container">
        <x-input-error :messages="$errors->get('profile_image')" class="mt-2" />
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="email" :required="true">Email</x-text_label>
      <x-text_input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}"
      placeholder="Enter your email" data-required="true" data-validate="email" data-name="Email" />
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

      function previewImage(input, previewId) {
          const file = input.files[0];
          if (file) {
              const reader = new FileReader();
              reader.onload = function(e) {
                  const preview = document.getElementById(previewId);
                  preview.style.display = 'block';
                  const img = document.getElementById(`${previewId}-img`);
                  img.src = e.target.result;
              };
              reader.readAsDataURL(file);
          }
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