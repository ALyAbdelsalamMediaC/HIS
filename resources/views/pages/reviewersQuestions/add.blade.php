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

    <form method="POST" action="{{ route('reviewersQuestions.add') }}" novalidate>
      @csrf
      <input type="hidden" id="question_type" name="question_type" value="text">
      
      <!-- Question Group Selection -->
      <div class="gap-3 mb-4 d-flex align-items-end justify-content-between w-100">
        <div class="form-infield" style="width: 75%;">
        <x-text_label for="question_group_id" :required="true">Questions Group Name</x-text_label>
        <x-select id="question_group_id" name="question_group_id" :options="$questionGroup->mapWithKeys(function ($questionGroup) {
    return [$questionGroup->id => $questionGroup->name];
    })->all()" placeholder="Enter group name" data-name="Help Category" :selected="old('question_group_id', session('selected_group_id'))" />
        <div id="question_group_id-error-container">
          <x-input-error :messages="$errors->get('question_group_id')" />
        </div>
        </div>
        <div class="gap-2 d-flex align-items-center" style="width: 25%;">
          <x-button type="button" data-bs-toggle="modal" data-bs-target="#editGroupModal" id="editGroupBtn">
            Edit Group
          </x-button>
          <x-button type="button" data-bs-toggle="modal" data-bs-target="#addGroupModal">
            <x-svg-icon name="plus3" size="16" />
            Create Group
          </x-button>
        </div>
      </div>
      
      <div class="gap-4 mt-4 d-flex align-items-start">
        <!-- question type -->
        <div class="question-types">
          <h4 class="h6-semibold">Question Type</h4>

          <div class="types-of-question">
            <div class="types-of active" data-type="text" role="button" tabindex="0">
              <x-svg-icon name="text-question" size="18" color="#ADADAD" />
              <span class="h6-semibold" style="margin-left:9px;">Text</span>
            </div>
            <div class="types-of" data-type="multiple" role="button" tabindex="0">
              <x-svg-icon name="multiple-choice" size="18" color="#ADADAD" />
              <span class="h6-semibold" style="margin-left:10px;">Multiple Choice</span>
            </div>
            <div class="types-of" data-type="single" role="button" tabindex="0">
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
        <div class="question-of d-none" data-type="multiple">
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
        <div class="question-of d-none" data-type="single">
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
    </form>
    </div>

  <!-- Existing Questions -->
  @if($existingQuestions && $existingQuestions->count() > 0)
  <div class="create-question-containuer">
    <div style="border-bottom: 1px solid #EDEDED; padding-bottom:20px;">
      <h3 class="h3-semibold">Existing Questions ({{ $existingQuestions->count() }})</h3>
    </div>

    <div>
      @foreach($existingQuestions as $question)
      <div class="existing-question">
        <div class="d-flex justify-content-between align-items-center">
          <div class="existing-question-type">
            @if($question->question_type === 'text')
              <x-svg-icon name="text-question" size="15" color="#35758C" />
              <span class="h6-semibold">Text</span>
            @elseif($question->question_type === 'multiple')
              <x-svg-icon name="multiple-choice" size="15" color="#35758C" />
              <span class="h6-semibold">Multiple Choice</span>
            @elseif($question->question_type === 'single')
              <x-svg-icon name="single-check" size="15" color="#35758C" />
              <span class="h6-semibold">Single Choice</span>
            @endif
          </div>
  
          <div class="gap-2 d-flex align-items-center">
            <x-svg-icon name="edit-pen2" size="15" color="#adadad"  />
            <x-svg-icon name="trash" size="15" color="#BB1313" />
          </div>
        </div>

        <div class="mt-3">
          <h3 class="h6-semibold">{{ $question->question }}</h3>

          @if($question->answers && $question->answers->count() > 0)
            <div class="flex-wrap mt-2 d-flex align-items-center" style="gap:20px;">
              @foreach($question->answers as $answer)
                <h4 class="h6-ragular">{{ $answer->content }}</h4>
              @endforeach
            </div>
          @endif
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
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const typeButtons = document.querySelectorAll('.question-types .types-of');
      const questionBlocks = document.querySelectorAll('.the-question .question-of');
      let pendingDeleteValue = null;

      function setActiveType(selectedType) {
        typeButtons.forEach(btn => {
          const isActive = btn.dataset.type === selectedType;
          btn.classList.toggle('active', isActive);
        });

        // Update the hidden question_type field
        const questionTypeField = document.getElementById('question_type');
        if (questionTypeField) {
          questionTypeField.value = selectedType;
        }

        questionBlocks.forEach(block => {
          const isMatch = block.dataset.type === selectedType;
          block.classList.toggle('d-none', !isMatch);

          const inputs = block.querySelectorAll('input, select, textarea');
          inputs.forEach(input => {
            if (isMatch) {
              input.removeAttribute('disabled');
            } else {
              input.setAttribute('disabled', 'disabled');
            }
          });
        });
      }

      typeButtons.forEach(btn => {
        btn.addEventListener('click', () => setActiveType(btn.dataset.type));
      });

      // Initialize default selection - try to restore from session or use default
      const lastQuestionType = '{{ old("question_type", "text") }}';
      setActiveType(lastQuestionType);

      // Form validation
      const form = document.querySelector('form');
      form.addEventListener('submit', function(e) {
        const questionGroupId = document.getElementById('question_group_id').value;
        const questionType = document.getElementById('question_type').value;
        let questionValue = '';
        
        // Get question value based on active type
        if (questionType === 'text') {
          questionValue = document.getElementById('text_question').value.trim();
        } else if (questionType === 'multiple') {
          questionValue = document.getElementById('text_question_multiple').value.trim();
        } else if (questionType === 'single') {
          questionValue = document.getElementById('text_question_single').value.trim();
        }

        if (!questionGroupId) {
          e.preventDefault();
          alert('Please select a question group.');
          return false;
        }

        if (!questionValue) {
          e.preventDefault();
          alert('Please enter a question.');
          return false;
        }

        // For multiple/single choice, validate that at least one answer is provided
        if ((questionType === 'multiple' || questionType === 'single') && questionType !== 'text') {
          const answers = document.querySelectorAll('input[name="answers[]"]');
          let hasAnswer = false;
          answers.forEach(answer => {
            if (answer.value.trim()) {
              hasAnswer = true;
            }
          });
          
          if (!hasAnswer) {
            e.preventDefault();
            alert('Please provide at least one answer option.');
            return false;
          }
        }
      });

      // Clear form after successful submission (if success message is present)
      @if(session('success'))
        // Reset form fields
        document.getElementById('text_question').value = '';
        document.getElementById('text_question_multiple').value = '';
        document.getElementById('text_question_single').value = '';
        
        // Clear answer fields
        const answerInputs = document.querySelectorAll('input[name="answers[]"]');
        answerInputs.forEach((input, index) => {
          if (index > 0) {
            input.closest('.answer-row').remove();
          } else {
            input.value = '';
          }
        });
        
        // Reset to default question type
        setActiveType('text');
      @endif

      // Handle dynamic answer inputs for Multiple Choice and Single Choice
      const answerLists = document.querySelectorAll('.answer-list');
      answerLists.forEach(list => {
        list.addEventListener('click', function (e) {
          const addBtn = e.target.closest('.add-answer');
          const deleteBtn = e.target.closest('.delete-answer');

          if (addBtn) {
            const row = addBtn.closest('.answer-row');
            const answerList = row.parentElement;
            const clone = row.cloneNode(true);
            clone.querySelector('input').value = ''; // Clear input value
            clone.querySelector('.add-answer').style.display = 'inline-block'; // Ensure Add is visible
            clone.querySelector('.delete-answer').style.display = 'none'; // Hide Delete in new row
            row.querySelector('.add-answer').style.display = 'none'; // Hide Add in current row
            row.querySelector('.delete-answer').style.display = 'inline-block'; // Show Delete in current row
            answerList.appendChild(clone);
          }

          if (deleteBtn) {
            const row = deleteBtn.closest('.answer-row');
            const answerList = row.parentElement;
            if (answerList.children.length > 1) {
              row.remove();
              // Ensure the last row always has Add button visible
              const lastRow = answerList.lastElementChild;
              lastRow.querySelector('.add-answer').style.display = 'inline-block';
              lastRow.querySelector('.delete-answer').style.display = answerList.children.length > 1 ? 'none' : 'none';
            }
          }
        });
      });

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