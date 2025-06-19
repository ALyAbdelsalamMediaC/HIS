@extends('layouts.app')
@section('title', 'HIS | Edit Help')
@section('content')

  <section>
    <div class="gap-3 d-flex align-items-center">
    <div class="arrow-back-btn" onclick="window.history.back()">
      <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
    </div>

    <div>
      <h2 class="h2-semibold" style="color:#35758C;">Edit Help</h2>
      <p class="h5-ragular" style="color:#ADADAD;">Update your help details</p>
    </div>
    </div>

  </section>

@endsection

@push('scripts')
  <script src="{{ asset('js/validations.js') }}"></script>
@endpush