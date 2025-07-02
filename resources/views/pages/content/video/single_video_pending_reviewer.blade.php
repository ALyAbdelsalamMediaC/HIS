@extends('layouts.app')
@section('title', 'HIS | Video - Pending Review')
@section('content')

  <div class="gap-3 mb-4 d-flex align-items-center">
    <a href="{{ url()->previous() }}" class="arrow-back-btn">
    <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
    </a>
    <div>
    <h2 class="h2-semibold" style="color:#35758C;">Video Details (Pending Review)</h2>
    <p class="h5-ragular" style="color:#ADADAD;">Review the video and leave comments if needed</p>
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
    <div class="mt-3">
    <p class="h5-ragular">{{ $media->description }}</p>
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

    <!-- Admin`s Rating -->
    <div class="mt-4">
      <h3 class="mb-2 h4-semibold">Reviewer Rating : ( 1 - 10 )</h3>
      <form action="{{ route('reviews.rate') }}" method="POST" class="gap-3 d-flex align-items-center w-100">
        @csrf
        <input type="hidden" name="media_id" value="{{ $media->id }}">
        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
        <x-text_input type="number" id="rate" name="rate" placeholder="1 - 10" />
      </form>
    </div>

    <!-- Comments -->
    <div class="mt-4">
    <h3 class="mb-2 h4-semibold">Reviewer Comment</h3>
    <!-- Add Comment Form -->
    <form action="{{ route('reviews.add', ['media_id' => $media->id]) }}" method="POST" class="mb-3">
        @csrf
        <x-comment-input id="comment" name="content" placeholder="Add new comment..." :value="old('content')" />
    </form>

    <!-- Comments List -->
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
              $hasAdminReply = $reviewReplies->contains(function($r) { return isset($r->user) && $r->user->role === 'admin'; });
            @endphp
            @if(auth()->user()->role === 'reviewer' && auth()->id() === $comment->user_id && !$hasAdminReply)
              <div class="d-flex align-items-center mt-2">
                <button class="btn-nothing" data-bs-toggle="modal" data-bs-target="#deleteReviewModal{{ $comment->id }}">
                  <x-svg-icon name="trash" size="20" color="#BB1313" />
                </button>
              </div>
              <!-- Delete Modal for Review -->
              <x-modal id="deleteReviewModal{{ $comment->id }}" title="Delete Review">
                <div class="my-3">
                  <p class="h4-ragular" style="color:#000;">Are you sure you want to delete this review?</p>
                  <p class="h5-ragular" style="color:#ADADAD;">This will also delete all replies to this review.</p>
                </div>
                <div class="modal-footer">
                  <x-button type="button" style="color:#BB1313; background-color:transparent; border:1px solid #BB1313;" data-bs-dismiss="modal">Cancel</x-button>
                  <form action="{{ route('reviews.delete', ['comment_id' => $comment->id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" style="background-color:#BB1313; color:#fff;">Delete</x-button>
                  </form>
                </div>
              </x-modal>
            @endif
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
</script>
@endpush


