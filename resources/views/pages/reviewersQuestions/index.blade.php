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

  <div class="accordion" id="questionGroupsAccordion" style="margin-top: 15px;">
    @forelse ($questionGroups as $questionGroup)
    <div class="mb-3 accordion-item">
      <h2 class="accordion-header d-flex" id="groupHeading{{ $questionGroup->id }}">
        <button class="accordion-button h4-semibold" type="button" data-bs-toggle="collapse"
          data-bs-target="#groupCollapse{{ $questionGroup->id }}" aria-expanded="false"
          aria-controls="groupCollapse{{ $questionGroup->id }}">
          {{ $questionGroup->name }}
        </button>
        <div class="px-3 d-flex align-items-center">
          <a href="{{ route('reviewersQuestions.view_edit', $questionGroup->id) }}" class="text-decoration-none me-2">
            <x-svg-icon name="edit-pen2" size="18" color="#adadad" />
          </a>
          <button type="button" class="p-0 bg-transparent border-0 delete-group-btn" data-bs-toggle="modal"
            data-bs-target="#deleteGroupModal{{ $questionGroup->id }}">
            <x-svg-icon name="trash" size="18" color="#BB1313" />
          </button>
        </div>
      </h2>
      <div id="groupCollapse{{ $questionGroup->id }}" class="accordion-collapse collapse"
        aria-labelledby="groupHeading{{ $questionGroup->id }}" data-bs-parent="#questionGroupsAccordion">
        <div class="accordion-body">
          @if($questionGroup->questions->count() > 0)
          <div class="questions-list">
            @foreach($questionGroup->questions as $question)
            <div class="mb-4 question-item">
              <h4 class="mb-2 h4-semibold">{{ $loop->iteration }}. {{ $question->content }}</h4>
              <p class="mb-2 text-muted small">Type: {{ str_replace('_', ' ', ucwords($question->type, '_')) }}</p>
              
              @if($question->type !== 'text' && $question->answers->count() > 0)
              <div class="answers-list ms-3">
                <h5 class="mb-2 h5-regular">Answers:</h5>
                <div class="flex-wrap mt-2 d-flex align-items-center" style="gap:20px;">

                    @foreach($question->answers as $answer)
                    <div class="mb-2 answer-item">
                      <p class="mb-1">• {{ $answer->content }}</p>
                    </div>
                    @endforeach
                </div>
              </div>
              @elseif($question->type !== 'text' && $question->answers->count() === 0)
              <p class="text-muted small ms-3">No answers provided for this question.</p>
              @endif
            </div>
            @endforeach
          </div>
          @else
          <p>No questions in this group.</p>
          @endif
        </div>
      </div>
    </div>

    <!-- Delete Modal for Question Group -->
    <x-modal id="deleteGroupModal{{ $questionGroup->id }}" title="Delete Question Group">
      <div class="my-3">
        <p class="h3-semibold" style="color:black;">Are you sure you want to delete the question group
          "{{ $questionGroup->name }}"?</p>
        <p class="text-muted">This will also delete all questions and answers within this group.</p>
      </div>
      <div class="modal-footer">
      <x-button type="button" style="color:#BB1313; background-color:transparent; border:1px solid #BB1313;" data-bs-dismiss="modal">Cancel</x-button>
        <form action="{{ route('question_groups.delete', $questionGroup->id) }}" method="POST">
          @csrf
          @method('DELETE')
            <x-button type="submit" style="background-color:#BB1313; color:#fff;">Delete</x-button>
        </form>
      </div>
    </x-modal>
    @empty
    <p>No question groups found.</p>
    @endforelse
  </div>

</section>

@endsection

@push('scripts')
@endpush
