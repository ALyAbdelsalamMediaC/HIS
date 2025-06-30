@extends('layouts.app')
@section('title', 'HIS | Dashboard')
@section('content')
    <section>
        <div class="dashboard-num-cards">
            <div class="dashboard-num-cards-container">
                <div class="card-icon1">
                    <x-svg-icon name="user" size="18" color="#8D44CC" />
                </div>
                <div class="card-icon-text">
                    <h4 class="h6-semibold" style="color:#ADADAD;">Total Users</h4>
                    <h3 class="h3-semibold">{{ number_format($usersCount) }}</h3>
                </div>
            </div>
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
            <div class="dashboard-num-cards-container">
                <div class="card-icon4">
                    <x-svg-icon name="clock2" size="18" color="#E0B610" />
                </div>
                <div class="card-icon-text">
                    <h4 class="h6-semibold" style="color:#ADADAD;">Pending Requests</h4>
                    <h3 class="h3-semibold">{{ number_format($mediaCountPending) }}</h3>
                </div>
            </div>
        </div>

        <div class="dashboard-contetn-container">
            <!-- left -->
            <div class="dashboard-contetn-left">
                <div class="dashboard-content-items w-100">
                    <div class="d-flex justify-content-between">
                        <h3 class="h4-semibold" style="color:#35758c;">Content Management</h3>

                        <div class="gap-2 d-flex align-items-center">
                            <x-link_btn href="{{  route('content.store') }}">
                                <x-svg-icon name="content" size="20" />
                                <span>Add Video</span>
                            </x-link_btn>
                            <x-link_btn href="{{ route('articles.store') }}">
                                <x-svg-icon name="article" size="20" />
                                <span>Add Article</span>
                            </x-link_btn>
                        </div>
                    </div>
                    

                    <div class="mt-3 d-flex justify-content-end w-100">
                        <x-link_btn href="{{ route('content.videos') }}" class="h6-ragular btn-nothing"
                            style="color: #7B7B7B;">View
                            all</x-link_btn>
                    </div>
                </div>
            </div>
            <!-- right -->
            <div class="dashboard-contetn-right">
                <!-- Top Videos -->
                <div class="dashboard-content-items w-100">
                    <div class="d-flex justify-content-between">
                        <h3 class="h4-semibold" style="color:#35758c;">Top Videos</h3>

                        <x-link_btn href="{{ route('content.videos') }}" class="h6-ragular btn-nothing"
                            style="color: #7B7B7B;">View
                            all</x-link_btn>

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
                <div class="mt-4 dashboard-content-items w-100">
                    <div class="d-flex justify-content-between">
                        <h3 class="h4-semibold" style="color:#35758c;">Top Articles</h3>

                        <x-link_btn href="{{ route('content.articles') }}" class="h6-ragular btn-nothing"
                            style="color: #7B7B7B;">View
                            all</x-link_btn>

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

            </div>
        </div>

        {{-- Delete Video Modal for Dashboard --}}

    </section>
@endsection

@push('scripts')

@endpush