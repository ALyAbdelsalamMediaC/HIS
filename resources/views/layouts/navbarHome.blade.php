<header class="navbar-home-container">
  <div class="navbar-home-custome">
    <div class="gap-3 w-50 d-flex align-items-center">
      <div class="nav-mobile-hamburger d-md-none">
        <button class="p-0 border-0 btn" type="button" id="mobileMenuToggle" aria-label="Toggle mobile menu">
          <x-svg-icon name="three-dots" size="18" color="#fff" />
        </button>
      </div>
      <h2 class="h3-semibold" style="color:#fff;">Welcome , {{ auth()->user()->name }} 👋🏻</h2>
    </div>

    <div class="w-50 d-flex justify-content-end">
      <div class="gap-2 w-100 d-flex align-items-center justify-content-end">
        <div class="search-nav-home-input-container w-50">
          <x-svg-icon name="search" size="18" color="#ADADAD" class="search-nav-home-icon" />
          <input type="text" name="search" placeholder="Search .." class="search-nav-home-input" />
        </div>

        <div class="gap-4 d-flex align-items-center">
          <div class="notification-bell-btn">
            <x-notifications-dropdown :unreadNotifications="collect([])" />
          </div>
          <a href="{{ route('settings.profile') }}" class="gap-3 d-flex align-items-center">
            <span class="h6-semibold" style="color:#fff;">{{ auth()->user()->name }}</span>
            <span class="nav-home-profile"><x-svg-icon name="user" size="19" color="#35758c" /></span>
          </a>
        </div>
      </div>
    </div>
  </div>
</header>