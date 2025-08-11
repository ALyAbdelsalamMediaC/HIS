@extends('layouts.app')
@section('title', 'HIS | Video - ' . $media->title)
@section('content')

@if($media->status === 'published')
    @include('pages.content.video.single_video_published')
@elseif($media->status === 'pending')
    @include('pages.content.video.single_video_pending')
@elseif($media->status === 'inreview')
    @if(auth()->user()->hasRole('admin'))
        @include('pages.content.video.single_video_inreview_admin')
    @elseif(auth()->user()->hasRole('reviewer'))
        @include('pages.content.video.single_video_inreview_reviewer')
    @endif
@elseif($media->status === 'revise')
    @if(auth()->user()->hasRole('admin'))
        @include('pages.content.video.single_video_revise_admin')
    @elseif(auth()->user()->hasRole('reviewer'))
        @include('pages.content.video.single_video_revise_reviewer')
    @else
        <section>
        <div class="gap-3 mb-4 d-flex align-items-center">
        <a href="{{ url()->previous() }}" class="arrow-back-btn">
          <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
        </a>
        <div>
          <h2 class="h2-semibold" style="color:#35758C;">Video Details</h2>
          <p class="h5-ragular" style="color:#ADADAD;">View video information and details</p>
        </div>
        </div>
        </section>
    @endif
@else
    <section>
    <div class="gap-3 mb-4 d-flex align-items-center">
    <a href="{{ url()->previous() }}" class="arrow-back-btn">
      <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
    </a>
    <div>
      <h2 class="h2-semibold" style="color:#35758C;">Video Details</h2>
      <p class="h5-ragular" style="color:#ADADAD;">View video information and details</p>
    </div>
    </div>
    </section>
@endif

@endsection

@push('scripts')
<script src="{{ asset('js/validations.js') }}"></script>
@endpush