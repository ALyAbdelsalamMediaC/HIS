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
                <div class="card-icon1">
                    <x-svg-icon name="content" size="18" color="#337FE8" />
                </div>
                <div class="card-icon-text">
                    <h4 class="h6-semibold" style="color:#ADADAD;">Total Users</h4>
                    <h3 class="h3-semibold">8,492</h3>
                </div>
            </div>
            <div class="dashboard-num-cards-container">
                <div class="card-icon1">
                    <x-svg-icon name="message" size="18" color="#01A20C" />
                </div>
                <div class="card-icon-text">
                    <h4 class="h6-semibold" style="color:#ADADAD;">Total Users</h4>
                    <h3 class="h3-semibold">8,492</h3>
                </div>
            </div>
            <div class="dashboard-num-cards-container">
                <div class="card-icon1">
                    <x-svg-icon name="shield-block" size="18" color="#D60000" />
                </div>
                <div class="card-icon-text">
                    <h4 class="h6-semibold" style="color:#ADADAD;">Total Users</h4>
                    <h3 class="h3-semibold">8,492</h3>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
@endpush