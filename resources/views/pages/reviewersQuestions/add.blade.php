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

  <!-- Questions Group -->
  <div class="filters-container w-100" data-url="{{ route('reviewersQuestions.view_add') }}">
    <div class="d-flex justify-content-between align-items-end">
      <div style="width:70%;">
        <!-- Question Group Selection -->
        <div class="form-infield">
          <x-text_label for="question_group_id" :required="true">Questions Group Name</x-text_label>
          <x-select id="question_group_id" name="question_group_id" class="filter-select" :options="$questionGroup->mapWithKeys(function ($questionGroup) {
          return [$questionGroup->id => $questionGroup->name];
          })->all()" placeholder="Enter group name" data-name="Help Category" :selected="request('question_group_id', session('selected_group_id'))" />
          <div id="question_group_id-error-container">
            <x-input-error :messages="$errors->get('question_group_id')" />
          </div>
        </div>
      </div>
      
      <div class="gap-2 d-flex align-items-center">
        <x-button type="button" data-bs-toggle="modal" data-bs-target="#editGroupModal" id="editGroupBtn">
          Edit Group
        </x-button>
        <x-button type="button" data-bs-toggle="modal" data-bs-target="#addGroupModal">
          <x-svg-icon name="plus3" size="16" />
          Create Group
        </x-button>
      </div>
    </div>
  </div>

  <!-- Create New Question -->
  <div class="create-question-containuer">
    <h3 class="h3-semibold">Create New Question</h3>
    <p class="h6-ragular" style="color:#ADADAD;">Select a question type and fill in the details.</p>

         <form method="POST" action="{{ route('reviewersQuestions.add') }}" novalidate class="mt-3">
       @csrf
       <input type="hidden" id="question_type" name="question_type" value="text">
       <input type="hidden" id="form_question_group_id" name="question_group_id" value="{{ request('question_group_id', session('selected_group_id')) }}">

       <div class="gap-4 d-flex align-items-start">
         <!-- question type -->
         <div class="question-types">
           <h4 class="h6-semibold">Question Type</h4>
 
           <div class="types-of-question">
             <div class="types-of active" data-type="text" role="button" tabindex="0">
               <x-svg-icon name="text-question" size="18" color="#ADADAD" />
               <span class="h6-semibold" style="margin-left:9px;">Text</span>
             </div>
             <div class="types-of" data-type="multiple_choice" role="button" tabindex="0">
               <x-svg-icon name="multiple-choice" size="18" color="#ADADAD" />
               <span class="h6-semibold" style="margin-left:10px;">Multiple Choice</span>
             </div>
             <div class="types-of" data-type="single_choice" role="button" tabindex="0">
               <x-svg-icon name="single-check" size="18" color="#ADADAD" />
               <span class="h6-semibold" style="margin-left:10px;">Single Choice</span>
             </div>
           </div>
         </div>
 
         <!-- question -->
         <div class="the-question">
           <h4 class="h6-semibold">Question Title</h4>
 
           <!-- text -->
           <div class="question-of" data-type="text">
             <div class="form-infield">
               <x-text_input type="text" id="text_question" name="question" placeholder="Enter text question" data-name="Text question" value="{{ old('question') }}" />
             </div>
           </div>
 
           <!-- Multiple Choice -->
           <div class="question-of d-none" data-type="multiple_choice">
               <div class="form-infield">
                 <x-text_input type="text" id="text_question_multiple" name="question" placeholder="Enter text question" data-name="Text question" value="{{ old('question') }}" />
               </div>
 
                 <div class="form-infield">
                   <x-text_label for="answer-1">Answers</x-text_label>
                   <div class="answer-list">
                     <div class="gap-3 d-flex align-items-center answer-row">
                       <x-text_input type="text" name="answers[]" placeholder="Enter answer" data-name="Text answer" />
                       <div class="actions">
                         <x-button type="button" class="btn-nothing delete-answer" style="display: none;">
                           <x-svg-icon name="false" size="17" color="#ADADAD" />
                         </x-button>
                         <x-button type="button" class="add-answer" style="background: #F1F9FA; color:#35758c;">Add</x-button>
                       </div>
                     </div>
                   </div>
                 </div>  
           </div>
         
           <!-- Single Choice -->
           <div class="question-of d-none" data-type="single_choice">
               <div class="form-infield">
                 <x-text_input type="text" id="text_question_single" name="question" placeholder="Enter text question" data-name="Text question" value="{{ old('question') }}" />
               </div>
 
               <div class="form-infield">
                 <x-text_label for="answer-1">Answers</x-text_label>
                 <div class="answer-list">
                   <div class="gap-3 d-flex align-items-center answer-row">
                     <x-text_input type="text" name="answers[]" placeholder="Enter answer" data-name="Text answer" />
                     <div class="actions">
                       <x-button type="button" class="btn-nothing delete-answer" style="display: none;">
                         <x-svg-icon name="false" size="17" color="#ADADAD" />
                       </x-button>
                       <x-button type="button" class="add-answer" style="background: #F1F9FA; color:#35758c;">Add</x-button>
                     </div>
                   </div>
                 </div>
               </div>  
               
              </div>
              <div class="mt-3 d-flex justify-content-end">
                <x-button type="submit">Save</x-button>
              </div>

       </div>
        
      </div>
    </form>
    </div>

  <!-- Existing Questions -->
  @if($existingQuestions && $existingQuestions->count() > 0)
  <div class="create-question-containuer">
    <div style="border-bottom: 1px solid #EDEDED; padding-bottom:20px;">
      <h3 class="h3-semibold">Existing Questions ({{ $existingQuestions->count() }})</h3>
    </div>

    <div class="w-100">
      @foreach($existingQuestions as $question)
      <div class="gap-4 d-flex align-items-center w-100" style="border-bottom: 1px solid var(--border-color);">
        <div class="order-icon">
          <x-svg-icon name="order" size="15" color="#000" />
        </div>

        <div class="existing-question w-100">
          <div class="d-flex justify-content-between align-items-center">
            <div class="existing-question-type 
              @if($question->type === 'text')
                text-type
              @elseif($question->type === 'multiple_choice')
                multiple-choice-type
              @elseif($question->type === 'single_choice')
                single-choice-type
              @endif
            ">
              @if($question->type === 'text')
                <x-svg-icon name="text-question" size="15" color="#973C00" />
                <span class="h6-semibold">Text</span>
              @elseif($question->type === 'multiple_choice')
                <x-svg-icon name="multiple-choice" size="15" color="#35758C" />
                <span class="h6-semibold">Multiple Choice</span>
              @elseif($question->type === 'single_choice')
                <x-svg-icon name="single-check" size="15" color="#087A2A" />
                <span class="h6-semibold">Single Choice</span>
              @endif
            </div>
    
            <div class="gap-2 d-flex align-items-center">
              <x-svg-icon name="edit-pen2" size="15" color="#adadad"  />
              <x-svg-icon name="trash" size="15" color="#BB1313" />
            </div>
          </div>
  
          <div class="mt-3">
            <h3 class="h6-semibold">{{ $question->content }}</h3>
  
            @if($question->answers && $question->answers->count() > 0)
              <div class="flex-wrap mt-2 d-flex align-items-center" style="gap:20px;">
                @foreach($question->answers as $answer)
                  <h4 class="h6-ragular">{{ $answer->content }}</h4>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  <!-- Add Group Modal -->
  <x-modal id="addGroupModal" title="Create Group">
    <form method="POST" action="{{ route('question_groups.store') }}" class="mt-4" novalidate>
    @csrf
      <div class="form-infield">
        <x-text_label for="group_name" :required="true">Group Name</x-text_label>
        <x-text_input type="text" id="group_name" name="name" placeholder="Group Name" data-required="true" data-name="Group Name" />
        <div id="group_name-error-container">
          <x-input-error :messages="$errors->get('name')" />
        </div>
      </div>
      <div class="mt-3 d-flex justify-content-end">
        <x-button type="button" class="bg-trans-btn me-2" data-bs-dismiss="modal">Cancel</x-button>
        <x-button type="submit">Add Group</x-button>
      </div>
    </form>
  </x-modal>

  <!-- Edit Groups Modal -->
  <x-modal id="editGroupModal" title="Edit Groups">
    <div class="groups-list">
      @forelse($questionGroup as $group)
      <div class="p-2 group-item d-flex align-items-center justify-content-between border-bottom">
        <div class="group-title d-flex align-items-center flex-grow-1" data-id="{{ $group->id }}">
          <span class="group-text">{{ $group->name }}</span>
          <form action="{{ route('question_groups.update', $group) }}" method="POST" class="edit-group-form d-none w-100">
            @csrf
            @method('PUT')
            <div class="d-flex">
              <x-text_input type="text" name="name" placeholder="Group name" class="group-input" value="{{ $group->name }}" />
            </div>
          </form>
        </div>
        <div class="mx-2 group-actions">
          <button type="button" class="p-0 border-0 btn btn-link edit-group-btn me-2" data-id="{{ $group->id }}">
            <x-svg-icon name="edit-pen2" size="18" color="#adadad" />
          </button>
          <button type="button" class="p-0 border-0 btn btn-link delete-group-btn" data-bs-toggle="modal" data-bs-target="#deleteGroupModal{{ $group->id }}">
            <x-svg-icon name="trash" size="18" color="#adadad" />
          </button>
        </div>
      </div>
      @empty
        <p class="p-3 text-center">No groups found.</p>
      @endforelse
    </div>
    <div class="mt-3 d-flex justify-content-end">
      <x-button type="button" class="bg-trans-btn" data-bs-dismiss="modal">Close</x-button>
    </div>
  </x-modal>

  <!-- Delete Group Modals -->
  @foreach($questionGroup as $group)
  <x-modal id="deleteGroupModal{{ $group->id }}" title="Delete Group">
    <div class="my-3">
      <p class="h3-semibold" style="color:black;">Are you sure you want to delete the group "{{ $group->name }}"?</p>
    </div>
    <div class="modal-footer">
      <x-button type="button" style="color:#BB1313; background-color:transparent; border:1px solid #BB1313;" data-bs-dismiss="modal">Cancel</x-button>
      <form action="{{ route('question_groups.delete', $group) }}" method="POST">
        @csrf
        @method('DELETE')
        <x-button type="submit" style="background-color:#BB1313; color:#fff;">Delete</x-button>
      </form>
    </div>
  </x-modal>
  @endforeach
