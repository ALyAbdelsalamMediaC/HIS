@extends('layouts.app')
@section('title', 'HIS | Video - ' . $media->title)
@section('content')

  @if($media->status === 'pending')
    @include('pages.content.video.single_video_pending_admin')
  @elseif($media->status === 'published')
    @include('pages.content.video.single_video_published')
  @else
    <section>
    <div class="gap-3 mb-4 d-flex align-items-center">
    <div class="arrow-back-btn" onclick="window.history.back()">
      <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
    </div>
    <div>
      <h2 class="h2-semibold" style="color:#35758C;">Video Details</h2>
      <p class="h5-ragular" style="color:#ADADAD;">View video information and details</p>
    </div>
    </div>
    </section>
  @endif

@endsection

@push('scripts')
  <script>
    // Common JavaScript for all video views can be added here
    document.addEventListener('DOMContentLoaded', function () {
    // Common video player interactions or other shared functionality
    });
  </script>
@endpush