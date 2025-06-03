<div class="mobile-backdrop" id="mobileBackdrop"></div>

<!-- Sidebar Component -->
<div class="sidebar" id="sidebar">
  <!-- Mobile Close Button (only visible on mobile when sidebar is open) -->
  <div class="mobile-close-btn d-md-none">
    <button class="p-0 border-0 btn" type="button" id="mobileCloseBtn" aria-label="Close mobile menu">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M18 6L6 18M6 6L18 18" stroke="#35758c" stroke-width="2" stroke-linecap="round"
          stroke-linejoin="round" />
      </svg>
    </button>
  </div>

  <div class="head">
    <a href="{{ route('pages.admin.dashboard') }}" class="side-logo">
      <img src="{{ asset('images/logo/logo-dashboard.png') }}" alt="logo">
    </a>
  </div>

  <div class="sidebar-mid w-100">
    <!-- Sidebar Header with Logos -->
    <!-- Sidebar Navigation -->
    <nav class="sidebar-menu">
      <ul>
        <li class="sidebar-menu-li">
          <a href="{{ route('pages.admin.dashboard') }}" class="h6-semibold">
            <x-svg-icon name="dashboard" size="18" />
            <span class="sidebar-menu-text">Dashboard</span>
          </a>
        </li>

        <li class="sidebar-menu-li">
          <a href="{{ route('users.index') }}" class="h6-semibold">
            <x-svg-icon name="user" size="18" />
            <span class="sidebar-menu-text">User Management</span>
          </a>
        </li>

        <li class="sidebar-menu-li">
          <a href="{{ route('content.videos') }}" class="h6-semibold">
            <x-svg-icon name="content" size="18" />
            <span class="sidebar-menu-text">Content</span>
          </a>
        </li>
        <li class="sidebar-menu-li">
          <a href="{{ route('article.getall') }}" class="h6-semibold">
            <x-svg-icon name="content" size="18" />
            <span class="sidebar-menu-text">Article</span>
          </a>
        </li>
        <li class="sidebar-menu-li">
          <a href="{{ route('categories.index') }}" class="h6-semibold">
            <x-svg-icon name="content" size="18" />
            <span class="sidebar-menu-text">Categories</span>
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
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <x-button type="submit" class="h6-semibold btn-nothing">
        <x-svg-icon name="logout" size="18" />
        <span class="sidebar-menu-text">Log Out</span>
      </x-button>
    </form>
  </div>
</div>

@push('scripts')
  <script src="{{ asset('js/layouts/sidebar.js') }}"></script>
@endpush