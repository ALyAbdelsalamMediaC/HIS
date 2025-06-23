@extends('layouts.app')
@section('title', 'HIS | Video - Published')
@section('content')

  <div class="gap-3 mb-4 d-flex align-items-center">
    <div class="arrow-back-btn" onclick="window.history.back()">
    <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
    </div>
    <div>
    <h2 class="h2-semibold" style="color:#35758C;">Video Details</h2>
    <p class="h5-ragular" style="color:#ADADAD;">View video information and details</p>
    </div>
  </div>

  <section class="single-video-container">

    <!-- Video -->
    <video controls style="width: 100%; border-radius: 20px; height: 600px;" preload="metadata">
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

    <div class="gap-4 d-flex align-items-center">
      <div>
      <x-svg-icon name="eye" size="24" color="Black" />
      <span class="h6-ragular">{{ $media->views}} viewers</span>
      </div>
      <div>
      <x-svg-icon name="like-empty" size="24" color="Black" />
      <span class="h6-ragular">Liked</span>
      </div>
    </div>
    </div>

    <!-- Video Description -->
    <div class="mt-3">
    <p class="h5-ragular">{{ $media->description }}</p>
    </div>

    <!-- Video Assetes -->
    <div class="gap-3 mt-4 d-flex align-items-center">
    <!-- PDF  -->
    @if($media->pdf)
    <a href="{{ $media->pdf }}" target="_blank" class="d-flex align-items-center justify-content-between w-100" style="border: 1px solid var(--Silver-100, #EDEDED); border-radius: 12px; padding: 12px 24px;

    ">
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

    <!-- Comments -->
    <div class="mt-4">
    <h3 class="mb-2 h4-semibold">Comments</h3>

    <!-- Add Comment -->
    <x-comment-input id="comment" name="comment" placeholder="Add new comment..." />

    <!-- Comments List -->
    <div class="comments-list-container">
      <div class="comment-container">
      <div class="gap-3 d-flex align-items-start">

        <div class="comment-container-user-icon">
        <x-svg-icon name="user" size="18" color="#35758c" />
        </div>

        <div>
        <h4 class="h5-semibold">John Doe</h4>
        <span class="h6-ragular" style="color:#ADADAD;">Commented On 2HOURES</span>
        <p class="mt-2 h6-ragular">Lorem ipsum dolor sit amet consectetur adipisicing elit. Perspiciatis nihil
          inventore
          ipsum necessitatibus
          consectetur et. Enim culpa, accusantium magnam alias molestiae obcaecati sapiente dolore cum, architecto
          dolores expedita nisi nulla.</p>

        <div class="d-flex justify-content-between align-items-center">
          <div class="gap-3 mt-3 d-flex align-items-center">
          <div>
            <x-svg-icon name="heart-empty" size="16" color="#ADADAD" />
            <span class="h6-ragular">0 Likes</span>
          </div>
          <div>
            <x-svg-icon name="message" size="16" color="#ADADAD" />
            <span class="h6-ragular">0 Comments</span>
          </div>
          </div>

          <div class="gap-2 mt-3 d-flex align-items-center">
          <button class="btn-nothing reply-btn">
            <x-svg-icon name="replay" size="20" color="#ADADAD" />
          </button>
          <button class="btn-nothing">
            <x-svg-icon name="trash" size="20" color="#BB1313" />
          </button>
          </div>
        </div>
        </div>
      </div>
      <!-- Reply input, hidden by default -->
      <div class="reply-input-container" style="display:none; margin-top:16px;">
        <x-comment-input id="reply-comment" name="reply-comment" placeholder="Reply to this comment..." />
      </div>
      </div>
    </div>

  </section>

@endsection

@push('scripts')
  <script>
    document.querySelectorAll('.reply-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var replyInput = this.closest('.comment-container').querySelector('.reply-input-container');
      if (replyInput.style.display === 'none' || replyInput.style.display === '') {
      replyInput.style.display = 'block';
      } else {
      replyInput.style.display = 'none';
      }
    });
    });
  </script>
@endpush