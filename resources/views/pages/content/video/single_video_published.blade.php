@extends('layouts.app')
@section('title', 'HIS | Video - Published')
@section('content')

  <div class="gap-3 mb-4 d-flex align-items-center">
    <a href="{{ url()->previous() }}" class="arrow-back-btn">
    <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
    </a>
    <div>
    <h2 class="h2-semibold" style="color:#35758C;">Video Details</h2>
    <p class="h5-ragular" style="color:#ADADAD;">View video information and details</p>
    </div>
  </div>

  <section class="single-video-container">

    <!-- Video -->
    <div class="video-container">
        <video 
            controls 
            class="video-player"
            preload="none"
            @if($media->thumbnail_path)
                poster="{{ $media->thumbnail_path }}"
            @endif
        >
            <source src="{{ route('content.stream', ['id' => $media->id]) }}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>

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
      <div class="gap-1 d-flex align-items-center">
        <form id="like-form" data-liked="{{ $userLiked ? '1' : '0' }}" data-media-id="{{ $media->id }}">
          @csrf
          <button type="submit" class="btn-nothing" title="{{ $userLiked ? 'Unlike' : 'Like' }}" id="like-btn">
            <span class="like-icon like-empty" style="{{ $userLiked ? 'display:none;' : '' }}">
              <x-svg-icon name="like-empty" size="24" color="Black" />
            </span>
            <span class="like-icon like-fill" style="{{ $userLiked ? '' : 'display:none;' }}">
              <x-svg-icon name="like-fill" size="24" color="Black" />
            </span>
          </button>
        </form>
        <span class="h6-ragular" id="like-count">{{$likesCount}} Likes</span>
      </div>
    </div>
    </div>

    <!-- Video Description -->
    <div class="mt-3 single-discription">
        <div class="h5-ragular description-content-wrapper" id="description-content-{{ $media->id }}">
            <div class="quill-content description-text" id="description-text-{{ $media->id }}" style="white-space: pre-wrap;">{!! $media->description !!}</div>
            <button class="btn-nothing read-more-btn" id="read-more-desc-{{ $media->id }}" style="display:none; color: var(--primary-color); font-weight: 500; padding: 0; margin-top: 8px;">
                Read more
            </button>
            <button class="btn-nothing read-less-btn" id="read-less-desc-{{ $media->id }}" style="display:none; color: var(--primary-color); font-weight: 500; padding: 0; margin-top: 8px;">
                Show less
            </button>
        </div>
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
    
    @php
        $ajaxConfig = json_encode([
            'addCommentEndpoint' => '/comments/add',
            'addReplyEndpoint' => '/comments/reply',
            'deleteCommentEndpoint' => '/comments',
            'likeCommentEndpoint' => '/comments',
            'unlikeCommentEndpoint' => '/comments',
        ]);
    @endphp
    
    <x-comments 
        :commentsData="$commentsData" 
        :mediaId="$media->id"
        :enableReplies="true"
        :enableLikes="true"
        :enableDelete="true"
        :showAddComment="true"
        :ajaxConfig="$ajaxConfig"
    />
    </div>

    <!-- Status Change Button -->
    <div class="gap-2 mt-5 d-flex justify-content-end align-items-center">
      <form action="{{ route('media.changeStatus', $media->id) }}" method="POST" style="display:inline;">
        @csrf
        <input type="hidden" name="status" value="inreview">
        <x-button type="submit">Revert to In Review</x-button>
      </form>
    </div>

  </section>

@endsection

@push('scripts')
<script src="{{ asset('js/descriptonReadMore.js') }}"></script>
<script src="{{ asset('js/showToast.js') }}"></script>
<script type="module">
import { likeVideo, unlikeVideo } from '/js/apis/publishVideoLike.js';

document.addEventListener('DOMContentLoaded', function() {
    const likeForm = document.getElementById('like-form');
    const likeBtn = document.getElementById('like-btn');
    if (likeForm) {
        likeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const liked = likeForm.getAttribute('data-liked') === '1';
            const mediaId = likeForm.getAttribute('data-media-id');
            const csrfToken = likeForm.querySelector('input[name="_token"]').value;
            let promise = liked ? unlikeVideo(mediaId, csrfToken) : likeVideo(mediaId, csrfToken);
            promise.then(data => {
                if (data.success) {
                    likeForm.setAttribute('data-liked', data.liked ? '1' : '0');
                    const emptyIcon = likeBtn.querySelector('.like-empty');
                    const fillIcon = likeBtn.querySelector('.like-fill');
                    if (emptyIcon) emptyIcon.style.display = data.liked ? 'none' : '';
                    if (fillIcon) fillIcon.style.display = data.liked ? '' : 'none';
                    const likeCountSpan = document.getElementById('like-count');
                    if (likeCountSpan && typeof data.likesCount !== 'undefined') {
                        likeCountSpan.textContent = `${data.likesCount} Likes`;
                    }
                } else {
                    showToast(data.message || 'Action failed', 'danger');
                }
            }).catch(() => showToast('Error processing like.', 'danger'));
        });
    }
});
</script>
@endpush
