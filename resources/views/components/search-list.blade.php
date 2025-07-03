@props(['articles' => collect([]), 'videos' => collect([])])

<div>
  @if($articles->isEmpty() && $videos->isEmpty())
    <div class="search-item text-muted">
      No results found
    </div>
  @endif

  @foreach($videos as $video)
    <div class="mb-2 search-item d-flex align-items-center">
      @if($video->thumbnail)
        <img src="{{ $video->thumbnail }}" alt="Thumbnail" class="me-2" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
      @else
        <x-svg-icon name="video" size="40" color="#ADADAD" class="me-2" />
      @endif
      <div class="flex-grow-1">
        <a href="{{ route('content.video', ['id' => $video->id, 'status' => $video->status ?? 'published']) }}" class="fw-bold text-decoration-none">{{ $video->title }}</a>
        <div class="small text-muted">{{ $video->created_at ? $video->created_at->format('Y-m-d') : '' }} | Status: {{ $video->status ?? 'published' }}</div>
      </div>
    </div>
  @endforeach

  @foreach($articles as $article)
    <div class="mb-2 search-item d-flex align-items-center">
      @if($article->thumbnail)
        <img src="{{ $article->thumbnail }}" alt="Thumbnail" class="me-2" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
      @else
        <x-svg-icon name="document" size="40" color="#ADADAD" class="me-2" />
      @endif
      <div class="flex-grow-1">
        <a href="{{ route('content.article', ['id' => $article->id]) }}" class="fw-bold text-decoration-none">{{ $article->title }}</a>
        <div class="small text-muted">{{ $article->created_at ? $article->created_at->format('Y-m-d') : '' }}</div>
      </div>
    </div>
  @endforeach
</div>