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
        <div>
          <h3 class="h4-semibold">Notifications</h3>
          <p class="h5-ragular" style="color:#ADADAD;">Stay Update with your latest notification</p>
        </div>
        <x-link_btn href="#" class="expand-notif-icon">
          <x-svg-icon name="expand" size="18" color="#000" />
        </x-link_btn>
      </div>

      <div class="w-100 d-flex justify-content-end">
        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
          @csrf
          <x-button type="submit" class="mt-3 btn-nothing text-dark h4-ragular">
            <x-svg-icon name="double-check" size="17" />
            Mark as read
          </x-button>
        </form>
      </div>

      @php
          use Carbon\Carbon;
          $groupedUnreadNotifications = $unreadNotifications->groupBy(function ($notification) {
              $date = Carbon::parse($notification->created_at);
              if ($date->isToday()) {
                  return 'Today';
              } elseif ($date->isYesterday()) {
                  return 'Yesterday';
              } else {
                  return $date->format('F d, Y');
              }
          });
      @endphp

      @if($unreadNotifications->count() > 0)
          @foreach($groupedUnreadNotifications as $date => $notificationGroup)
              <li class="px-3 pt-2 pb-0" style="background:transparent; cursor:default;">
                  <span class="h5-ragular" style="color:#ADADAD;">{{ $date }}</span>
              </li>
              @php
                  $max = $date === 'Today' ? 3 : 1;
              @endphp
              @foreach($notificationGroup->take($max) as $notification)
                  <li class="mt-2 list-noti-style dropdown-item">
                      <a href="{{ route('notifications.read', $notification->id) }}" class="notification-item text-decoration-none">
                          <div>
                              <h3 class="h6-semibold" style="color:#000;">
                                  {{ $notification->sender->name ?? 'System' }}
                              </h3>
                              <p class="h6-ragular" style="color:#7B7B7B;">
                                  {{ Str::words($notification->body, 8, '...') }}
                              </p>
                              <span class="small text-muted">{{ $notification->created_at ? $notification->created_at->format('M d, Y \a\t h:i A') : 'N/A' }}</span>
                          </div>
                          <img src="/images/icons/dot_red.svg" alt="unread dot">
                      </a>
                  </li>
              @endforeach
          @endforeach
      @else
          <li class="mt-3 dropdown-item">
              <p class="mb-0">No new notifications</p>
          </li>
      @endif
    </div>
  </ul>
</div>