@extends('layouts.app')
@section('title', 'HIS | Notifications')
@section('content')

  <section>
  <div class="d-flex justify-content-between align-items-center">
    <div class="gap-3 d-flex align-items-center">
      <a href="{{ url()->previous() }}" class="arrow-back-btn">
        <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
      </a>

      <div>
        <h2 class="h2-semibold" style="color:#35758C;">Notifications</h2>
        <p class="h5-ragular" style="color:#ADADAD;">Stay upload with your latest notifications.</p>
      </div>
      </div>

      <form action="{{ route('notifications.mark-all-read') }}" method="POST">
      @csrf
      <x-button type="submit" class="h4-ragular"   style="background-color: transparent; color: #35758c; border: 1px solid #35758c;">
        <x-svg-icon name="double-check" size="19" />
        Mark all as read
      </x-button>
      </form>
      </div>
    
    <div class="mt-4">
    <div class="notification-head">
      <div class="gap-3 d-flex align-items-center">
      <!-- Bootstrap tabs -->
      <ul class="gap-3 border-0 nav nav-tabs d-flex align-items-center" id="notificationTabs" role="tablist">
        <li class="nav-item" role="presentation">
        <button class="border-0 nav-link active h2-semibold" id="all-tab" data-bs-toggle="tab"
          data-bs-target="#all-notifications" type="button" role="tab" aria-controls="all-notifications"
          aria-selected="true">All</button>
        </li>
        <li class="nav-item" role="presentation">
        <button class="gap-1 border-0 nav-link h3-ragular d-flex align-items-center" id="unread-tab" data-bs-toggle="tab"
          data-bs-target="#unread-notifications" type="button" role="tab" aria-controls="unread-notifications"
          aria-selected="false">
          Unread <span>({{ $unreadCount }})</span>
        </button>
        </li>
      </ul>
      </div>
    </div>

    <!-- Tab content -->
    <div class="tab-content" id="notificationTabsContent">
      <!-- All notifications tab -->
      <div class="tab-pane fade show active" id="all-notifications" role="tabpanel" aria-labelledby="all-tab">
      <div class="notifications-list">
        
        <div class="mt-4">
          @forelse($notifications as $notification)
          <div class="mb-1 notifications-content">
          <a href="{{ route('notifications.read', $notification->id) }}">
          <div class="notifications-content-left">
          <div class="notifications-icon">
          @if(isset($notification->sender) && $notification->sender->profile_image)
          <img src="{{ $notification->sender->profile_image}}" class="user-profile-img" style="width:32px;height:32px;border-radius:50%;object-fit:cover;" alt="User Image" />
                    @else
                    <div class="comment-container-user-icon">
                      <x-svg-icon name="user" size="18" color="#35758c" />
                    </div>
                    @endif
          </div>
          <div class="gap-1 d-flex flex-column">
          <h3 class="h6-semibold" style="color: black;">{{ $notification->title }}</h3>
          <h4 class="h6-ragular" style="color: #adadad;">{{ $notification->body }}</h4>
          <h5 style="color: #adadad; font-size: 12px; font-weight: 400;">
          {{ $notification->created_at ? $notification->created_at->format('M d, Y \a\t h:i A') : 'N/A' }}
          </h5>
          </div>
          </div>
          </a>
    
          @if(!$notification->seen)
          <img src="/images/icons/dot_red.svg" alt="dot_red">
        @endif
          </div>
        @empty
        <div class="mt-4 text-center">
        <p class="h4-ragular">No notifications found</p>
        </div>
      @endforelse
          </div>
          </div>
        </div>

      <!-- Unread notifications tab -->
      <div class="tab-pane fade" id="unread-notifications" role="tabpanel" aria-labelledby="unread-tab">
      <div class="notifications-list">
        @php
        $unreadNotifications = $notifications->where('seen', false);
        @endphp
        <div class="mt-4">

          @forelse($unreadNotifications as $notification)
        <div class="mb-1 notifications-content">
        <a href="{{ route('notifications.read', $notification->id) }}">
        <div class="notifications-content-left">
        <div class="notifications-icon">
        @if(isset($notification->sender) && $notification->sender->profile_image)
        <img src="{{ $notification->sender->profile_image}}" class="user-profile-img" style="width:32px;height:32px;border-radius:50%;object-fit:cover;" alt="User Image" />
        @else
        <div class="comment-container-user-icon">
          <x-svg-icon name="user" size="18" color="#35758c" />
        </div>
        @endif
        </div>
        <div class="gap-1 d-flex flex-column">
        <h3 class="h6-semibold" style="color: black;">{{ $notification->title }}</h3>
        <h4 class="h6-ragular" style="color: #adadad;">{{ $notification->body }}</h4>
        <h5 style="color: #adadad; font-size: 12px; font-weight: 400;">
        {{ $notification->created_at ? $notification->created_at->format('M d, Y \a\t h:i A') : 'N/A' }}
        </h5>
        </div>
        </div>
        </a>
  
        <img src="/images/icons/dot_red.svg" alt="dot_red">
        </div>
      @empty
      <div class="mt-4 text-center">
      <p class="h5-ragular">No unread notifications</p>
      </div>
    @endforelse
        </div>
        </div>
      </div>
        </div>
    </div>

    <!-- Table Info and Pagination -->
    <div class="bottom-vid-pagination d-flex justify-content-between align-items-center">
      @if($notifications->count())
          <x-table-info :paginator="$notifications" />
          <x-pagination :paginator="$notifications" :appends="request()->query()" />
      @endif
    </div>
  </section>

@endsection

@push('scripts')
@endpush