@props(['reply', 'replys', 'media'])

@php
  $isReviewerOwner = auth()->user()->role === 'reviewer' && auth()->id() === $reply->user_id;
  $isAdmin = isset($reply->user) && $reply->user->role === 'admin';
  $children = $replys->where('parent_id', $reply->id);
  $hasAdminChildReply = $children->contains(function($r) { return isset($r->user) && $r->user->role === 'admin'; });
  $isAdminUser = auth()->user()->role === 'admin';
@endphp

<div class="mb-2 comment-container" style="border: 1px solid #EDEDED; padding-left: 16px;">
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
        <button class="btn-nothing reply-btn" data-reply-id="{{ $reply->id }}">
          <x-svg-icon name="replay" size="20" color="#ADADAD" />
        </button>
      @endif
      @if(auth()->user()->role === 'reviewer')
        <button class="btn-nothing reply-btn" data-reply-id="{{ $reply->id }}">
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
        <form action="{{ route('reviews.delete', ['comment_id' => $reply->id]) }}" method="POST">
          @csrf
          @method('DELETE')
          <x-button type="submit" style="background-color:#BB1313; color:#fff;">Delete</x-button>
        </form>
      </div>
    </x-modal>
  @endif
  @if(auth()->user()->role === 'admin')
    <div class="reply-input-container" id="reply-container-{{ $reply->id }}" style="display:none; margin-top:16px;">
      <form action="{{ route('reviews.reply', ['media_id' => $media->id, 'parent_id' => $reply->id]) }}" method="POST" class="mb-2">
        @csrf
        <x-comment-input id="reply-comment-{{ $reply->id }}" name="content" placeholder="Reply to this comment..." :value="old('content')" />
        @error('content')
          <div class="mt-1 text-danger">{{ $message }}</div>
        @enderror
        <button type="submit" class="mt-2 btn btn-primary" style="display:none;"></button>
      </form>
    </div>
  @endif
  @if(auth()->user()->role === 'reviewer')
    <div class="reply-input-container" id="reply-container-{{ $reply->id }}" style="display:none; margin-top:16px;">
      <form action="{{ route('reviews.reply', ['media_id' => $media->id, 'parent_id' => $reply->id]) }}" method="POST" class="mb-2">
        @csrf
        <x-comment-input id="reply-comment-{{ $reply->id }}" name="content" placeholder="Reply to this comment..." :value="old('content')" />
        @error('content')
          <div class="mt-1 text-danger">{{ $message }}</div>
        @enderror
        <button type="submit" class="mt-2 btn btn-primary" style="display:none;"></button>
      </form>
    </div>
  @endif
  @if($children->count() > 0)
    <div class="mt-2 replies-container" style="margin-left: 40px;">
      @foreach($children as $child)
        <x-review-reply :reply="$child" :replys="$replys" :media="$media" />
      @endforeach
    </div>
  @endif
</div> 