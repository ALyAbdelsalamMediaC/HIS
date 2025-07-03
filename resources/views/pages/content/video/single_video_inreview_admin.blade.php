@extends('layouts.app')
@section('title', 'HIS | Video - In review')
@section('content')

  <div class="gap-3 mb-4 d-flex align-items-center">
    <a href="{{ url()->previous() }}" class="arrow-back-btn">
    <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
    </a>
    <div>
    <h2 class="h2-semibold" style="color:#35758C;">In review Video</h2>
    <p class="h5-ragular" style="color:#ADADAD;">Review and manage pending video content</p>
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

    <!-- Video Assets -->
    <div class="gap-3 mt-4 d-flex align-items-center">
    <!-- PDF  -->
    @if($media->pdf)
    <a href="{{ $media->pdf }}" target="_blank" class="d-flex align-items-center justify-content-between w-100"
      style="border: 1px solid var(--Silver-100, #EDEDED); border-radius: 12px; padding: 12px 24px;">
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

    <!-- Assigned -->
    <div class="mt-4">
    <h3 class="h4-semibold">Assigned to : <span class="h4-ragular" style="color:#35758C;">{{ $assignedReviewersCount }} Reviews </span></h3>

    <div class="mt-2 replay-list-container">
      @if($reviewers->isEmpty())
        <div class="comment-container">
          <p class="h6-ragular">No reviews yet.</p>
        </div>
      @endif
      @foreach($reviewers as $review)
      <div class="mb-3 replay-container">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="gap-3 d-flex align-items-center">
              <div class="comment-container-user-icon">
                @if(isset($review->user) && $review->user->profile_image)
                  <img src="{{ $review->user->profile_image }}" class="user-profile-img" style="width:32px;height:32px;border-radius:50%;object-fit:cover;" alt="User Image" />
                @else
                  <x-svg-icon name="user" size="18" color="#35758c" />
                @endif
              </div>
              <div>
                <h4 class="h5-semibold">{{ $review->user->name ?? 'Unknown User' }}</h4>
                <span class="h5-ragular">Rating : {{ $review->rate ?? '-' }} / 10</span>
              </div>
            </div>
            <h4 class="mt-3 h5-semibold">Reviewer Comment :</h4>
            <p class="h6-ragular">{{ $review->content }}</p>
            <div class="gap-3 d-flex align-items-center">
              <span class="mt-2 h6-ragular" style="color:#ADADAD;">Commented On {{ $review->created_at->diffForHumans() }}</span>
              <div>
                  <x-svg-icon name="message" size="16" color="#ADADAD" />
                  <span class="h6-ragular">{{ $replysCount }} Replies</span>
              </div>
              <button class="btn-nothing" data-bs-toggle="modal" data-bs-target="#deleteReviewModal{{ $review->id }}">
            <x-svg-icon name="trash" size="20" color="#BB1313" />
          </button>
            </div>
          </div>
          <button class="btn-nothing toggle-reply-btn" type="button" data-review-id="{{ $review->id }}">
            <span class="arrow-down-icon">
              <x-svg-icon name="arrow-down" size="20" color="#0F1417" />
            </span>
            <span class="arrow-up-icon" style="display:none;">
              <x-svg-icon name="arrow-up" size="20" color="#0F1417" />
            </span>
          </button>
        </div>
        <!-- Delete Modal for Review -->
        <x-modal id="deleteReviewModal{{ $review->id }}" title="Delete Review">
          <div class="my-3">
            <p class="h4-ragular" style="color:#000;">Are you sure you want to delete this review?</p>
            <p class="h5-ragular" style="color:#ADADAD;">This will also delete all replies to this review.</p>
          </div>
          <div class="modal-footer">
            <x-button type="button" style="color:#BB1313; background-color:transparent; border:1px solid #BB1313;" data-bs-dismiss="modal">Cancel</x-button>
            <form action="{{ route('reviews.delete', ['comment_id' => $review->id]) }}" method="POST">
              @csrf
              @method('DELETE')
              <x-button type="submit" style="background-color:#BB1313; color:#fff;">Delete</x-button>
            </form>
          </div>
        </x-modal>
        <!-- Reply input, hidden by default -->
        <div class="mt-3 reply-input-container" id="reply-container-{{ $review->id }}" style="display:none;">
          <form action="{{ route('reviews.reply', ['media_id' => $media->id, 'parent_id' => $review->id]) }}" method="POST" class="mb-2">
            @csrf
            <x-comment-input id="reply-comment-{{ $review->id }}" name="content" placeholder="Reply to this reviewer comment..." :value="old('content')" />
            @error('content')
              <div class="mt-1 text-danger">{{ $message }}</div>
            @enderror
            <button type="submit" class="mt-2 btn btn-primary" style="display:none;"></button>
          </form>

            <!-- Show replies -->
        @php
          $reviewReplies = $replys->where('parent_id', $review->id);
        @endphp
        @if($reviewReplies->count() > 0)
        <div class="mt-2 replies-container" style="margin-left: 40px;">
          @foreach($reviewReplies as $reply)
            <x-review-reply :reply="$reply" :replys="$replys" :media="$media" />
          @endforeach
        </div>
        @endif
      </div>
      @endforeach
        </div>
    </div>

    <!-- Admin`s Rating -->
    <div class="mt-4">
      <h3 class="mb-2 h4-semibold">Admin's Rating : ( 1 - 10 )</h3>
      <form action="{{ route('admins.rate') }}" method="POST" class="gap-3 d-flex align-items-center w-100">
        @csrf
        <input type="hidden" name="media_id" value="{{ $media->id }}">
        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
        <x-text_input type="number" id="rate" name="rate" placeholder="1 - 10" :value="$myRate" />
      </form>
    </div>

    <!-- Admin Comments -->
    <div class="mt-4">
        <h3 class="mb-2 h4-semibold">Admin Comments (Visible to Users)</h3>
        <x-comments 
            :commentsData="$adminComments"
            :mediaId="$media->id"
            :enableReplies="false"
            :enableLikes="false"
            :enableDelete="true"
            :showAddComment="true"
            commentRoute="AdminComment.add"
            deleteRoute="AdminComment.delete"
        />
    </div>
    </div>

    <div class="gap-2 mt-5 d-flex justify-content-end align-items-center">
      <form action="{{ route('media.changeStatus', $media->id) }}" method="POST" style="display:inline;">
        @csrf
        <input type="hidden" name="status" value="declined">
        <x-button class="bg-danger" type="submit">Declined</x-button>
      </form>
      <form action="{{ route('media.changeStatus', $media->id) }}" method="POST" style="display:inline;">
        @csrf
        <input type="hidden" name="status" value="pending">
        <x-button style="color: #e0b610; background-color: #fbfdd0;" type="submit">Previous (Pending)</x-button>
      </form>
      <form action="{{ route('media.changeStatus', $media->id) }}" method="POST" style="display:inline;">
        @csrf
        <input type="hidden" name="status" value="published">
        <x-button style="background-color:#f1f9fa; color: #35758c;" type="submit">Next (Published)</x-button>
      </form>
    </div>
  </section>
@endsection

@push('scripts')
  <script src="{{ asset('js/validations.js') }}"></script>

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

    // Toggle Past Reviewer Comments
    document.querySelectorAll('.toggle-past-reviewer-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var replayContainer = this.closest('.replay-container');
      var pastReviewerSection = null;
      // Find the next sibling with the class 'past-reviewer-section'
      var sibling = replayContainer.nextElementSibling;
      while (sibling) {
      if (sibling.classList && sibling.classList.contains('past-reviewer-section')) {
        pastReviewerSection = sibling;
        break;
      }
      sibling = sibling.nextElementSibling;
      }
      var arrowDown = this.querySelector('.arrow-down-icon');
      var arrowUp = this.querySelector('.arrow-up-icon');
      if (pastReviewerSection) {
      if (pastReviewerSection.style.display === 'none' || pastReviewerSection.style.display === '') {
        pastReviewerSection.style.display = 'block';
        arrowDown.style.display = 'none';
        arrowUp.style.display = '';
      } else {
        pastReviewerSection.style.display = 'none';
        arrowDown.style.display = '';
        arrowUp.style.display = 'none';
      }
      }
    });
    });

    document.querySelectorAll('.toggle-reply-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var reviewId = this.getAttribute('data-review-id');
        var replyInput = document.getElementById('reply-container-' + reviewId);
        var arrowDown = this.querySelector('.arrow-down-icon');
        var arrowUp = this.querySelector('.arrow-up-icon');
        if (replyInput.style.display === 'none' || replyInput.style.display === '') {
          replyInput.style.display = 'block';
          arrowDown.style.display = 'none';
          arrowUp.style.display = '';
          // Focus on the input field
          var inputField = replyInput.querySelector('input, textarea');
          if (inputField) {
            inputField.focus();
          }
        } else {
          replyInput.style.display = 'none';
          arrowDown.style.display = '';
          arrowUp.style.display = 'none';
        }
      });
    });
  </script>
@endpush