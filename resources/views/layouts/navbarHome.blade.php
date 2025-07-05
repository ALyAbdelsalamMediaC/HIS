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
        <div class="search-nav-home-input-container w-50 position-relative" x-data="searchDropdown()">
          <x-svg-icon name="search" size="18" color="#ADADAD" class="search-nav-home-icon" />
          <input type="text" name="search" placeholder="Search .." class="search-nav-home-input" autocomplete="off"
            x-model="query"
            @focus="show = true"
            @input.debounce.500ms="fetchResults"
            @click.away="show = false" />
          <div x-show="show" class="mt-3 bg-white rounded shadow search-dropdown position-absolute" style="width:320px;z-index: 1000; left:0; max-height: 350px; overflow-y: auto; scrollbar-width: thin;">
            <template x-for="video in results.videos.slice(0,5)" :key="video.id">
              <a :href="`/content/videos/${video.id}/${video.status}`" class="px-2 py-1 mb-2 search-item d-flex align-items-center text-decoration-none text-dark">
                <template x-if="video.thumbnail">
                  <img :src="video.thumbnail" alt="Thumbnail" class="me-2" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                </template>
                <template x-if="!video.thumbnail">
                  <span class="me-2" style="display:inline-block; width:40px; height:40px; background:#eee; border-radius:4px; text-align:center; line-height:40px;">
                    <svg width="24" height="24" fill="#ADADAD" viewBox="0 0 24 24"><path d="M17 10.5V7c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2v-3.5l4 4v-11l-4 4z"></path></svg>
                  </span>
                </template>
                <div>
                  <div class="fw-bold" x-text="video.title.length > 25 ? video.title.substring(0, 25) + '...' : video.title"></div>
                  <div class="small text-muted">
                    <span>Video | <span x-text="video.status"> </span> | <span x-text="video.created_at"></span></span>
                  </div>
                </div>
              </a>
            </template>
            <template x-for="article in results.articles.slice(0,5)" :key="article.id">
              <a :href="`/article/${article.id}`" class="px-2 py-1 mb-2 search-item d-flex align-items-center text-decoration-none text-dark">
                <template x-if="article.thumbnail">
                  <img :src="article.thumbnail" alt="Thumbnail" class="me-2" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                </template>
                <template x-if="!article.thumbnail">
                  <span class="me-2" style="display:inline-block; width:40px; height:40px; background:#eee; border-radius:4px; text-align:center; line-height:40px;">
                    <svg width="24" height="24" fill="#ADADAD" viewBox="0 0 24 24"><path d="M6 2a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6H6zm7 7V3.5L18.5 9H13z"></path></svg>
                  </span>
                </template>
                <div>
                  <div class="fw-bold" x-text="article.title.length > 25 ? article.title.substring(0, 25) + '...' : article.title"></div>
                  <div class="small text-muted">
                    <span>Article | </span><span x-text="article.created_at"></span>
                  </div>
                </div>
              </a>
            </template>
            <div x-show="query.length >= 2 && results.articles.length === 0 && results.videos.length === 0" class="px-2 py-1 search-item text-muted">
              No results found
            </div>
          </div>
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

<script>
function searchDropdown() {
  return {
    query: '',
    show: false,
    results: { articles: [], videos: [] },
    fetchResults() {
      if (this.query.length < 2) {
        this.results = { articles: [], videos: [] };
        return;
      }
      fetch(`/search?search=${encodeURIComponent(this.query)}&ajax=1`)
        .then(res => res.json())
        .then(data => {
          this.results = data;
        });
    }
  }
}
</script>