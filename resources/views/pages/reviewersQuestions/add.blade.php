@extends('layouts.app')
@section('title', 'HIS | Add Reviewers Questions')
@section('content')

<section>

  <div class="gap-3 d-flex align-items-center">
    <a href="{{ url()->previous() }}" class="arrow-back-btn">
      <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
    </a>

    <div>
      <h2 class="h2-semibold" style="color:#35758C;">Add Reviewers Questions</h2>
      <p class="h5-ragular" style="color:#ADADAD;">Add and organize your survey questions.</p>
    </div>
  </div>

</section>

@endsection

@push('scripts')
  <script src="{{ asset('js/coutriesAPi.js') }}"></script>
  <script src="{{ asset('js/validations.js') }}"></script>
  <script src="{{ asset('js/showToast.js') }}"></script>
@endpush
