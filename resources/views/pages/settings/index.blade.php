@extends('layouts.app')
@section('title', 'HIS | Settings')
@section('content')

  <section>
    <div>
    <h2 class="h2-semibold" style="color:#35758C;">Settings</h2>
    <p class="h5-ragular" style="color:#ADADAD;">Manage your preferences and account detail .</p>
    </div>

    <h2 class="mt-4 mb-3 h3-semibold">Account Details:</h2>

    <div class="settings-links-content">
    <a href="{{ route('settings.profile') }}" class="settings-links-container">
      <span class="gap-2 d-flex align-items-center">
      <span class="setting-icon">
        <x-svg-icon name="user" size="18" color="#35758C" />
      </span>

      <span class="d-flex flex-column">
        <span class="h5-semibold" style="color:#000;">Account Details</span>
        <span class="h6-ragular" style="color:#ADADAD;">Manage your account details</span>
      </span>
      </span>

      <x-svg-icon name="arrow-right" size="24" color="#000" />
    </a>

    <a href="{{ route('settings.changePassword') }}" class="settings-links-container">
      <span class="gap-2 d-flex align-items-center">
      <span class="setting-icon">
        <x-svg-icon name="lock" size="18" color="#35758C" />
      </span>

      <span class="d-flex flex-column">
        <span class="h5-semibold" style="color:#000;">Password</span>
        <span class="h6-ragular" style="color:#ADADAD;">Change your password</span>
      </span>
      </span>

      <x-svg-icon name="arrow-right" size="24" color="#000" />
    </a>

    <a href="" class="settings-links-container">
      <span class="gap-2 d-flex align-items-center">
      <span class="setting-icon">
        <x-svg-icon name="bell" size="18" color="#35758C" />
      </span>

      <span class="d-flex flex-column">
        <span class="h5-semibold" style="color:#000;">Notification</span>
        <span class="h6-ragular" style="color:#ADADAD;">Manage your notification settings</span>
      </span>
      </span>

      <x-svg-icon name="arrow-right" size="24" color="#000" />
    </a>

    <a href="{{ route('policies.index') }}" class="settings-links-container">
      <span class="gap-2 d-flex align-items-center">
      <span class="setting-icon">
        <x-svg-icon name="info" size="18" color="#35758C" />
      </span>

      <span class="d-flex flex-column">
        <span class="h5-semibold" style="color:#000;">Help Center</span>
        <span class="h6-ragular" style="color:#ADADAD;">Get help with the app</span>
      </span>
      </span>

      <x-svg-icon name="arrow-right" size="24" color="#000" />
    </a>
    </div>
  </section>

@endsection

@push('scripts')
@endpush