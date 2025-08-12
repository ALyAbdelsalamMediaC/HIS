 @php
    use App\Models\Notification;
    use Illuminate\Support\Facades\Auth;
    use Carbon\Carbon;
    use App\Models\User;

    // Fetch the latest 6 notifications regardless of date
    $latestNotifications = Notification::where('receiver_id',auth()->id())->with(['sender', 'receiver', 'media'])
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

    // Show the bell indicator only if any of the displayed notifications is unread (seen == 0)
    $hasUnreadOnList = $latestNotifications->contains('seen', 0);
    @endphp

<div class="dropdown">
  <button class="p-0 border-0 btn" type="button" id="notificationDropdown" data-bs-toggle="dropdown"
    aria-expanded="false" aria-label="Notifications">
    @if($hasUnreadOnList)
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

      @if($latestNotifications->count() > 0)
          @foreach($latestNotifications as $notification)
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
                       @if(!$notification->seen)
                       <img src="/images/icons/dot_red.svg" alt="unread dot">
                       @endif
                  </a>
              </li>
          @endforeach
      @else
          <li class="mt-3 dropdown-item">
              <p class="mb-0">No notifications</p>
          </li>
      @endif
    </div>
  </ul>
</div>