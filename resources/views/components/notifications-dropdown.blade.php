 @php
    use App\Models\Notification;
    use Illuminate\Support\Facades\Auth;
    use Carbon\Carbon;
    use App\Models\User;

    $today = Carbon::today();
    $yesterday = Carbon::yesterday();

    $unreadNotifications = Notification::with(['sender', 'receiver', 'media'])
      ->where('seen', false)
      ->orderBy('created_at', 'desc')
      ->get();

    $todayNotifications = $unreadNotifications->filter(function ($notification) use ($today) {
      return Carbon::parse($notification->created_at)->isToday();
    })->take(3);

    $yesterdayNotifications = $unreadNotifications->filter(function ($notification) use ($yesterday) {
      return Carbon::parse($notification->created_at)->isYesterday();
    })->take(3);
    @endphp

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
        <x-link_btn href="{{ route('notifications.index') }}" class="expand-notif-icon">
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

      @if($unreadNotifications->count() > 0)
          @if($todayNotifications->count() > 0)
              <li class="px-3 pt-2 pb-0" style="background:transparent; cursor:default;">
                  <span class="h5-ragular" style="color:#ADADAD;">Today</span>
              </li>
              @foreach($todayNotifications as $notification)
                  <li class="mt-2 list-noti-style dropdown-item">
                      <a href="{{ route('notifications.read', $notification->id) }}" class="notification-item text-decoration-none">
                          <div>
                              <h3 class="h6-semibold" style="color:#000;">
                                  {{ $notification->sender->name ?? 'System' }}
                              </h3>
                              <p class="h6-ragular" style="color:#7B7B7B;">
                                  {{ Str::words($notification->body, 8, '...') }}
                              </p>
                              <span class="small text-muted">
                                  {{ $notification->created_at ? $notification->created_at->format('h:i A') : 'N/A' }}
                              </span>
                          </div>
                          <img src="/images/icons/dot_red.svg" alt="unread dot">
                      </a>
                  </li>
              @endforeach
          @endif

          @if($yesterdayNotifications->count() > 0)
              <li class="px-3 pt-2 pb-0" style="background:transparent; cursor:default;">
                  <span class="h5-ragular" style="color:#ADADAD;">Yesterday</span>
              </li>
              @foreach($yesterdayNotifications as $notification)
                  <li class="mt-2 list-noti-style dropdown-item">
                      <a href="{{ route('notifications.read', $notification->id) }}" class="notification-item text-decoration-none">
                          <div>
                              <h3 class="h6-semibold" style="color:#000;">
                                  {{ $notification->sender->name ?? 'System' }}
                              </h3>
                              <p class="h6-ragular" style="color:#7B7B7B;">
                                  {{ Str::words($notification->body, 8, '...') }}
                              </p>
                              <span class="small text-muted">
                                  {{ $notification->created_at ? $notification->created_at->format('h:i A') : 'N/A' }}
                              </span>
                          </div>
                          <img src="/images/icons/dot_red.svg" alt="unread dot">
                      </a>
                  </li>
              @endforeach
          @endif
      @else
          <li class="mt-3 dropdown-item">
              <p class="mb-0">No new notifications</p>
          </li>
      @endif
    </div>
  </ul>
</div>