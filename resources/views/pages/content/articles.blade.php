@extends('layouts.app')
@section('title', 'HIS | Articles')
@section('content')

  <section>
    <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="h2-semibold" style="color:#35758C;">Content Management</h2>
      <p class="h5-ragular" style="color:#ADADAD;">Create, update, or save content as draft—all in one place .</p>
    </div>

    <div class="gap-3 d-flex align-items-center">
      <x-link_btn href="{{ route('content.store') }}">
      <x-svg-icon name="content" size="20" />
      <span>Add Video</span>
      </x-link_btn>
      <x-link_btn href="{{ route('articles.store') }}">
      <x-svg-icon name="article" size="20" />
      <span>Add Article</span>
      </x-link_btn>
    </div>
    </div>

    <!-- Articles & Videos -->
    <div class="mt-4">
    <!-- Tabs using the component -->
    <x-tabs_pages :tabs="[
    ['id' => 'videos', 'label' => 'Videos', 'route' => route('content.videos')],
    ['id' => 'articles', 'label' => 'Articles', 'route' => route('content.articles')],
    ]" activeTab="articles" />
    </div>

    <div class="content-container">
    <div class="filters-container w-100" data-url="{{ route('content.articles') }}">
      <div class="d-flex justify-content-between align-items-center">
      <div class="w-25">
        <x-search_input id="search_input" type="text" name="search" placeholder="Search article title..."
        value="{{ request('search') }}" class="w-100" />
      </div>

      <div class="gap-2 d-flex align-items-center">
        @php
      $filters = [
      'category' => [
      'placeholder' => '-- Select Category --',
      'options' => $categories->mapWithKeys(fn($item) => [$item->name => ucwords(str_replace('_', ' ', $item->name))])->toArray()
      ],
      'status' => [
      'placeholder' => '-- Select status --',
      'options' => [
        'published' => 'Published',
        'pending' => 'Pending',
        'declined' => 'Declined'
      ]
      ],
      ];
    @endphp

        @foreach($filters as $name => $data)
      <x-filter_select name="{{ $name }}" class="form-control-select" :options="$data['options']"
      placeholder="{{ $data['placeholder'] }}" :selected="request($name)">
      </x-filter_select>
      @endforeach

        <x-button id="reset-filters">
        <x-svg-icon name="refresh" size="16" /> Reset
        </x-button>
      </div>
      </div>
    </div>

    <div class="content-container-cards">
      @forelse ($article as $item)
      <div class="content-container-card">
      <div class="d-flex justify-content-between align-items-end w-100">
      <div>
      <h2 class="h4-semibold">{{ $item->user->name  }}</h2>
      <span class="h6-ragular" style="color:#ADADAD;">Published
        {{ $item->created_at->diffForHumans() }}</span>
      </div>
      <div class="d-flex justify-content-between align-items-center">
      <h4 class="h6-ragular card-status {{ $item->status }}">
        {{ ucfirst($item->status) }}
      </h4>
      </div>
      </div>

      <div class="mt-3 content-container-card-img">
      <img src="{{ $item->thumbnail_path}}" alt="{{ $item->title }}">
      <span class="c-v-span">Article</span>
      </div>

      <div class="video-card-content-content">
      <div class="dashboard-video-card-content-content-top">
      <h3 class="h5-semibold" style="margin-top:12px; line-height: 1.5em;">
        {{ $item->title }}
      </h3>
      <p class="h6-ragular">{{ Str::words($item->description, 15, '...') }}</p>

      <div class="gap-2 d-flex align-items-center">
        <x-svg-icon name="link" size="12" color="Black" />
        <a href="{{ $item->hyperlink }}" target="_blank" class="h6-ragular"
        style="color:#2463B6; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block;"
        title="{{ $item->hyperlink }}">
        {{ Str::limit($item->hyperlink, 40) }}
        </a>
      </div>
      </div>

      <div class="dashboard-video-card-content-content-down">
      <div class="gap-2 d-flex align-items-center">
        <a href="{{ route('articles.edit', $item->id) }}">
        <x-svg-icon name="edit-pen2" size="12" color="Black" />
        </a>

        <button class="btn-nothing delete-article-btn" data-bs-toggle="modal"
        data-bs-target="#deleteArticleModal{{ $item->id }}">
        <x-svg-icon name="trash" size="12" color="Black" />
        </button>
      </div>

      <div class="gap-3 d-flex align-items-center">
        <div>
        <x-svg-icon name="eye" size="12" color="Black" />
        <span class="h6-ragular">{{ $item->views }}</span>
        </div>
        <div>
        <x-svg-icon name="message" size="12" color="Black" />
        <span class="h6-ragular">{{ $item->comments }}</span>
        </div>
      </div>
      </div>
      </div>
      </div>
    @empty
      <div class="py-5 text-center" style="grid-column: 1 / -1;">
      <p class="h5-ragular" style="color:#ADADAD;">No articles found</p>
      </div>
    @endforelse
    </div>

    </div>
    <div class="bottom-vid-pagination d-flex justify-content-between align-items-center">
    <!-- Table Info and Pagination -->
    @if($article->count())
    <x-table-info :paginator="$article" />
    <x-pagination :paginator="$article" :appends="request()->query()" />
    @endif
    </div>

    <!-- Delete Article Modals -->
    @foreach($article as $item)
    <x-modal id="deleteArticleModal{{ $item->id }}" title="Delete Article">
    <div class="my-3">
      <p class="h3-semibold" style="color:black;">Are you sure you want to delete the article "{{ $item->title }}"?</p>
    </div>
    <div class="modal-footer">
      <x-button type="button" class="px-4 bg-trans-btn" data-bs-dismiss="modal">Cancel</x-button>
      <form action="{{ route('article.destroy', $item->id) }}" method="POST">
      @csrf
      @method('DELETE')
      <x-button type="submit" class="px-4 btn-danger">Delete</x-button>
      </form>
    </div>
    </x-modal>
    @endforeach
  </section>

@endsection

@push('scripts')
  <script src="{{ asset('js/filters.js') }}"></script>
@endpush