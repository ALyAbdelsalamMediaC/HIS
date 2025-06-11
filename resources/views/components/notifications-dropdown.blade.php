@props(['unreadNotifications' => collect([])])

<div class="dropdown">
  <button class="p-0 border-0 btn" type="button" id="notificationDropdown" data-bs-toggle="dropdown"
    aria-expanded="false" aria-label="Notifications">
    @if($unreadNotifications->count() > 0)
    <div class="bell-circle"></div>
  @endif
    <x-svg-icon name="bell" size="19" color="#ADADAD" />
  </button>

  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
    <div>
      <div class="upper-notification">
        <h3 class="h3-semibold">Notifications</h3>
        <x-link_btn href="#" class="px-3 py-2">
          View All
        </x-link_btn>
      </div>

      <div class="w-100 d-flex justify-content-end">
        <form action="#" method="POST">
          @csrf
          <x-button type="submit" class="mark-as-btn">
            <x-svg-icon name="double-check" size="19" />
            Mark as read
          </x-button>
        </form>
      </div>

      @if($unreadNotifications->count() > 0)
      @foreach($unreadNotifications as $notification)
      <li class="dropdown-item">
      <a href="#" class="notification-item text-decoration-none">
      <div class="gap-2 d-flex align-items-center">
      <img src="{{ $notification->sender->profile_image ?? asset('images/global/avatar.svg') }}"
        alt="{{ $notification->sender->name ?? 'User' }}"
        style="width: 29px; height: 29px; object-fit: cover; border-radius: 50%;">
      <p class="h5-semibold" style="color:#000;">
        <span><strong>{{ $notification->title }}:</strong></span> {{ $notification->body }}
      </p>
      </div>
      <img src="/images/icons/dot_red.svg" alt="unread dot">
      </a>
      </li>
    @endforeach
    @else
      <li class="dropdown-item">
      <p class="mb-0">No new notifications</p>
      </li>
    @endif
    </div>
  </ul>
</div>