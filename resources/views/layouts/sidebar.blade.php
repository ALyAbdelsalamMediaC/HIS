<!-- Sidebar Component -->
<div class="sidebar">
  <!-- Sidebar Toggle Button -->
  <div class="sidebar-open-btn">
    <x-svg-icon name="arrow-right" size="12" color="#b12028" />
  </div>
  <div class="head">
    <a href="" class="side-logo">
      <img src="{{ asset('images/logo/logo-full-white.svg') }}" alt="logo" class="logo-large">
      <img src="{{ asset('images/logo/hansy-small-white.svg') }}" alt="logo-small" class="logo-small">
    </a>
  </div>

  <div class="sidebar-mid w-100">
    <!-- Sidebar Header with Logos -->
    <!-- Sidebar Navigation -->
    <nav class="sidebar-menu">
      <ul>
        <li class="sidebar-menu-li">
          <a href="" class="h3-semibold">
            <x-svg-icon name="dashboard" size="20" />
            <span class="sidebar-menu-text">Dashboard</span>
          </a>
        </li>

        <li class="sidebar-menu-li">
          <a href="" class="h3-semibold">
            <x-svg-icon name="user-check" size="20" />
            <span class="sidebar-menu-text">Employees</span>
          </a>
        </li>

        <li class="sidebar-menu-li">
          <a href="" class="h3-semibold">
            <x-svg-icon name="money" size="20" />
            <span class="sidebar-menu-text">Payroll</span>
          </a>
        </li>
        <li class="sidebar-menu-li">
          <a href="" class="h3-semibold">
            <x-svg-icon name="user2" size="20" />
            <span class="sidebar-menu-text">Attendance</span>
          </a>
        </li>
        <li class="sidebar-menu-li">
          <a href="" class="h3-semibold">
            <x-svg-icon name="calender" size="20" />
            <span class="sidebar-menu-text">Meeting</span>
          </a>
        </li>

        <li class="sidebar-menu-li">
          <a href="" class="h3-semibold">
            <x-svg-icon name="lead" size="20" />
            <span class="sidebar-menu-text">Leads</span>
          </a>
        </li>

        <li class="sidebar-menu-li">
          <a href="" class="h3-semibold">
            <x-svg-icon name="history" size="20" />
            <span class="sidebar-menu-text">Leave Request</span>
          </a>
        </li>

        <li class="sidebar-menu-li">
          <a href="" class="h3-semibold">
            <x-svg-icon name="analysis-up" size="20" />
            <span class="sidebar-menu-text">Achievements</span>
          </a>
        </li>

        <li class="sidebar-menu-li">
          <a href="" class="h3-semibold">
            <x-svg-icon name="map-pin" size="20" />
            <span class="sidebar-menu-text">Branches</span>
          </a>
        </li>

        <li class="sidebar-menu-li">
          <a href="" class="h3-semibold">
            <x-svg-icon name="policy" size="20" />
            <span class="sidebar-menu-text">Private Policy</span>
          </a>
        </li>
      </ul>
    </nav>
  </div>

  <!-- Account Section -->
  <div class="sidebar-bottom">

  </div>
</div>

@push('scripts')
  <script src="{{ asset('js/layouts/sidebar.js') }}"></script>
@endpush