@props(['reply', 'replys', 'media'])

@php
  $isReviewerOwner = auth()->user()->role === 'reviewer' && auth()->id() === $reply->user_id;
  $isAdmin = isset($reply->user) && $reply->user->role === 'admin';
  $children = $replys->where('parent_id', $reply->id);
  $hasAdminChildReply = $children->contains(function($r) { return isset($r->user) && $r->user->role === 'admin'; });
  $isAdminUser = auth()->user()->role === 'admin';
@endphp

<div class="mb-2 comment-container" id="review-reply-{{ $reply->id }}" style="border: 1px solid #EDEDED; padding-left: 16px;">
  <div class="gap-3 d-flex align-items-start">
    <div class="comment-container-user-icon">
      <x-svg-icon name="user" size="18" color="#35758c" />
    </div>
    <div class="w-100">
      <h4 class="h5-semibold">{{ $reply->user->name ?? 'Unknown User' }}</h4>
      <span class="h6-ragular" style="color:#ADADAD;">Replied On {{ $reply->created_at->diffForHumans() }}</span>
      <p class="mt-2 h6-ragular">{{ $reply->content }}</p>
    </div>
    <div class="gap-2 d-flex align-items-center">
      @if(($isReviewerOwner && !$isAdmin && !$hasAdminChildReply) || $isAdminUser)
        <button class="btn-nothing" data-bs-toggle="modal" data-bs-target="#deleteReplyModal{{ $reply->id }}">
          <x-svg-icon name="trash" size="20" color="#BB1313" />
        </button>
      @endif
      @if(auth()->user()->role === 'admin')
        <button class="btn-nothing review-reply-btn" data-reply-id="{{ $reply->id }}">
          <x-svg-icon name="replay" size="20" color="#ADADAD" />
        </button>
      @endif
      @if(auth()->user()->role === 'reviewer')
        <button class="btn-nothing review-reply-btn" data-reply-id="{{ $reply->id }}">
          <x-svg-icon name="replay" size="20" color="#ADADAD" />
        </button>
      @endif
    </div>
  </div>
  <!-- Delete Modal for Reply -->
  @if(($isReviewerOwner && !$isAdmin && !$hasAdminChildReply) || $isAdminUser)
    <x-modal id="deleteReplyModal{{ $reply->id }}" title="Delete Reply">
      <div class="my-3">
        <p class="h4-ragular" style="color:#000;">Are you sure you want to delete this reply?</p>
      </div>
      <div class="modal-footer">
        <x-button type="button" style="color:#BB1313; background-color:transparent; border:1px solid #BB1313;" data-bs-dismiss="modal">Cancel</x-button>
        <x-button type="button" class="delete-review-reply-confirm-btn" data-reply-id="{{ $reply->id }}" style="background-color:#BB1313; color:#fff;">Delete</x-button>
      </div>
    </x-modal>
  @endif
  @if(auth()->user()->role === 'admin' || auth()->user()->role === 'reviewer')
    <div class="mt-3 input-icon reply-input-container review-reply-input-wrapper" id="reply-container-{{ $reply->id }}" data-parent-id="{{ $reply->id }}" data-media-id="{{ $media ? $media->id : '' }}">
      <x-textarea 
        id="review-reply-comment-{{ $reply->id }}" 
        name="content"
        placeholder="Reply to this comment..." 
        rows="1"
        class="review-reply-textarea"
        style="background-color: transparent; border-radius: 38px; min-height: 60px; max-height: 120px; resize: none; width: 100%; padding-right: 40px;"
      />
      <div class="input-icon-send review-reply-submit-btn" style="cursor:pointer; position: absolute; right: 20px; top: 50%; transform: translateY(-50%);" title="Submit">
        <x-svg-icon name="send" size="14" color="#fff" />
      </div>
    </div>
  @endif
  <div class="mt-2 replies-container" id="review-replies-{{ $reply->id }}" style="margin-left: 40px;">
    @if(isset($children) && $children->count() > 0)
      @foreach($children as $child)
        <x-review-reply :reply="$child" :replys="$replys" :media="$media" />
      @endforeach
    @endif
  </div>
</div> 