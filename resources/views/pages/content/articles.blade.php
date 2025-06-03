@extends('layouts.app')
@section('title', 'HIS | Videos')
@section('content')

  <section>

    <div class="d-flex justify-content-between align-items-start">
    <div>
      <h2 class="h2-semibold" style="color:#35758C;">Content Management</h2>
      <p class="h5-ragular" style="color:#ADADAD;">Create, update, or save content as draft—all in one place .</p>
    </div>

    <div class="gap-3 d-flex align-items-center">
      <x-link_btn href="">
      <x-svg-icon name="content" size="18" />
      <span>Add Video</span>
      </x-link_btn>
      <x-link_btn href="">
      <x-svg-icon name="article" size="18" />
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

  </section>

@endsection

@push('scripts')
  <script src="{{ asset('js/filters.js') }}"></script>
@endpush