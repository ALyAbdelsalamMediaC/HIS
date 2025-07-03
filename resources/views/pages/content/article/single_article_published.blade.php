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
    <!-- Article Title -->
    <div class="gap-3 mt-3 d-flex align-items-center">
      <h2 class="h3-semibold">{{ $article->title }}</h2>
      <h4 class="h6-ragular card-status">
        {{ ucfirst($article->status ?? 'published') }}
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
        <div>
        @if($userLiked)
          <form action="{{ route('article.like.remove', ['mediaId' => $article->id]) }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-nothing" title="Unlike">
              <x-svg-icon name="like-fill" size="24" color="Black" />
            </button>
          </form>
        @else
          <form action="{{ route('article.like.add', ['mediaId' => $article->id]) }}" method="POST" style="display:inline;">
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

    <!-- Article Description -->
    <div class="mt-3 single-discription">
      <div class="h5-ragular quill-content">{!! $article->description !!}</div>
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
      <x-comments 
        :commentsData="$CommentArticle" 
        :mediaId="$article->id"
        :enableReplies="true"
        :enableLikes="true"
        :enableDelete="true"
        :showAddComment="true"
        commentRoute="article.comments.add"
        replyRoute="article.comments.reply"
        likeAddRoute="article.comments.like.add"
        likeRemoveRoute="article.comments.like.remove"
        deleteRoute="article.comments.delete"
      />
    </div>

  </section>

@endsection

@push('scripts')
<script src="{{ asset('js/validations.js') }}"></script>
@endpush
