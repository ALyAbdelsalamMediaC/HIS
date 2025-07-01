@props([
    'commentsData' => [],
    'mediaId' => null,
    'enableReplies' => true,
    'enableLikes' => true,
    'enableDelete' => true,
    'showAddComment' => true,
    'commentRoute' => 'comments.add',
    'replyRoute' => 'comments.reply',
    'likeAddRoute' => 'comments.like.add',
    'likeRemoveRoute' => 'comments.like.remove',
    'deleteRoute' => 'comments.delete'
])

<!-- Add Comment -->
@if($showAddComment)
<form action="{{ route($commentRoute, ['media_id' => $mediaId]) }}" method="POST" class="mb-3">
    @csrf
    <x-comment-input id="comment" name="content" placeholder="Add new comment..." :value="old('content')" />
    <button type="submit" class="mt-2 btn btn-primary" style="display:none"></button>
</form>
@endif

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
                        @if($enableLikes)
                        <div class="gap-3 mt-3 d-flex align-items-center">
                            <div>
                                @php
                                    $userLikedComment = \App\Models\LikeComment::where('user_id', auth()->id())
                                        ->where('comment_id', $comment->id)
                                        ->exists();
                                    $commentLikesCount = \App\Models\LikeComment::where('comment_id', $comment->id)->count();
                                @endphp
                                @if($userLikedComment)
                                    <form action="{{ route($likeRemoveRoute, ['commentId' => $comment->id]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-nothing" title="Unlike">
                                            <x-svg-icon name="heart-fill" size="16" color="#BB1313" />
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route($likeAddRoute, ['commentId' => $comment->id]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn-nothing" title="Like">
                                            <x-svg-icon name="heart-empty" size="16" color="#ADADAD" />
                                        </button>
                                    </form>
                                @endif
                                <span class="h6-ragular">{{ $commentLikesCount }} Likes</span>
                            </div>
                            @if($enableReplies)
                            <div>
                                <x-svg-icon name="message" size="16" color="#ADADAD" />
                                <span class="h6-ragular">{{ $comment->replies->count() }} Replies</span>
                            </div>
                            @endif
                        </div>
                        @endif
                        <div class="gap-2 mt-3 d-flex align-items-center">
                            @if($enableReplies)
                            <button class="btn-nothing reply-btn" data-comment-id="{{ $comment->id }}">
                                <x-svg-icon name="replay" size="20" color="#ADADAD" />
                            </button>
                            @endif
                            @if($enableDelete && (auth()->user()->role === 'admin' || (auth()->user()->role === 'reviewer' && auth()->id() === $comment->user_id)))
                                <button class="btn-nothing" data-bs-toggle="modal" data-bs-target="#deleteCommentModal{{ $comment->id }}">
                                    <x-svg-icon name="trash" size="20" color="#BB1313" />
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reply input, hidden by default -->
            @if($enableReplies)
            <div class="reply-input-container" id="reply-container-{{ $comment->id }}" style="display:none; margin-top:16px;">
                <form action="{{ route($replyRoute, ['media_id' => $mediaId, 'parent_id' => $comment->id]) }}" method="POST" class="mb-2">
                    @csrf
                    <x-comment-input id="reply-comment-{{ $comment->id }}" name="content" placeholder="Reply to this comment..." :value="old('content')" />
                    @error('content')
                        <div class="mt-1 text-danger">{{ $message }}</div>
                    @enderror
                    <button type="submit" class="mt-2 btn btn-primary" style="display:none;"></button>
                </form>
            </div>
            @endif

            <!-- Show replies -->
            @if($enableReplies && $comment->replies->count() > 0)
                <div class="replies-container" style="margin-left: 40px; margin-top: 16px;">
                    @foreach($comment->replies as $reply)
                        <div class="comment-container" style="border: 1px solid #EDEDED; padding-left: 16px;">
                            <div class="gap-3 d-flex align-items-start">
                                <div class="comment-container-user-icon">
                                    <x-svg-icon name="user" size="18" color="#35758c" />
                                </div>
                                <div class="w-100">
                                    <h4 class="h5-semibold">{{ $reply->user->name ?? 'Unknown User' }}</h4>
                                    <span class="h6-ragular" style="color:#ADADAD;">Replied On {{ $reply->created_at->diffForHumans() }}</span>
                                    <p class="mt-2 h6-ragular">{{ $reply->content }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        @if($enableLikes)
                                        <div class="gap-3 mt-3 d-flex align-items-center">
                                            <div>
                                                @php
                                                    $userLikedReply = \App\Models\LikeComment::where('user_id', auth()->id())
                                                        ->where('comment_id', $reply->id)
                                                        ->exists();
                                                    $replyLikesCount = \App\Models\LikeComment::where('comment_id', $reply->id)->count();
                                                @endphp
                                                @if($userLikedReply)
                                                    <form action="{{ route($likeRemoveRoute, ['commentId' => $reply->id]) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-nothing" title="Unlike">
                                                            <x-svg-icon name="heart-fill" size="16" color="#BB1313" />
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route($likeAddRoute, ['commentId' => $reply->id]) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="btn-nothing" title="Like">
                                                            <x-svg-icon name="heart-empty" size="16" color="#ADADAD" />
                                                        </button>
                                                    </form>
                                                @endif
                                                <span class="h6-ragular">{{ $replyLikesCount }} Likes</span>
                                            </div>
                                        </div>
                                        @endif
                                        @if($enableDelete && (auth()->user()->role === 'admin' || (auth()->user()->role === 'reviewer' && auth()->id() === $reply->user_id)))
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
                        @if($enableDelete && (auth()->user()->role === 'admin' || (auth()->user()->role === 'reviewer' && auth()->id() === $reply->user_id)))
                        <x-modal id="deleteCommentModal{{ $reply->id }}" title="Delete Reply">
                            <div class="my-3">
                                <p class="h4-ragular" style="color:#000;">Are you sure you want to delete this reply?</p>
                            </div>
                            <div class="modal-footer">
                                <x-button type="button" style="color:#BB1313; background-color:transparent; border:1px solid #BB1313;" data-bs-dismiss="modal">Cancel</x-button>
                                <form action="{{ route($deleteRoute, ['comment_id' => $reply->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" style="background-color:#BB1313; color:#fff;">Delete</x-button>
                                </form>
                            </div>
                        </x-modal>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Delete Modal for Comment -->
        @if($enableDelete && (auth()->user()->role === 'admin' || (auth()->user()->role === 'reviewer' && auth()->id() === $comment->user_id)))
        <x-modal id="deleteCommentModal{{ $comment->id }}" title="Delete Comment">
            <div class="my-3">
                <p class="h4-ragular" style="color:#000;">Are you sure you want to delete this comment?</p>
                @if($enableReplies)
                <p class="h5-ragular" style="color:#ADADAD;">This will also delete all replies to this comment.</p>
                @endif
            </div>
            <div class="modal-footer">
                <x-button type="button" style="color:#BB1313; background-color:transparent; border:1px solid #BB1313;" data-bs-dismiss="modal">Cancel</x-button>
                <form action="{{ route($deleteRoute, ['comment_id' => $comment->id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" style="background-color:#BB1313; color:#fff;">Delete</x-button>
                </form>
            </div>
        </x-modal>
        @endif
    @empty
        <div class="comment-container">
            <p class="h6-ragular">No comments yet.</p>
        </div>
    @endforelse
</div>

@if($enableReplies)
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
@endif
