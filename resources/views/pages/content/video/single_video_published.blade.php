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

    <div class="gap-4 d-flex align-items-center">
      <div>
      <x-svg-icon name="eye" size="24" color="Black" />
      <span class="h6-ragular">{{ $media->views}} viewers</span>
      </div>
      <div>
        @if($userLiked)
          <form action="{{ route('media.like.remove', ['mediaId' => $media->id]) }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-nothing" title="Unlike">
              <x-svg-icon name="like-fill" size="24" color="Black" />
            </button>
          </form>
        @else
          <form action="{{ route('media.like.add', ['mediaId' => $media->id]) }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn-nothing" title="Like">
              <x-svg-icon name="like-empty" size="24" color="Black" />
            </button>
          </form>
        @endif
        <span class="h6-ragular" id="like-count">{{$likesCount}} Likes</span>
      </div>
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
        <div style="padding: 10px 20px; border-radius: 32px; border: 1px solid #EDEDED;">
          <h3 class="h6-ragular" style="color:#7B7B7B;">@Lorem ipsum</h3>
        </div>
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

    <!-- Add Comment -->
    <form action="{{ route('comments.add', ['media_id' => $media->id]) }}" method="POST" class="mb-3">
      @csrf
      <x-comment-input id="comment" name="content" placeholder="Add new comment..." :value="old('content')" />
      <button type="submit" class="mt-2 btn btn-primary" style="display:none"></button>
    </form>

    <!-- Comments List -->
    <div class="comments-list-container">
      @forelse($commentsData as $comment)
        <div class="comment-container">
          <div class="gap-3 d-flex align-items-start">
            <div class="comment-container-user-icon">
              <x-svg-icon name="user" size="18" color="#35758c" />
            </div>
            <div class="w-100">
              <h4 class="h5-semibold">{{ $comment->user->name ?? 'Unknown User' }}</h4>
              <span class="h6-ragular" style="color:#ADADAD;">Commented On {{ $comment->created_at->diffForHumans() }}</span>
              <p class="mt-2 h6-ragular">{{ $comment->content }}</p>
              <div class="d-flex justify-content-between align-items-center">
                <div class="gap-3 mt-3 d-flex align-items-center">
                  <div>
                    @php
                      $userLikedComment = \App\Models\LikeComment::where('user_id', auth()->id())
                          ->where('comment_id', $comment->id)
                          ->exists();
                      $commentLikesCount = \App\Models\LikeComment::where('comment_id', $comment->id)->count();
                    @endphp
                    @if($userLikedComment)
                      <form action="{{ route('comments.like.remove', ['commentId' => $comment->id]) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-nothing" title="Unlike">
                          <x-svg-icon name="heart-fill" size="16" color="#BB1313" />
                        </button>
                      </form>
                    @else
                      <form action="{{ route('comments.like.add', ['commentId' => $comment->id]) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-nothing" title="Like">
                          <x-svg-icon name="heart-empty" size="16" color="#ADADAD" />
                        </button>
                      </form>
                    @endif
                    <span class="h6-ragular">{{ $commentLikesCount }} Likes</span>
                  </div>
                  <div>
                    <x-svg-icon name="message" size="16" color="#ADADAD" />
                    <span class="h6-ragular">{{ $comment->replies->count() }} Replyes</span>
                  </div>
                </div>
                <div class="gap-2 mt-3 d-flex align-items-center">
                  <button class="btn-nothing reply-btn" data-comment-id="{{ $comment->id }}">
                    <x-svg-icon name="replay" size="20" color="#ADADAD" />
                  </button>
                  @if(auth()->id() === $comment->user_id)
                    <button class="btn-nothing" data-bs-toggle="modal" data-bs-target="#deleteCommentModal{{ $comment->id }}">
                      <x-svg-icon name="trash" size="20" color="#BB1313" />
                    </button>
                  @endif
                </div>
              </div>
            </div>
          </div>
          
          <!-- Reply input, hidden by default -->
          <div class="reply-input-container" id="reply-container-{{ $comment->id }}" style="display:none; margin-top:16px;">
            <form action="{{ route('comments.reply', ['media_id' => $media->id, 'parent_id' => $comment->id]) }}" method="POST" class="mb-2">
              @csrf
              <x-comment-input id="reply-comment-{{ $comment->id }}" name="content" placeholder="Reply to this comment..." :value="old('content')" />
              @error('content')
                <div class="mt-1 text-danger">{{ $message }}</div>
              @enderror
              <button type="submit" class="mt-2 btn btn-primary" style="display:none;"></button>
            </form>
          </div>

          <!-- Show replies -->
          @if($comment->replies->count() > 0)
            <div class="replies-container" style="margin-left: 40px; margin-top: 16px;">
              @foreach($comment->replies as $reply)
                <div class="comment-container" style="border-left: 2px solid #EDEDED; padding-left: 16px;">
                  <div class="gap-3 d-flex align-items-start">
                    <div class="comment-container-user-icon">
                      <x-svg-icon name="user" size="18" color="#35758c" />
                    </div>
                    <div class="w-100">
                      <h4 class="h5-semibold">{{ $reply->user->name ?? 'Unknown User' }}</h4>
                      <span class="h6-ragular" style="color:#ADADAD;">Replied On {{ $reply->created_at->diffForHumans() }}</span>
                      <p class="mt-2 h6-ragular">{{ $reply->content }}</p>
                      <div class="d-flex justify-content-between align-items-center">
                        <div class="gap-3 mt-3 d-flex align-items-center">
                          <div>
                            @php
                              $userLikedReply = \App\Models\LikeComment::where('user_id', auth()->id())
                                  ->where('comment_id', $reply->id)
                                  ->exists();
                              $replyLikesCount = \App\Models\LikeComment::where('comment_id', $reply->id)->count();
                            @endphp
                            @if($userLikedReply)
                              <form action="{{ route('comments.like.remove', ['commentId' => $reply->id]) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-nothing" title="Unlike">
                                  <x-svg-icon name="heart-fill" size="16" color="#BB1313" />
                                </button>
                              </form>
                            @else
                              <form action="{{ route('comments.like.add', ['commentId' => $reply->id]) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-nothing" title="Like">
                                  <x-svg-icon name="heart-empty" size="16" color="#ADADAD" />
                                </button>
                              </form>
                            @endif
                            <span class="h6-ragular">{{ $replyLikesCount }} Likes</span>
                          </div>
                        </div>
                        @if(auth()->id() === $reply->user_id)
                          <div class="gap-2 mt-3 d-flex align-items-center">
                            <button class="btn-nothing" data-bs-toggle="modal" data-bs-target="#deleteCommentModal{{ $reply->id }}">
                              <x-svg-icon name="trash" size="20" color="#BB1313" />
                            </button>
                          </div>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Delete Modal for Reply -->
                <x-modal id="deleteCommentModal{{ $reply->id }}" title="Delete Reply">
                  <div class="my-3">
                    <p class="h4-ragular" style="color:#000;">Are you sure you want to delete this reply?</p>
                  </div>
                  <div class="modal-footer">
                    <x-button type="button" style="color:#BB1313; background-color:transparent; border:1px solid #BB1313;" data-bs-dismiss="modal">Cancel</x-button>
                    <form action="{{ route('comments.delete', ['comment_id' => $reply->id]) }}" method="POST">
                      @csrf
                      @method('DELETE')
                      <x-button type="submit" style="background-color:#BB1313; color:#fff;">Delete</x-button>
                    </form>
                  </div>
                </x-modal>
              @endforeach
            </div>
          @endif
        </div>

        <!-- Delete Modal for Comment -->
        <x-modal id="deleteCommentModal{{ $comment->id }}" title="Delete Comment">
          <div class="my-3">
            <p class="h4-ragular" style="color:#000;">Are you sure you want to delete this comment?</p>
            <p class="h5-ragular" style="color:#ADADAD;">This will also delete all replies to this comment.</p>
          </div>
          <div class="modal-footer">
            <x-button type="button" style="color:#BB1313; background-color:transparent; border:1px solid #BB1313;" data-bs-dismiss="modal">Cancel</x-button>
            <form action="{{ route('comments.delete', ['comment_id' => $comment->id]) }}" method="POST">
              @csrf
              @method('DELETE')
              <x-button type="submit" style="background-color:#BB1313; color:#fff;">Delete</x-button>
            </form>
          </div>
        </x-modal>
      @empty
        <div class="comment-container">
          <p class="h6-ragular">No comments yet.</p>
        </div>
      @endforelse
    </div>

  </section>

@endsection

@push('scripts')
  <script>
    document.querySelectorAll('.reply-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var commentId = this.getAttribute('data-comment-id');
        var replyInput = document.getElementById('reply-container-' + commentId);
        if (replyInput.style.display === 'none' || replyInput.style.display === '') {
          replyInput.style.display = 'block';
          // Focus on the input field
          var inputField = replyInput.querySelector('input, textarea');
          if (inputField) {
            inputField.focus();
          }
        } else {
          replyInput.style.display = 'none';
        }
      });
    });
  </script>
@endpush