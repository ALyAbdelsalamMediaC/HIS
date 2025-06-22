@extends('layouts.app')
@section('title', 'HIS | Video - Pending')
@section('content')

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

@endsection

@push('scripts')
  <script>

  </script>
@endpush