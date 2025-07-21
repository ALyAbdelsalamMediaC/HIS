@props([
    'comment',
    'replys' => collect([]),
    'media' => null,
    'enableReplies' => true,
    'enableDelete' => true,
])

@php
    $isReviewerOwner = auth()->user()->role === 'reviewer' && auth()->id() === $comment->user_id;
    $isAdmin = isset($comment->user) && $comment->user->role === 'admin';
    $isAdminUser = auth()->user()->role === 'admin';
    $children = $replys->where('parent_id', $comment->id);
    $hasAdminChildReply = $children->contains(function($r) { return isset($r->user) && $r->user->role === 'admin'; });
@endphp

<div class="comment-container" id="review-{{ $comment->id }}">
    <div class="justify-between d-flex align-items-start w-100">
        <div class="gap-3 w-100 d-flex align-items-start">
            <div class="comment-container-user-icon">
                <x-svg-icon name="user" size="18" color="#35758c" />
            </div>
            <div class="w-100">
                <h4 class="h5-semibold">{{ $comment->user->name ?? 'Unknown User' }}</h4>
                <span class="h6-ragular" style="color:#ADADAD;">Commented On {{ $comment->created_at ? $comment->created_at->diffForHumans() : '' }}</span>
                <div class="mt-2 comment-content-wrapper" id="review-content-{{ $comment->id }}">
                    <div class="h6-ragular comment-text" id="review-text-{{ $comment->id }}" style="white-space: pre-wrap;">{!! nl2br(e($comment->content)) !!}</div>
                    <button class="btn-nothing read-more-btn" id="read-more-review-{{ $comment->id }}" style="display:none; color: var(--primary-color); font-weight: 500; padding: 0; margin-top: 4px;">
                        Read more
                    </button>
                    <button class="btn-nothing read-less-btn" id="read-less-review-{{ $comment->id }}" style="display:none; color: var(--primary-color); font-weight: 500; padding: 0; margin-top: 4px;">
                        Show less
                    </button>
                </div>
            </div>
        </div>
        <div class="gap-2 mt-3 d-flex align-items-center">
            @if($enableReplies)
                <button class="btn-nothing review-reply-btn" data-review-id="{{ $comment->id }}">
                    <x-svg-icon name="replay" size="20" color="#ADADAD" />
                </button>
            @endif
            @if($enableDelete && auth()->check() && ($isAdminUser || ($isReviewerOwner && !$isAdmin && !$hasAdminChildReply)))
                <button class="btn-nothing" data-bs-toggle="modal" data-bs-target="#deleteReviewModal{{ $comment->id }}">
                    <x-svg-icon name="trash" size="20" color="#BB1313" />
                </button>
            @endif
        </div>
    </div>
    @if($enableReplies && (auth()->user()->role === 'admin' || auth()->user()->role === 'reviewer'))
        <div class="mt-3 input-icon reply-input-container review-reply-input-wrapper" id="reply-container-{{ $comment->id }}" data-parent-id="{{ $comment->id }}" data-media-id="{{ $media ? $media->id : '' }}">
            <x-textarea
                id="review-reply-comment-{{ $comment->id }}"
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
    @if($enableDelete && auth()->check() && ($isAdminUser || ($isReviewerOwner && !$isAdmin && !$hasAdminChildReply)))
        <x-modal id="deleteReviewModal{{ $comment->id }}" title="Delete Review">
            <div class="my-3">
                <p class="h4-ragular" style="color:#000;">Are you sure you want to delete this review?</p>
                @if($enableReplies)
                    <p class="h5-ragular" style="color:#ADADAD;">This will also delete all replies to this review.</p>
                @endif
            </div>
            <div class="modal-footer">
                <x-button type="button" style="color:#BB1313; background-color:transparent; border:1px solid #BB1313;" data-bs-dismiss="modal">Cancel</x-button>
                <x-button type="button" class="delete-review-comment-confirm-btn" data-comment-id="{{ $comment->id }}" style="background-color:#BB1313; color:#fff;">Delete</x-button>
            </div>
        </x-modal>
    @endif
    <div class="replies-container" id="review-replies-{{ $comment->id }}" style="margin-left: 40px; margin-top: 16px;">
        @if(isset($replys) && $replys->count() > 0)
            @foreach($replys->where('parent_id', $comment->id) as $reply)
                <x-review-reply :reply="$reply" :replys="$replys" :media="$media" />
            @endforeach
        @endif
    </div>
</div>