</section>

@endsection

@push('scripts')
  <script src="{{ asset('js/coutriesAPi.js') }}"></script>
  <script src="{{ asset('js/validations.js') }}"></script>
  <script src="{{ asset('js/showToast.js') }}"></script>
  <script src="{{ asset('js/filters.js') }}"></script>
  <script src="{{ asset('js/ChangeQuestionType.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      let pendingDeleteValue = null;

      // All functionality is now handled by ChangeQuestionType.js module

      // Initialize question type manager with the last selected type
      const lastQuestionType = '{{ old("question_type", "text") }}';
      if (window.questionTypeManager) {
        window.questionTypeManager.setType(lastQuestionType);
      }

      // Sync the hidden form field with the selected group
      const questionGroupSelect = document.getElementById('question_group_id');
      const formQuestionGroupId = document.getElementById('form_question_group_id');
      
      questionGroupSelect.addEventListener('change', function() {
        formQuestionGroupId.value = this.value;
      });

      // Clear form after successful submission (if success message is present)
      @if(session('success'))
        if (window.questionTypeManager) {
          window.questionTypeManager.clearFormAfterSuccess();
        }
      @endif

      // Handle edit group button clicks
      const editGroupButtons = document.querySelectorAll('.edit-group-btn');
      editGroupButtons.forEach(button => {
        button.addEventListener('click', function () {
          const groupId = this.getAttribute('data-id');
          const groupItem = document.querySelector(`.group-title[data-id="${groupId}"]`);
          const textElement = groupItem.querySelector('.group-text');
          const formElement = groupItem.querySelector('.edit-group-form');

          // Hide text, show form
          textElement.classList.add('d-none');
          formElement.classList.remove('d-none');

          // Focus on input
          const inputElement = formElement.querySelector('.group-input');
          inputElement.focus();
          inputElement.select();

          // Add event listener for enter key to submit
          inputElement.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
              e.preventDefault();
              formElement.submit();
            } else if (e.key === 'Escape') {
              textElement.classList.remove('d-none');
              formElement.classList.add('d-none');
            }
          });
        });
      });
    })
  </script>
@endpush