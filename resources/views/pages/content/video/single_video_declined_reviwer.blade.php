@extends('layouts.app')
@section('title', 'HIS | Video - Reviewer Review (Declined)')
@section('content')

  <div class="gap-3 mb-4 d-flex align-items-center">
    <a href="{{ url()->previous() }}" class="arrow-back-btn">
    <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
    </a>
    <div>
    <h2 class="h2-semibold" style="color:#35758C;">Video Reviewer Review (Declined)</h2>
    <p class="h5-ragular" style="color:#ADADAD;">This video has been declined. You can view your rating and comments below.</p>
    </div>
  </div>

  <section class="single-video-container">

    <!-- Video -->
    <video controls style="width: 100%; border-radius: 20px; height: 600px;" preload="none">
    <source src="{{ route('content.stream', ['id' => $media->id]) }}" type="video/mp4">
    Your browser does not support the video tag.
    </video>

    <!-- Video Title -->
    <div class="gap-3 mt-3 d-flex align-items-center">
    <h2 class="h3-semibold">{{ $media->title }}</h2>
    <h4 class="h6-ragular card-status {{ $media->status }}">
      {{ ucfirst($media->status) }}
    </h4>
    </div>

    <!-- Video Details -->
    <div class="d-flex justify-content-between align-items-center">
    <div class="gap-3 mt-2 d-flex align-items-center">
      <span class="h5-ragular" style="color:#ADADAD;">
      <x-format-duration :seconds="$media->duration" />
      </span>
      <span class="h5-ragular" style="color:#ADADAD;">Uploaded
      {{ $media->created_at->diffForHumans() }}</span>
      <span class="h5-ragular" style="color:#ADADAD;">by {{ $media->user->name }}</span>
    </div>
    </div>

    <!-- Video Description -->
    <div class="mt-3 single-discription">
    <div class="h5-ragular quill-content">{!! $media->description !!}</div>
    </div>

    <!-- Video Mentions  -->
     <div class="gap-4 mt-3 d-flex align-items-center">
      <h3 class="h5-semibold">Mentioned to :</h3>
      <div class="flex-wrap gap-3 d-flex align-items-center">
        @if($media->mention && is_array(json_decode($media->mention, true)))
          @foreach(json_decode($media->mention, true) as $mentionedUser)
            <div style="padding: 10px 20px; border-radius: 32px; border: 1px solid #EDEDED;">
              <h3 class="h6-ragular" style="color:#7B7B7B;">{{ '@' . $mentionedUser }}</h3>
            </div>
          @endforeach
        @else
          <div style="padding: 10px 20px; border-radius: 32px; border: 1px solid #EDEDED;">
            <h3 class="h6-ragular" style="color:#7B7B7B;">No mentions</h3>
          </div>
        @endif
      </div>
     </div>

    <!-- Video Assetes -->
    <div class="gap-3 mt-4 d-flex align-items-center">
    <!-- PDF  -->
    @if($media->pdf)
    <a href="{{ $media->pdf }}" target="_blank" class="d-flex align-items-center justify-content-between w-100" style="border: 1px solid var(--Silver-100, #EDEDED); border-radius: 12px; padding: 12px 24px;">
      <span class="gap-2 d-flex align-items-center">
      <span style="background-color: #F1F9FA; border-radius: 8px; padding: 12px;">
      <x-svg-icon name="document" size="24" color="Black" />
      </span>
      <span class="d-flex flex-column">
      <span class="h5-semibold" style="color:#000;">Document</span>
      <span class="h5-ragular" style="color:#ADADAD;">PDF</span>
      </span>
      </span>
      <span> <x-svg-icon name="pop-out" size="24" color="Black" /></span>
    </a>
    @endif

    <!-- Image -->
    @if($media->image_path)
    <x-button type="button" data-bs-toggle="modal" data-bs-target="#viewImageModal"
      class="d-flex align-items-center justify-content-between w-100 btn-nothing"
      style="border: 1px solid var(--Silver-100, #EDEDED); border-radius: 12px; padding: 12px 24px;">
      <span class="gap-2 d-flex align-items-center">
      <span style="background-color: #F1F9FA; border-radius: 8px; padding: 12px;">
      <x-svg-icon name="document" size="24" color="Black" />
      </span>
      <span class="d-flex flex-column">
      <span class="h5-semibold" style="color:#000;">Image</span>
      <span class="h5-ragular" style="color:#ADADAD;">image</span>
      </span>
      </span>
      <span> <x-svg-icon name="expand" size="24" color="Black" /></span>
    </x-button>
    <x-modal id="viewImageModal" title="Image Preview" :image="$media->image_path" />
    @endif
    </div>

    <!-- Reviwer`s Rating (Read Only) -->
    <div class="mt-4">
      <h3 class="mb-2 h4-semibold">Your Rating : ( 1 - 10 )</h3>
      <div class="gap-3 d-flex align-items-center w-100">
        <span class="h4-semibold" style="color:#35758C;">{{ $myRate ?? 'No rating given' }}</span>
      </div>
    </div>

    <!-- Comments (Read Only) -->
    <div class="mt-4">
    <h3 class="mb-2 h4-semibold">Reviewer Comment</h3>
    @foreach($commentsData as $comment)
      <div class="comment-container">
        <div class="gap-3 d-flex align-items-start">
          <div class="comment-container-user-icon">
            <x-svg-icon name="user" size="18" color="#35758c" />
          </div>
          <div class="w-100">
            <h4 class="h5-semibold">{{ $comment->user->name ?? 'Unknown User' }}</h4>
            <span class="h6-ragular" style="color:#ADADAD;">Commented On {{ $comment->created_at->diffForHumans() }}</span>
            <p class="mt-2 h6-ragular">{{ $comment->content }}</p>
            <div class="w-100 d-flex justify-content-end align-items-end">
              <div class="gap-2 d-flex align-items-center">
                <x-svg-icon name="message" size="16" color="#ADADAD" />
                <span class="h6-ragular">{{ $replysCount }} Replies</span>
              </div>
            </div>
            @php
              $reviewReplies = $replys->where('parent_id', $comment->id);
            @endphp
            @if($reviewReplies->count() > 0)
              <div class="mt-2 replies-container" style="margin-left: 40px;">
                @foreach($reviewReplies as $reply)
                  <x-review-reply :reply="$reply" :replys="$replys" :media="$media" />
                @endforeach
              </div>
            @endif
          </div>
        </div>
      </div>
    @endforeach
    </div>

  </section>

@endsection
