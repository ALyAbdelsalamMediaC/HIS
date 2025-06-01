<!-- Sidebar Component -->
<div class="sidebar">
  <div class="head">
    <a href="" class="side-logo">
      <img src="{{ asset('images/logo/logo-full-white.svg') }}" alt="logo">
    </a>
  </div>

  <div class="sidebar-mid w-100">
    <!-- Sidebar Header with Logos -->
    <!-- Sidebar Navigation -->
    <nav class="sidebar-menu">
      <ul>
        <li class="sidebar-menu-li">
          <a href="" class="h6-semibold">
            <x-svg-icon name="dashboard" size="18" />
            <span class="sidebar-menu-text">Dashboard</span>
          </a>
        </li>

        <li class="sidebar-menu-li">
          <a href="" class="h6-semibold">
            <x-svg-icon name="user" size="18" />
            <span class="sidebar-menu-text">User Management</span>
          </a>
        </li>

        <li class="sidebar-menu-li">
          <a href="" class="h6-semibold">
            <x-svg-icon name="content" size="18" />
            <span class="sidebar-menu-text">Content</span>
          </a>
        </li>
        <li class="sidebar-menu-li">
          <a href="" class="h6-semibold">
            <x-svg-icon name="message" size="18" />
            <span class="sidebar-menu-text">Comments</span>
          </a>
        </li>
        <li class="sidebar-menu-li">
          <a href="" class="h6-semibold">
            <x-svg-icon name="shield-block" size="18" />
            <span class="sidebar-menu-text">Blocked Users</span>
          </a>
        </li>

        <li class="sidebar-menu-li">
          <a href="" class="h6-semibold">
            <x-svg-icon name="setting" size="18" />
            <span class="sidebar-menu-text">Settings</span>
          </a>
        </li>
      </ul>
    </nav>
  </div>

  <!-- Account Section -->
  <div class="sidebar-bottom">
    <a href="" class="h6-semibold">
      <x-svg-icon name="logout" size="18" />
      <span class="sidebar-menu-text">Log Out</span>
    </a>
  </div>
</div>

@push('scripts')
  <script src="{{ asset('js/layouts/sidebar.js') }}"></script>
@endpush