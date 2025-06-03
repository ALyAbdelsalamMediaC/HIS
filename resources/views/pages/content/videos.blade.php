@extends('layouts.app')
@section('title', 'HIS | Videos')
@section('content')

    <section>

        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h2-semibold" style="color:#35758C;">Content Management</h2>
                <p class="h5-ragular" style="color:#ADADAD;">Create, update, or save content as draft—all in one place .</p>
            </div>

            <div class="gap-3 d-flex align-items-center">
                <x-link_btn href="{{  route('content.store') }}">
                    <x-svg-icon name="content" size="20" />
                    <span>Add Video</span>
                </x-link_btn>
                <x-link_btn href="">
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
        ]" activeTab="videos" />
        </div>

        <div class="content-container">
            <div class="filters-container w-100" data-url="{{ route('content.videos') }}">
                <div class="d-flex align-items-center w-25">
                    <x-search_input id="search_input" type="text" name="search" placeholder="Search video name..."
                        value="{{ request('search') }}" class="w-100" />
                </div>
            </div>
        </div>

    </section>

@endsection

@push('scripts')
    <script src="{{ asset('js/filters.js') }}"></script>
@endpush