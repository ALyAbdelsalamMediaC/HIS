@props([
    'reply',
    'enableLikes' => true,
    'enableDelete' => true,
    'likeAddRoute' => 'comments.like.add',
    'likeRemoveRoute' => 'comments.like.remove',
    'deleteRoute' => 'comments.delete',
])

<div class="comment-container" id="reply-{{ $reply->id }}" style="border: 1px solid #EDEDED; padding-left: 16px;">
    <div class="justify-between d-flex align-items-start w-100">
    <div class="gap-3 w-100 d-flex align-items-start">
            <div class="comment-container-user-icon">
                <x-svg-icon name="user" size="18" color="#35758c" />
            </div>
            <div class="w-100">
                <h4 class="h5-semibold">{{ optional($reply->user)->name ?? 'Unknown User' }}</h4>
                <span class="h6-ragular" style="color:#ADADAD;">Replied On {{ $reply->created_at->diffForHumans() }}</span>
                
                <!-- Reply content with Read More functionality -->
                <div class="mt-2 comment-content-wrapper" id="reply-content-{{ $reply->id }}">
                    <div class="h6-ragular comment-text" id="reply-text-{{ $reply->id }}" style="white-space: pre-wrap;">{!! nl2br(e($reply->content)) !!}</div>
                    <button class="btn-nothing read-more-btn" id="read-more-reply-{{ $reply->id }}" style="display:none; color: var(--primary-color); font-weight: 500; padding: 0; margin-top: 4px;">
                        Read more
                    </button>
                    <button class="btn-nothing read-less-btn" id="read-less-reply-{{ $reply->id }}" style="display:none; color: var(--primary-color); font-weight: 500; padding: 0; margin-top: 4px;">
                        Show less
                    </button>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    @if($enableLikes)
                    <div class="gap-3 mt-3 d-flex align-items-center">
                        <div>
                            @php
                                // Check if this is an article comment or video comment based on the route
                                $isArticleComment = str_contains($likeAddRoute, 'article');
                                $likeModel = $isArticleComment ? '\App\Models\LikeCommentArticle' : '\App\Models\LikeComment';
                                
                                $userLikedReply = auth()->check() ? $likeModel::where('user_id', auth()->id())
                                    ->where('comment_id', $reply->id)
                                    ->exists() : false;
                                $replyLikesCount = $likeModel::where('comment_id', $reply->id)->count();
                            @endphp
                            <button class="btn-nothing" data-action="like-comment" data-comment-id="{{ $reply->id }}" data-liked="{{ $userLikedReply ? 'true' : 'false' }}" title="{{ $userLikedReply ? 'Unlike' : 'Like' }}">
                                <span class="like-icon like-fill" style="{{ $userLikedReply ? '' : 'display:none;' }}">
                                    <x-svg-icon name="heart-fill" size="16" color="#BB1313" />
                                </span>
                                <span class="like-icon like-empty" style="{{ $userLikedReply ? 'display:none;' : '' }}">
                                    <x-svg-icon name="heart-empty" size="16" color="#ADADAD" />
                                </span>
                            </button>
                            <span class="h6-ragular likes-count">{{ $replyLikesCount }} Likes</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            </div>
            @if($enableDelete && auth()->check() && (auth()->user()->role === 'admin' || (auth()->user()->role === 'reviewer' && auth()->id() === $reply->user_id)))
                <div class="gap-2 mt-3 d-flex align-items-center">
                    <button class="btn-nothing" data-bs-toggle="modal" data-bs-target="#deleteCommentModal{{ $reply->id }}">
                        <x-svg-icon name="trash" size="20" color="#BB1313" />
                    </button>
                </div>
            @endif
    </div>
</div>

<!-- Delete Modal for Reply -->
@if($enableDelete && auth()->check() && (auth()->user()->role === 'admin' || (auth()->user()->role === 'reviewer' && auth()->id() === $reply->user_id)))
    <x-modal id="deleteCommentModal{{ $reply->id }}" title="Delete Reply">
        <div class="my-3">
            <p class="h4-ragular" style="color:#000;">Are you sure you want to delete this reply?</p>
        </div>
        <div class="modal-footer">
            <x-button type="button" style="color:#BB1313; background-color:transparent; border:1px solid #BB1313;" data-bs-dismiss="modal">Cancel</x-button>
            <x-button type="button" class="delete-comment-confirm-btn" data-comment-id="{{ $reply->id }}" style="background-color:#BB1313; color:#fff;">Delete</x-button>
        </div>
    </x-modal>
@endif 