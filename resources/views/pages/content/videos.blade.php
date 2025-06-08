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
                <div class="d-flex justify-content-between align-items-center">

                    <div class="w-25">
                        <x-search_input id="search_input" type="text" name="search" placeholder="Search video name..."
                            value="{{ request('search') }}" class="w-100" />
                    </div>


                </div>
            </div>

            <div class="content-container-cards">
                @forelse ($media as $item)
                    <div class="content-container-card">
                        <div class="content-container-card-img">
                            <img src="{{ asset($item->thumbnail_path) }}" alt="{{ $item->title }}">

                            <span>Video</span>
                        </div>

                        <div class="dashboard-video-card-content-content">
                            <div class="dashboard-video-card-content-content-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="h6-semibold" style="color:#35758C;">{{ $item->category->name }}</h4>
                                    <h4 class="h6-ragular"
                                        style="color:#35758C; padding: 8px; border-radius: 12px; background: #F1F9FA;">
                                        {{ ucfirst($item->status) }}
                                    </h4>
                                </div>

                                <h3 class="h5-semibold" style="margin-top:8px; line-height: 1.5em;">
                                    {{ $item->title }}
                                </h3>
                                <p class="h6-ragular" style="color:#ADADAD;">15:24 . Uploaded
                                    {{ $item->created_at->diffForHumans() }}
                                </p>
                            </div>

                            <div class="dashboard-video-card-content-content-down">
                                <div class="gap-2 d-flex align-items-center">
                                    <x-svg-icon name="edit-pen2" size="12" color="Black" />
                                    <x-svg-icon name="trash" size="12" color="Black" />
                                    <x-svg-icon name="three-dots" size="12" color="Black" />
                                </div>

                                <div class="gap-3 d-flex align-items-center">
                                    <div>
                                        <x-svg-icon name="eye" size="12" color="Black" />
                                        <span class="h6-ragular">{{ $item->views}}</span>
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
                    <div class="py-5 text-center">
                        <p class="h5-ragular" style="color:#ADADAD;">No videos found</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-2 d-flex justify-content-between align-items-center">
                <!-- Table Info and Pagination -->
                @if($media->count())
                    <x-table-info :paginator="$media" />
                    <x-pagination :paginator="$media" :appends="request()->query()" />
                @endif
            </div>
        </div>
    </section>

@endsection

@push('scripts')
    <script src="{{ asset('js/filters.js') }}"></script>
@endpush