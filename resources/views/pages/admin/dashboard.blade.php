@extends('layouts.app')
@section('title', 'HIS | Dashboard')
@section('content')
    <section>
        <div class="dashboard-num-cards {{ auth()->check() && !auth()->user()->hasRole('admin') ? 'dashboard-num-cards-nonadmin' : '' }}">
            @if(auth()->check() && auth()->user()->hasRole('admin'))
                <div class="dashboard-num-cards-container">
                    <div class="card-icon1">
                        <x-svg-icon name="user" size="18" color="#8D44CC" />
                    </div>
                    <div class="card-icon-text">
                        <h4 class="h6-semibold" style="color:#ADADAD;">Total Users</h4>
                        <h3 class="h3-semibold">{{ number_format($usersCount) }}</h3>
                    </div>
                </div>
            @endif
            <div class="dashboard-num-cards-container">
                <div class="card-icon2">
                    <x-svg-icon name="content" size="18" color="#337FE8" />
                </div>
                <div class="card-icon-text">
                    <h4 class="h6-semibold" style="color:#ADADAD;">Active Videos</h4>
                    <h3 class="h3-semibold">{{ number_format($mediaCountPublished) }}</h3>
                </div>
            </div>
            <div class="dashboard-num-cards-container">
                <div class="card-icon3">
                    <x-svg-icon name="message" size="18" color="#01A20C" />
                </div>
                <div class="card-icon-text">
                    <h4 class="h6-semibold" style="color:#ADADAD;">Total Comments</h4>
                    <h3 class="h3-semibold">{{ number_format($commentsCount) }}</h3>
                </div>
            </div>
            @if(auth()->check() && auth()->user()->hasRole('admin'))
            <div class="dashboard-num-cards-container">
                <div class="card-icon4">
                    <x-svg-icon name="clock2" size="18" color="#E0B610" />
                </div>
                <div class="card-icon-text">
                    <h4 class="h6-semibold" style="color:#ADADAD;">Pending Requests</h4>
                    <h3 class="h3-semibold">{{ number_format($mediaCountPending) }}</h3>
                </div>
            </div>
            @endif
            <div class="dashboard-num-cards-container">
                <div class="card-icon4">
                    <x-svg-icon name="clock2" size="18" color="#E0B610" />
                </div>
                <div class="card-icon-text">
                    <h4 class="h6-semibold" style="color:#ADADAD;">Inreview Requests</h4>
                    <h3 class="h3-semibold">{{ number_format($mediaCountInreview) }}</h3>
                </div>
            </div>
        </div>

        <div class="dashboard-contetn-container">
            <!-- Left Section -->
            <div class="dashboard-contetn-left">
                <div class="dashboard-content-items w-100">
                    <div class="d-flex justify-content-between">
                        <h3 class="h4-semibold" style="color:#35758c;">Content Management</h3>
                        @if(auth()->user()->hasRole('admin'))
                            <div class="gap-2 d-flex align-items-center">
                                <x-link_btn href="{{ route('content.store') }}">
                                    <x-svg-icon name="content" size="20" />
                                    <span>Add Video</span>
                                </x-link_btn>
                                <x-link_btn href="{{ route('articles.store') }}">
                                    <x-svg-icon name="article" size="20" />
                                    <span>Add Article</span>
                                </x-link_btn>
                            </div>
                        @endif
                    </div>

                    <!-- Video Card -->
                    @if($lastPublishedMedia)
                        <div class="dashboard-video-card">
                            <a href="{{ route('content.video', ['id' => $lastPublishedMedia->id, 'status' => $lastPublishedMedia->status]) }}">
                                <div class="dashboard-video-card-image">
                                    <img src="{{ $lastPublishedMedia->thumbnail_path }}" alt="{{ $lastPublishedMedia->title }}">
                                    <span>Video</span>
                                </div>
                            </a>
                            <div class="dashboard-video-card-content">
                                <div class="dashboard-video-card-content-top">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="h6-semibold" style="color:#35758C;">{{ $lastPublishedMedia->user->name }}</h4>
                                        <h4 class="h6-ragular card-status {{ $lastPublishedMedia->status }}">
                                            {{ ucfirst($lastPublishedMedia->status) }}
                                        </h4>
                                    </div>
                                    <h3 class="h5-semibold" style="line-height: 1.5em; color:#000;">
                                        {{ $lastPublishedMedia->title }}
                                    </h3>
                                    <p class="h6-ragular" style="color:#ADADAD;">
                                        <x-format-duration :seconds="$lastPublishedMedia->duration" class="c-d-span" /> Uploaded
                                        {{ $lastPublishedMedia->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="dashboard-video-card-content-down">
                                    <div class="gap-2 d-flex align-items-center">
                                        <button class="btn-nothing" onclick="event.stopPropagation(); window.location.href='{{ route('content.edit', $lastPublishedMedia->id) }}'" style="background: none; border: none; padding: 0;">
                                            <x-svg-icon name="edit-pen2" size="12" color="Black" />
                                        </button>
                                        <button class="btn-nothing" data-bs-toggle="modal" data-bs-target="#deleteVideoModalDashboard" style="background: none; border: none; padding: 0;">
                                            <x-svg-icon name="trash" size="12" color="Black" />
                                        </button>
                                    </div>
                                    <div class="gap-3 d-flex align-items-center">
                                        <div>
                                            <x-svg-icon name="eye" size="12" color="Black" />
                                            <span class="h6-ragular" style="color:#000;">{{ $lastPublishedMedia->views }}</span>
                                        </div>
                                        <div>
                                            <x-svg-icon name="message" size="12" color="Black" />
                                            <span class="h6-ragular" style="color:#000;">{{ $lastPublishedMediaCommentsCount }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 d-flex justify-content-end w-100">
                            <x-link_btn href="{{ route('content.videos') }}" class="h6-ragular btn-nothing" style="color: #7B7B7B;">View all</x-link_btn>
                        </div>
                    @else
                        <div class="dashboard-video-card">
                            <div class="dashboard-video-card-content">
                                <x-data-not-found>No published videos found.</x-data-not-found>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Section -->
            <div class="dashboard-contetn-right">
                <!-- Top Videos -->
                <div class="dashboard-content-items w-100">
                    <div class="d-flex justify-content-between">
                        <h3 class="h4-semibold" style="color:#35758c;">Top Videos</h3>
                        <x-link_btn href="{{ route('content.videos') }}" class="h6-ragular btn-nothing" style="color: #7B7B7B;">View all</x-link_btn>
                    </div>

                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead style="color:#ADADAD;">
                                <tr>
                                    <th style="width:20%; text-align: left;">#</th>
                                    <th style="width:30%; text-align: left;">Name</th>
                                    <th style="width:20%; text-align: left;">Views</th>
                                    <th style="width:20%; text-align: left;">Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="text-align: left;">1</td>
                                    <td style="text-align: left;">Ahmed</td>
                                    <td style="text-align: left;">132k</td>
                                    <td style="text-align: left;">50</td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <x-data-not-found>No videos found.</x-data-not-found>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Articles -->
                @if(auth()->check() && auth()->user()->hasRole('admin'))
                    <div class="mt-4 dashboard-content-items w-100">
                        <div class="d-flex justify-content-between">
                            <h3 class="h4-semibold" style="color:#35758c;">Top Articles</h3>
                            <x-link_btn href="{{ route('content.articles') }}" class="h6-ragular btn-nothing" style="color: #7B7B7B;">View all</x-link_btn>
                        </div>

                        <div class="table-responsive">
                            <table class="custom-table">
                                <thead style="color:#ADADAD;">
                                    <tr>
                                        <th style="width:20%; text-align: left;">#</th>
                                        <th style="width:30%; text-align: left;">Name</th>
                                        <th style="width:20%; text-align: left;">Comments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="text-align: left;">1</td>
                                        <td style="text-align: left;">Ahmed</td>
                                        <td style="text-align: left;">50</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <x-data-not-found>No articles found.</x-data-not-found>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Delete Video Modal for Dashboard -->
                @if($lastPublishedMedia)
                    <x-modal id="deleteVideoModalDashboard" title="Delete Video">
                        <div class="my-3">
                            <p class="h3-semibold" style="color:black;">
                                Are you sure you want to delete the video "{{ $lastPublishedMedia->title }}"?
                            </p>
                        </div>
                        <div class="modal-footer">
                            <x-button type="button" class="px-4 bg-trans-btn" data-bs-dismiss="modal">Cancel</x-button>
                            <form action="{{ route('content.destroy', $lastPublishedMedia->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" class="px-4 btn-danger">Delete</x-button>
                            </form>
                        </div>
                    </x-modal>
                @endif
            </div>
        </div>
    </section>
@endsection

@push('scripts')
@endpush