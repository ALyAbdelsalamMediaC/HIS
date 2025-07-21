@extends('layouts.app')
@section('title', 'HIS | Article - Published')
@section('content')

  <div class="gap-3 mb-4 d-flex align-items-center">
    <a href="{{ url()->previous() }}" class="arrow-back-btn">
    <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
    </a>
    <div>
    <h2 class="h2-semibold" style="color:#35758C;">Article Details</h2>
    <p class="h5-ragular" style="color:#ADADAD;">View article information and details</p>
    </div>
  </div>

  <section class="single-video-container">
    <!-- Thumbnail -->
    @if(!empty($article->thumbnail_path) && $article->thumbnail_path !== 'null')
    <div class="article-thumbnail">
      <img src="{{ $article->thumbnail_path }}" alt="{{ $article->title }}">
    </div>
    @endif

    <!-- Article Title -->
    <div class="gap-3 mt-3 d-flex align-items-center">
      <h2 class="h3-semibold">{{ $article->title }}</h2>
      <h4 class="h6-ragular card-status published">
      Published
      </h4>
    </div>

    <!-- Article Details -->
    <div class="d-flex justify-content-between align-items-center">
      <div class="gap-3 mt-2 d-flex align-items-center">
        <span class="h5-ragular" style="color:#ADADAD;">Uploaded {{ $article->created_at ? $article->created_at->diffForHumans() : '' }}</span>
        <span class="h5-ragular" style="color:#ADADAD;">by {{ $article->user->name ?? 'Unknown' }}</span>
      </div>
      <div class="gap-4 d-flex align-items-center">
        <div>
          <x-svg-icon name="eye" size="24" color="Black" />
          <span class="h6-ragular">{{ $article->views ?? 0 }} viewers</span>
        </div>
        <div class="gap-1 d-flex align-items-center">
        <form id="like-form" data-liked="{{ $userLiked ? '1' : '0' }}" data-media-id="{{ $article->id }}">
          @csrf
          <button type="submit" class="btn-nothing" title="{{ $userLiked ? 'Unlike' : 'Like' }}" id="like-btn">
            <span class="like-icon like-empty" style="{{ $userLiked ? 'display:none;' : '' }}">
              <x-svg-icon name="like-empty" size="24" color="Black" />
            </span>
            <span class="like-icon like-fill" style="{{ $userLiked ? '' : 'display:none;' }}">
              <x-svg-icon name="like-fill" size="24" color="Black" />
            </span>
          </button>
        </form>
        <span class="h6-ragular" id="like-count">{{$likesCount}} Likes</span>
      </div>
      </div>
    </div>

    <!-- Article Description -->
    <div class="mt-3 single-discription">
        <div class="h5-ragular description-content-wrapper quill-content" id="description-content-{{ $article->id }}">
            <div class="description-text" id="description-text-{{ $article->id }}" style="white-space: pre-wrap;">{!! $article->description !!}</div>
            <button class="btn-nothing read-more-btn" id="read-more-desc-{{ $article->id }}" style="display:none; color: var(--primary-color); font-weight: 500; padding: 0; margin-top: 8px;">
                Read more
            </button>
            <button class="btn-nothing read-less-btn" id="read-less-desc-{{ $article->id }}" style="display:none; color: var(--primary-color); font-weight: 500; padding: 0; margin-top: 8px;">
                Show less
            </button>
        </div>
    </div>

    <!-- Article Mentions  -->
    <div class="gap-4 mt-3 d-flex align-items-center">
      <h3 class="h5-semibold">Mentioned to :</h3>
      <div class="flex-wrap gap-3 d-flex align-items-center">
        @if($article->mention && is_array(json_decode($article->mention, true)))
          @foreach(json_decode($article->mention, true) as $mentionedUser)
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

    <!-- Article Assets -->
    <div class="mt-4 w-100">
      <!-- PDF  -->
      @if($article->pdf)
        <a href="{{ $article->pdf }}" target="_blank" class="d-flex align-items-center justify-content-between w-100" style="border: 1px solid var(--Silver-100, #EDEDED); border-radius: 12px; padding: 12px 24px;">
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
    </div>

    <!-- Comments -->
    <div class="mt-4">
      <h3 class="mb-2 h4-semibold">Comments</h3>
      
      @php
          $ajaxConfig = json_encode([
              'addCommentEndpoint' => '/article-comments/add',
              'addReplyEndpoint' => '/article-comments/reply',
              'deleteCommentEndpoint' => '/article-comments',
              'likeCommentEndpoint' => '/articleComments',
              'unlikeCommentEndpoint' => '/articleComments',
          ]);
      @endphp
      
      <x-comments 
        :commentsData="$CommentArticle" 
        :mediaId="$article->id"
        :enableReplies="true"
        :enableLikes="true"
        :enableDelete="true"
        :showAddComment="true"
        :ajaxConfig="$ajaxConfig"
        commentType="article"
      />
    </div>

  </section>

@endsection

@push('scripts')
<script src="{{ asset('js/descriptonReadMore.js') }}"></script>
<script src="{{ asset('js/showToast.js') }}"></script>
<script type="module">
  import { likeArticle, unlikeArticle } from '/js/apis/article.js';

  document.addEventListener('DOMContentLoaded', function() {
    const likeForm = document.getElementById('like-form');
    const likeBtn = document.getElementById('like-btn');
    if (likeForm) {
        likeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const liked = likeForm.getAttribute('data-liked') === '1';
            const mediaId = likeForm.getAttribute('data-media-id');
            const csrfToken = likeForm.querySelector('input[name="_token"]').value;
            let promise = liked ? unlikeArticle(mediaId, csrfToken) : likeArticle(mediaId, csrfToken);
            promise.then(data => {
                if (data.success) {
                    likeForm.setAttribute('data-liked', data.liked ? '1' : '0');
                    const emptyIcon = likeBtn.querySelector('.like-empty');
                    const fillIcon = likeBtn.querySelector('.like-fill');
                    if (emptyIcon) emptyIcon.style.display = data.liked ? 'none' : '';
                    if (fillIcon) fillIcon.style.display = data.liked ? '' : 'none';
                    const likeCountSpan = document.getElementById('like-count');
                    if (likeCountSpan && typeof data.likesCount !== 'undefined') {
                        likeCountSpan.textContent = `${data.likesCount} Likes`;
                    }
                } else {
                    showToast(data.message || 'Action failed', 'danger');
                }
            }).catch(() => showToast('Error processing like.', 'danger'));
        });
    }
});

</script>
@endpush
