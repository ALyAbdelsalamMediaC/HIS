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
  <form method="POST" action="" class="mt-4" novalidate enctype="multipart/form-data">
  @csrf
    <div class="gap-3 d-flex align-items-end justify-content-between w-100">
      <div class="form-infield" style="width: 75%;">
      <x-text_label for="group_name" :required="true">Questions Group Name</x-text_label>
      <x-select id="group_name" name="group_name" :options="[]" placeholder="Enter group name" data-name="Help Category" :selected="old('group_name')" />
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
  </form>

  <!-- Create New Question -->
  <div class="create-question-containuer">
    <h3 class="h3-semibold">Create New Question</h3>
    <p class="h6-ragular" style="color:#ADADAD;">Select a question type and fill in the details.</p>

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
              <x-text_input type="text" id="text_question" name="text_question" placeholder="Enter text question" data-name="Text question" />
            </div>
        </div>

        <!-- Multiple Choice -->
        <div class="question-of d-none" data-type="multiple">
            <div class="form-infield">
              <x-text_input type="text" id="text_question_multiple" name="text_question" placeholder="Enter text question" data-name="Text question" />
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
              <x-text_input type="text" id="text_question_single" name="text_question" placeholder="Enter text question" data-name="Text question" />
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
          <x-button>Save</x-button>
        </div>
      </div>
    </div>
  </div>

  <!-- Existing Questions (3) -->
  <div class="create-question-containuer">
    <div style="border-bottom: 1px solid #EDEDED; padding-bottom:20px;">
      <h3 class="h3-semibold">Existing Questions (3)</h3>
    </div>

    <div>
      <div class="existing-question">
        <div class="d-flex justify-content-between align-items-center">
          <div class="existing-question-type">
            <x-svg-icon name="multiple-choice" size="15" color="#35758C" />
            <span class="h6-semibold">Multiple Choice</span>
          </div>
  
          <div class="gap-2 d-flex align-items-center">
            <x-svg-icon name="edit-pen2" size="15" color="#adadad"  />
            <x-svg-icon name="trash" size="15" color="#BB1313" />
          </div>
        </div>

        <div class="mt-3">
          <h3 class="h6-semibold">Lorem ipsum dolor sit amet consectetur?</h3>

          <div class="mt-2 d-flex align-items-center" style="gap:100px;">
            <h4 class="h6-ragular">lorem ipsum</h4>
            <h4 class="h6-ragular">lorem ipsum</h4>
          </div>
          <div class="mt-2 d-flex align-items-center" style="gap:100px;">
            <h4 class="h6-ragular">lorem ipsum</h4>
            <h4 class="h6-ragular">lorem ipsum</h4>
          </div>
        </div>
    </div>

      
    </div>
  </div>

  <!-- Add Group Modal -->
  <x-modal id="addGroupModal" title="Create Group">
    <form id="addGroupForm">
      <div class="form-infield">
        <x-text_label for="modal_group_name" :required="true">Group Name</x-text_label>
        <x-text_input type="text" id="modal_group_name" name="group_name" placeholder="Group Name" data-required="true" data-name="Group Name" autocomplete="off" />
        <div id="modal_group_name-error-container">
          <x-input-error :messages="$errors->get('group_name')" />
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
      <!-- Rows populated dynamically -->
    </div>
    <template id="groupRowTemplate">
      <div class="p-2 group-item d-flex align-items-center justify-content-between border-bottom">
        <div class="group-title d-flex align-items-center flex-grow-1" data-value="">
          <span class="group-text"></span>
          <form class="edit-group-form d-none w-100">
            <div class="d-flex">
              <x-text_input type="text" name="title" placeholder="Group name" class="group-input" value="" />
            </div>
          </form>
        </div>
        <div class="mx-2 group-actions">
          <button type="button" class="p-0 border-0 btn btn-link edit-group-btn me-2">
            <x-svg-icon name="edit-pen2" size="18" color="#adadad" />
          </button>
          <button type="button" class="p-0 border-0 btn btn-link delete-group-btn" data-bs-toggle="modal" data-bs-target="#deleteGroupModal">
            <x-svg-icon name="trash" size="18" color="#adadad" />
          </button>
        </div>
      </div>
    </template>
    <div class="mt-3 d-flex justify-content-end">
      <x-button type="button" class="bg-trans-btn" data-bs-dismiss="modal">Close</x-button>
    </div>
  </x-modal>

  <!-- Delete Group Modal -->
  <x-modal id="deleteGroupModal" title="Delete Group">
    <div class="my-3">
      <p class="h3-semibold" style="color:black;">Are you sure you want to delete the group "<span id="delete_group_name"></span>"?</p>
    </div>
    <div class="modal-footer">
      <x-button type="button" style="color:#BB1313; background-color:transparent; border:1px solid #BB1313;" data-bs-dismiss="modal">Cancel</x-button>
      <x-button type="button" id="confirmDeleteGroupBtn" style="background-color:#BB1313; color:#fff;">Delete</x-button>
    </div>
  </x-modal>

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
      const editGroupsModalEl = document.getElementById('editGroupModal');
      const editGroupsList = document.querySelector('#editGroupModal .groups-list');
      const groupRowTemplate = document.getElementById('groupRowTemplate');
      const deleteGroupModalEl = document.getElementById('deleteGroupModal');
      const deleteGroupNameSpan = document.getElementById('delete_group_name');
      const confirmDeleteGroupBtn = document.getElementById('confirmDeleteGroupBtn');
      let pendingDeleteValue = null;

      function setActiveType(selectedType) {
        typeButtons.forEach(btn => {
          const isActive = btn.dataset.type === selectedType;
          btn.classList.toggle('active', isActive);
        });

        questionBlocks.forEach(block => {
          const isMatch = block.dataset.type === selectedType;
          block.classList.toggle('d-none', !isMatch);

          const inputs = block.querySelectorAll('input, select, textarea, button');
          inputs.forEach(input => {
            if (isMatch) {
              input.removeAttribute('disabled');
            } else {
              if (input.type !== 'button' && input.type !== 'submit') {
                input.setAttribute('disabled', 'disabled');
              }
            }
          });
        });
      }

      typeButtons.forEach(btn => {
        btn.addEventListener('click', () => setActiveType(btn.dataset.type));
      });

      // Initialize default selection
      setActiveType('text');

      // Handle Add Group Modal submit (client-side add to select)
      const addGroupForm = document.getElementById('addGroupForm');
      const groupSelect = document.getElementById('group_name');
      addGroupForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        const input = document.getElementById('modal_group_name');
        const value = (input?.value || '').trim();
        if (!value) {
          input?.focus();
          return;
        }
        if (groupSelect) {
          const existingOption = Array.from(groupSelect.options).find(o => o.value === value);
          if (!existingOption) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            groupSelect.appendChild(option);
          }
          groupSelect.value = value;
        }
        if (typeof bootstrap !== 'undefined') {
          const modalEl = document.getElementById('addGroupModal');
          const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
          modal.hide();
        }
        input.value = '';
      });

      // Populate Edit Groups modal with current select options
      function renderGroupsList() {
        if (!groupSelect || !editGroupsList || !groupRowTemplate) return;
        editGroupsList.innerHTML = '';
        const options = Array.from(groupSelect.options);
        options.forEach(option => {
          const node = groupRowTemplate.content.cloneNode(true);
          const titleWrap = node.querySelector('.group-title');
          const spanText = node.querySelector('.group-text');
          const form = node.querySelector('.edit-group-form');
          const input = node.querySelector('.group-input');
          const editBtn = node.querySelector('.edit-group-btn');
          const deleteBtn = node.querySelector('.delete-group-btn');

          titleWrap.dataset.value = option.value;
          spanText.textContent = option.textContent;
          input.value = option.textContent;

          editBtn.addEventListener('click', function () {
            spanText.classList.add('d-none');
            form.classList.remove('d-none');
            input.focus();
            input.select();
          });

          input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
              e.preventDefault();
              const newTitle = input.value.trim();
              if (!newTitle) return;
              option.textContent = newTitle;
              spanText.textContent = newTitle;
              spanText.classList.remove('d-none');
              form.classList.add('d-none');
            } else if (e.key === 'Escape') {
              spanText.classList.remove('d-none');
              form.classList.add('d-none');
              input.value = option.textContent;
            }
          });

          deleteBtn.addEventListener('click', function () {
            pendingDeleteValue = option.value;
            if (deleteGroupNameSpan) deleteGroupNameSpan.textContent = option.textContent;
          });

          editGroupsList.appendChild(node);
        });
      }

      editGroupsModalEl?.addEventListener('show.bs.modal', renderGroupsList);

      // Confirm delete group
      confirmDeleteGroupBtn?.addEventListener('click', function () {
        if (!groupSelect || !pendingDeleteValue) return;
        const toRemove = Array.from(groupSelect.options).find(o => o.value === pendingDeleteValue);
        if (toRemove) {
          const wasSelected = groupSelect.value === pendingDeleteValue;
          toRemove.remove();
          if (wasSelected) {
            groupSelect.value = '';
          }
        }
        pendingDeleteValue = null;
        if (typeof bootstrap !== 'undefined' && deleteGroupModalEl) {
          const modal = bootstrap.Modal.getOrCreateInstance(deleteGroupModalEl);
          modal.hide();
        }
        renderGroupsList();
      });

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
    });
  </script>
@endpush