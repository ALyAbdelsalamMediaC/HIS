@extends('layouts.app')
@section('title', 'HIS | Video - Reviewer Review')
@section('content')

  <div class="gap-3 mb-4 d-flex align-items-center">
    <a href="{{ url()->previous() }}" class="arrow-back-btn">
    <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
    </a>
    <div>
    <h2 class="h2-semibold" style="color:#35758C;">Video Reviewer Review</h2>
    <p class="h5-ragular" style="color:#ADADAD;">Review the video and leave comments if needed</p>
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

    <!-- Reviwer`s Rating -->
    <div class="mt-4">
      <h3 class="mb-2 h4-semibold">Reviewer Rating : ( 1 - 10 )</h3>
      <form id="reviewer-rate-form" action="{{ route('reviews.rate') }}" method="POST" class="gap-3 d-flex align-items-center w-100">
        @csrf
        <input type="hidden" name="media_id" value="{{ $media->id }}">
        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
        <div class="input-icon w-100">
          <x-text_input type="number" id="rate" name="rate" placeholder="1 - 10" :value="$myRate" />
          <div class="input-icon-send" style="cursor:pointer" onclick="document.getElementById('reviewer-rate-form').dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}));">
            <x-svg-icon name="send" size="14" color="#fff" />
          </div>
        </div>
      </form>
    </div>

    <!-- Comments -->
    <div class="mt-4">
    <h3 class="mb-2 h4-semibold">Reviewer Comment</h3>
    <!-- Add Comment Form -->
    <div class="mb-3 input-icon review-reply-input-wrapper" data-media-id="{{ $media->id }}" data-action="add-review">
        <x-textarea
            id="review-reply-comment-main"
            name="content"
            placeholder="Add new comment..."
            rows="1"
            class="review-reply-textarea"
            style="background-color: transparent; border-radius: 38px; min-height: 60px; max-height: 120px; resize: none; width: 100%; padding-right: 40px;"
        />
        <div class="input-icon-send review-reply-submit-btn" style="cursor:pointer; position: absolute; right: 20px; top: 50%; transform: translateY(-50%);" title="Submit">
            <x-svg-icon name="send" size="14" color="#fff" />
        </div>
    </div>

    <!-- Comments List -->
    <div class="reviews-list-container" data-ajax-review='@json(["addReviewEndpoint" => "/reviews/add", "getReviewHtmlEndpoint" => "/reviews"])'>
    @foreach($commentsData as $comment)
      <x-review-item :comment="$comment" :replys="$replys" :media="$media" />
    @endforeach
    </div>

  </section>

@endsection

@push('scripts')
<script src="{{ asset('js/apis/ajaxReview.js') }}"></script>
<script src="{{ asset('js/descriptonReadMore.js') }}"></script>
<script src="{{ asset('/js/apis/ajaxRate.js') }}"></script>
<script>
  document.querySelectorAll('.reply-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var replyId = this.getAttribute('data-reply-id');
      var replyInput = document.getElementById('reply-container-' + replyId);
      if (replyInput.style.display === 'none' || replyInput.style.display === '') {
        replyInput.style.display = 'block';
        var inputField = replyInput.querySelector('input, textarea');
        if (inputField) inputField.focus();
      } else {
        replyInput.style.display = 'none';
      }
    });
  });
</script>

<script type="module">
import { submitReviewerRate } from '/js/apis/ajaxRate.js';

function showToast(message, type = 'success') {
    if (window.showToast) {
        window.showToast(message, type);
    } else {
        alert(message);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const rateForm = document.getElementById('reviewer-rate-form');
    if (rateForm) {
        rateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const mediaId = rateForm.querySelector('input[name="media_id"]').value;
            const userId = rateForm.querySelector('input[name="user_id"]').value;
            const rate = rateForm.querySelector('input[name="rate"]').value;
            const csrfToken = rateForm.querySelector('input[name="_token"]').value;
            submitReviewerRate(mediaId, userId, rate, csrfToken).then(data => {
                if (data.success) {
                    showToast(data.message || 'Rating submitted successfully.', 'success');
                    // Update the input value to the new rate
                    rateForm.querySelector('input[name="rate"]').value = rate;
                } else {
                    showToast(data.message || 'Failed to submit rating.', 'danger');
                }
            }).catch(() => showToast('Error submitting rating.', 'danger'));
        });
    }
});
</script>
@endpush



