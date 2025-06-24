<!-- Main Modal for Editing Lead -->
<x-modal id="{{ $modalId }}" title="Edit Lead">
  @if(isset($showAssignForm) && $showAssignForm)
    <form action="{{ route('assign_lead.store') }}" method="POST" class="mt-3 mb-4" novalidate>
    @csrf
    <input type="hidden" name="lead_id" value="{{ $lead->id }}">
    <div class="form-infield mb-4 assignment-select">
      <x-text_label for="employee_ids_{{ $modalId }}" :required="true">Assign to</x-text_label>
      <p class="h5-ragular mb-1" style="color:#adadad; margin-top:-6px;">You can select up to 3 employees</p>
      <select class="select2-employees-modal form-control" name="employee_ids[]" multiple="multiple"
      id="employee_ids_{{ $modalId }}" required>
      @foreach($employees as $employee)
      <option value="{{ $employee->id }}">{{ $employee->name }}</option>
    @endforeach
      </select>
      <div id="employee_ids-error-container_{{ $modalId }}">
      <x-input-error :messages="$errors->get('employee_ids')" class="mt-2" />
      </div>
    </div>
    <div class="d-flex justify-content-center">
      <x-button type="submit" class="px-4">Save Assignment</x-button>
    </div>
    </form>
    <hr>
  @endif

  @if(auth()->user()->hasAnyRole(['CEO', 'Sales Admin']))
    <form action="{{ route('lead.update', $lead->id) }}" method="POST" class="mt-3" novalidate style="text-align:left;">
    @csrf
    @method('PUT')

    <div class="d-flex align-items-center gap-2">
      <h3 class="h3-ragular">Assigned to: </h3>
      @foreach($lead->assignments as $assignment)
      @if($loop->index < 3)
      <img src="{{ $assignment->employee->image ?? '/images/global/avatar.svg' }}"
      alt="{{ $assignment->employee ? $assignment->employee->name : 'Deleted User' }}"
      style="width:32px; height:32px; object-fit:cover; border-radius:50%; {{ !$loop->first ? 'margin-left:-8px;' : '' }}"
      data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $assignment->employee ? $assignment->employee->name : 'Deleted User' }}">
    @endif
    @endforeach
    </div>

    <div class="d-flex flex-sm-row flex-column align-items-center column-gap-4">
      <div class="form-infield">
      <x-text_label for="priority" :required="true">Priority</x-text_label>
      <x-select name="priority" id="priority" :options="['low' => 'Low', 'high' => 'High']"
        placeholder="Select priority" data-required="true" data-name="Priority" :selected="$lead->priority" />
      <div id="priority-error-container">
        <x-input-error :messages="$errors->get('priority')" class="mt-2" />
      </div>
      </div>
      <div class="form-infield">
      <x-text_label for="name" :required="true">Lead Name</x-text_label>
      <x-text_input type="text" name="name" id="name" value="{{ $lead->name }}" placeholder="Enter lead name"
        data-required="true" data-name="Lead Name" />
      <div id="name-error-container">
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
      </div>
      </div>
    </div>
    <div class="d-flex flex-sm-row flex-column align-items-center column-gap-4">
      <div class="form-infield">
      <x-text_label for="location" :required="true">Location</x-text_label>
      <x-text_input type="text" name="location" id="location" value="{{ $lead->location }}"
        placeholder="Enter Location" data-required="true" data-name="location" />
      <div id="location-error-container">
        <x-input-error :messages="$errors->get('location')" class="mt-2" />
      </div>
      </div>
      <div class="form-infield">
      <x-text_label for="lead_type_ids" :required="true">Lead Type</x-text_label>
      <select name="lead_type_ids[]" id="lead_type_ids" class="form-control select2-edit" multiple required>
        @foreach($leadTypes as $type)
      <option value="{{ $type->id }}" {{ $lead->types->contains($type->id) ? 'selected' : '' }}>
      {{ $type->name }}
      </option>
    @endforeach
      </select>
      <div id="lead_type_ids-error-container{{ $lead->id }}">
        <x-input-error :messages="$errors->get('lead_type_ids')" class="mt-2" />
      </div>
      </div>
    </div>

    <div class="form-infield">
      <x-text_label for="lead_project_ids" :required="true">Lead Project</x-text_label>
      <select name="lead_project_ids[]" id="lead_project_ids" class="form-control select2-edit" multiple required>
      @foreach($leadProjects as $project)
      <option value="{{ $project->id }}" {{ $lead->projects->contains($project->id) ? 'selected' : '' }}>
      {{ $project->name }}
      </option>
    @endforeach
      </select>
      <div id="lead_project_ids-error-container{{ $lead->id }}">
      <x-input-error :messages="$errors->get('lead_project_ids')" class="mt-2" />
      </div>
    </div>

    <div class="d-flex flex-sm-row flex-column align-items-center column-gap-4">
      <div class="form-infield">
      <x-text_label for="phone1" :required="true">Primary Phone</x-text_label>
      <x-text_input type="text" id="phone1" name="phone1" value="{{ $lead->phone1 }}"
        placeholder="Enter primary phone" data-required="true" data-name="Primary Phone" data-validate="phone" />
      <div id="phone1-error-container">
        <x-input-error :messages="$errors->get('phone1')" class="mt-2" />
      </div>
      </div>
      <div class="form-infield">
      <x-text_label for="phone2">Secondary Phone</x-text_label>
      <x-text_input type="text" id="phone2" name="phone2" value="{{ $lead->phone2 }}" placeholder="Secondary Phone"
        data-validate="phone" />
      <div id="phone2-error-container">
        <x-input-error :messages="$errors->get('phone2')" class="mt-2" />
      </div>
      </div>
    </div>

    <div class="form-infield mb-3">
      <x-text_label for="description">Description</x-text_label>
      <x-textarea class="form-control" name="description" id="description" placeholder="Enter description" :rows="3"
      value="{{ $lead->description }}" />
    </div>
    <!-- modal-footer -->
    <div class="modal-footer">
      <x-button type="button" class="bg-trans-btn px-4" data-bs-dismiss="modal">Cancel</x-button>
      <x-button type="submit" class="px-4">Save Changes</x-button>
    </div>
    </form>
  @endif

  <div class="past_comments mb-3 text-start">
    <form action="{{ route('lead.comment.store', $lead->id) }}" method="POST" class="mb-4">
      @csrf
      <div class="form-infield">
        <x-text_label for="comment">Comment</x-text_label>
        <x-textarea name="comment" id="comment" placeholder="Enter comment" :rows="3" />
      </div>
      <div class="d-flex justify-content-end mt-2">
        <x-button type="submit" class="px-4">Add comment</x-button>
      </div>
    </form>

    <h3 class="h3-semibold mb-2" style="color:#000;">Past Comment</h3>

    <div class="past_comments_card_container">
      @if($lead->comments && count($lead->comments) > 0)
      @foreach($lead->comments as $comment)
      <div class="past_comments_card">
      <div class="d-flex align-items-center gap-3">
      <img src="{{ $comment->user->image ?? asset('images/global/avatar.svg') }}" alt="avatar"
      style="width: 47px; height: 47px; border-radius:50%; object-fit: cover;">
      <div>
      <h3 class="h5-semibold" style="color: #000;">{{ $comment->user->name }}</h3>
      <h4 class="h6-ragular" style="color: #adadad;">{{ $comment->created_at->format('Y-m-d H:i:s') }}</h4>
      </div>
      </div>
      <p class="h3-ragular mt-3" style="word-break: break-word;">
      @if(strlen($comment->comment) > 30)
      {{ substr($comment->comment, 0, 30) }}...
      <button style="background-color: transparent; padding: 0; border: none; color:#b12028;"
      class="h4-semibold toggle-comment" data-target="fullComment-{{ $lead->id }}-{{ $comment->id }}"
      aria-expanded="false" aria-controls="fullComment-{{ $lead->id }}-{{ $comment->id }}">Read more</button>
    @else
      {{ $comment->comment }}
    @endif
      </p>
      </div>
    @endforeach
    @else
      <p class="text-muted">No comments available for this lead.</p>
    @endif
    </div>
  </div>

  <!-- Full Comment Boxes (Moved Outside past_comments_card_container) -->
  @if($lead->comments && count($lead->comments) > 0)
    @foreach($lead->comments as $comment)
    @if(strlen($comment->comment) > 30)
    <div id="fullComment-{{ $lead->id }}-{{ $comment->id }}" class="past_comments_card-full mt-2"
    style="display: none; width: 100%;">
    <p class="h6-ragular" style="word-break: break-word;">{{ $comment->comment }}</p>
    </div>
  @endif
  @endforeach
  @endif
</x-modal>

<!-- JavaScript to Toggle Full Comment Box -->
<script>
  document.querySelectorAll('.toggle-comment').forEach(button => {
    button.addEventListener('click', function () {
      const targetId = this.getAttribute('data-target');
      const fullCommentDiv = document.getElementById(targetId);
      const isExpanded = fullCommentDiv.style.display === 'block';

      // If the clicked comment is already expanded, collapse it
      if (isExpanded) {
        fullCommentDiv.style.display = 'none';
        this.textContent = 'Read more';
        this.setAttribute('aria-expanded', 'false');
        return; // Exit early since we're just collapsing the current comment
      }

      // If another comment is open, close it first
      document.querySelectorAll('.past_comments_card-full').forEach(div => {
        div.style.display = 'none';
      });
      document.querySelectorAll('.toggle-comment').forEach(btn => {
        btn.textContent = 'Read more';
        btn.setAttribute('aria-expanded', 'false');
      });

      // Expand the clicked comment
      fullCommentDiv.style.display = 'block';
      this.textContent = 'Read less';
      this.setAttribute('aria-expanded', 'true');
      fullCommentDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
  });
</script>