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
                    <h3 class="h3-semibold">8,492</h3>
                </div>
            </div>
            <div class="dashboard-num-cards-container">
                <div class="card-icon2">
                    <x-svg-icon name="content" size="18" color="#337FE8" />
                </div>
                <div class="card-icon-text">
                    <h4 class="h6-semibold" style="color:#ADADAD;">Active Videos</h4>
                    <h3 class="h3-semibold">8,492</h3>
                </div>
            </div>
            <div class="dashboard-num-cards-container">
                <div class="card-icon3">
                    <x-svg-icon name="message" size="18" color="#01A20C" />
                </div>
                <div class="card-icon-text">
                    <h4 class="h6-semibold" style="color:#ADADAD;">Total Comments</h4>
                    <h3 class="h3-semibold">8,492</h3>
                </div>
            </div>
            <div class="dashboard-num-cards-container">
                <div class="card-icon4">
                    <x-svg-icon name="shield-block" size="18" color="#D60000" />
                </div>
                <div class="card-icon-text">
                    <h4 class="h6-semibold" style="color:#ADADAD;">Blocked Users</h4>
                    <h3 class="h3-semibold">8,492</h3>
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

                    <div class="dashboard-video-card">
                        <div class="dashboard-video-card-image">
                            <img src="{{ asset('images/global/login-img.png') }}" alt="video image">

                            <span>Video</span>
                        </div>

                        <div class="dashboard-video-card-content">
                            <div class="dashboard-video-card-content-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="h6-semibold" style="color:#35758C;">Men's health</h4>
                                    <h4 class="h6-ragular"
                                        style="
                                                                                                                                                                            color:#35758C;
                                                                                                                                                                            padding: 8px;
                                                                                                                                                                            border-radius: 12px;
                                                                                                                                                                            background: #F1F9FA;">
                                        Puplished
                                    </h4>
                                </div>

                                <h3 class="h5-semibold"
                                    style="
                                                                                                                                                                        margin-top:8px;
                                                                                                                                                                            line-height: 1.5em;
                                                                                                                                                                    ">
                                    Lorem
                                    ipsum
                                    dolor
                                    sit
                                    amet
                                    consectetur.
                                </h3>
                                <p class="h6-ragular" style="color:#ADADAD;">15:24 . Updated 2 days ago</p>
                            </div>

                            <div class="dashboard-video-card-content-down">
                                <div class="gap-2 d-flex align-items-center">
                                    <x-svg-icon name="edit-pen2" size="12" color="Black" />
                                    <x-svg-icon name="trash" size="12" color="Black" />
                                    <x-svg-icon name="three-dots" size="12" color="Black" />
                                </div>

                                <div class="gap-3 d-flex align-items-center">
                                    <div>
                                        <x-svg-icon name="eye" size="12" color="Black" />
                                        <span class="h6-ragular">2.5k</span>
                                    </div>
                                    <div>
                                        <x-svg-icon name="message" size="12" color="Black" />
                                        <span class="h6-ragular">18</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 d-flex justify-content-end w-100">
                        <x-link_btn href="h6-ragular" class="btn-nothing" style="color: #7B7B7B;">View
                            all</x-link_btn>
                    </div>
                </div>
            </div>
            <!-- right -->
            <div class="dashboard-contetn-right"></div>
        </div>
    </section>
@endsection

@push('scripts')
@endpush