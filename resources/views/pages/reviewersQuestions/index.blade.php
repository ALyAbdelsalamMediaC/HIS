@extends('layouts.app')
@section('title', 'HIS | Reviewers Questions')
@section('content')

<section>
  <div class="d-flex justify-content-between align-items-center">
      <div>
          <h2 class="h2-semibold" style="color:#35758C;">Review Questions</h2>
          <p class="h5-ragular" style="color:#ADADAD;">Manage and organize your survey questions.</p>
      </div>

      <x-link_btn href="{{  route('reviewersQuestions.view_add') }}">
          <x-svg-icon name="plus3" size="20" />
          <span>Add Question</span>
      </x-link_btn>
    </div>
</section>

@endsection

@push('scripts')
@endpush
