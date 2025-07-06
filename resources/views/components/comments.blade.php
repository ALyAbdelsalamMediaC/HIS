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
            <div class="justify-between d-flex align-items-start w-100">
                <div class="gap-3 w-100 d-flex align-items-start">
                <div class="comment-container-user-icon">
                    <x-svg-icon name="user" size="18" color="#35758c" />
                </div>
                <div class="w-100">
                    <h4 class="h5-semibold">{{ $comment->user->name ?? 'Unknown User' }}</h4>
                    <span class="h6-ragular" style="color:#ADADAD;">Commented On    {{ $comment->created_at ? $comment->created_at->diffForHumans() : '' }}</span>
                    
                    <!-- Comment content with Read More functionality -->
                    <div class="mt-2 comment-content-wrapper" id="comment-content-{{ $comment->id }}">
                        <div class="h6-ragular comment-text" id="comment-text-{{ $comment->id }}" style="white-space: pre-wrap;">{!! nl2br(e($comment->content)) !!}</div>
                        <button class="btn-nothing read-more-btn" id="read-more-{{ $comment->id }}" style="display:none; color: var(--primary-color); font-weight: 500; padding: 0; margin-top: 4px;">
                            Read more
                        </button>
                        <button class="btn-nothing read-less-btn" id="read-less-{{ $comment->id }}" style="display:none; color: var(--primary-color); font-weight: 500; padding: 0; margin-top: 4px;">
                            Show less
                        </button>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        @if($enableLikes)
                        <div class="gap-3 mt-3 d-flex align-items-center">
                            <div>
                                @php
                                    // Check if this is an article comment or video comment based on the route
                                    $isArticleComment = str_contains($commentRoute, 'article');
                                    $likeModel = $isArticleComment ? '\App\Models\LikeCommentArticle' : '\App\Models\LikeComment';
                                    
                                    $userLikedComment = $likeModel::where('user_id', auth()->id())
                                        ->where('comment_id', $comment->id)
                                        ->exists();
                                    $commentLikesCount = $likeModel::where('comment_id', $comment->id)->count();
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
                       
                    </div>
                </div>
                </div>

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
                        <div class="justify-between d-flex align-items-start w-100">
                        <div class="gap-3 w-100 d-flex align-items-start">
                                <div class="comment-container-user-icon">
                                    <x-svg-icon name="user" size="18" color="#35758c" />
                                </div>
                                <div class="w-100">
                                    <h4 class="h5-semibold">{{ $reply->user->name ?? 'Unknown User' }}</h4>
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
                                                    $isArticleComment = str_contains($commentRoute, 'article');
                                                    $likeModel = $isArticleComment ? '\App\Models\LikeCommentArticle' : '\App\Models\LikeComment';
                                                    
                                                    $userLikedReply = $likeModel::where('user_id', auth()->id())
                                                        ->where('comment_id', $reply->id)
                                                        ->exists();
                                                    $replyLikesCount = $likeModel::where('comment_id', $reply->id)->count();
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

                                    </div>
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

@push('scripts')
<script>
    // Reply button functionality
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

    // Read More functionality for comments and replies
    function initializeReadMore() {
        const maxHeight = 60; // Maximum height in pixels before showing "Read more"
        
        // Handle comments and replies
        document.querySelectorAll('.comment-text').forEach(function(textElement) {
            const contentWrapper = textElement.closest('.comment-content-wrapper');
            const readMoreBtn = contentWrapper.querySelector('.read-more-btn');
            const readLessBtn = contentWrapper.querySelector('.read-less-btn');
            
            // Remove existing event listeners to prevent duplicates
            if (readMoreBtn) {
                readMoreBtn.replaceWith(readMoreBtn.cloneNode(true));
            }
            if (readLessBtn) {
                readLessBtn.replaceWith(readLessBtn.cloneNode(true));
            }
            
            // Get fresh references after cloning
            const newReadMoreBtn = contentWrapper.querySelector('.read-more-btn');
            const newReadLessBtn = contentWrapper.querySelector('.read-less-btn');
            
            if (textElement.scrollHeight > maxHeight) {
                // Content is longer than max height, show "Read more" button
                textElement.style.maxHeight = maxHeight + 'px';
                textElement.style.overflow = 'hidden';
                textElement.classList.add('collapsed');
                newReadMoreBtn.style.display = 'inline-block';
                
                // Read More button click
                newReadMoreBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    textElement.style.maxHeight = 'none';
                    textElement.style.overflow = 'visible';
                    textElement.classList.remove('collapsed');
                    newReadMoreBtn.style.display = 'none';
                    newReadLessBtn.style.display = 'inline-block';
                });
                
                // Read Less button click
                newReadLessBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    textElement.style.maxHeight = maxHeight + 'px';
                    textElement.style.overflow = 'hidden';
                    textElement.classList.add('collapsed');
                    newReadLessBtn.style.display = 'none';
                    newReadMoreBtn.style.display = 'inline-block';
                });
            } else {
                // Content is short enough, hide both buttons
                newReadMoreBtn.style.display = 'none';
                newReadLessBtn.style.display = 'none';
            }
        });
    }

    // Initialize read more functionality when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        initializeReadMore();
    });

    // Re-initialize when new content is loaded (for dynamic content)
    function reinitializeReadMore() {
        setTimeout(initializeReadMore, 100);
    }

    // Export function for external use
    window.reinitializeReadMore = reinitializeReadMore;

    // Also initialize when the page is fully loaded (for images and other content)
    window.addEventListener('load', function() {
        initializeReadMore();
    });

    // Handle dynamic content loading (if comments are loaded via AJAX)
    if (typeof window.MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    // Check if any comment containers were added
                    const hasNewComments = Array.from(mutation.addedNodes).some(function(node) {
                        return node.nodeType === 1 && (
                            node.classList.contains('comment-container') ||
                            node.querySelector('.comment-container')
                        );
                    });
                    
                    if (hasNewComments) {
                        reinitializeReadMore();
                    }
                }
            });
        });

        // Start observing the comments container
        const commentsContainer = document.querySelector('.comments-list-container');
        if (commentsContainer) {
            observer.observe(commentsContainer, {
                childList: true,
                subtree: true
            });
        }
    }
</script>
@endpush
