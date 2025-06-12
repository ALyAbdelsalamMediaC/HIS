<header class="navbar-custome">
  <div class="gap-3 d-flex align-items-center">
    <div class="nav-mobile-hamburger d-md-none">
      <button class="p-0 border-0 btn" type="button" id="mobileMenuToggle" aria-label="Toggle mobile menu">
        <x-svg-icon name="three-dots" size="18" color="#ADADAD" />
      </button>
    </div>
    <div class="position-relative" x-data="{ showResults: true, searchQuery: '' }">
      <x-svg-icon name="search" size="18" color="#ADADAD" />
      <input type="text" name="search" placeholder="Search .." class="nav-search" x-model="searchQuery"
        @focus="showResults = true" @click.away="showResults = false" />
      <div x-show="showResults">
        <x-search-list x-bind:search-query="searchQuery" />
      </div>
    </div>
  </div>

  <div class="gap-3 d-flex align-items-center">
    <div class="notification-bell-btn">
      <x-notifications-dropdown :unreadNotifications="collect([])" />
    </div>
    <a href="" class="nav-profile">
      <x-svg-icon name="user" size="18" color="#fff" />
    </a>
  </div>
</header>