@extends('layouts.app')
@section('title', 'HIS | Video - Declined')
@section('content')

<div class="gap-3 mb-4 d-flex align-items-center">
    <a href="{{ url()->previous() }}" class="arrow-back-btn">
    <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
    </a>
    <div>
    <h2 class="h2-semibold" style="color:#35758C;">Video Details (Declined Review)</h2>
    <p class="h5-ragular" style="color:#ADADAD;">Review the video and leave comments if needed</p>
    </div>
  </div>

  <section class="single-video-container">

    <!-- Video -->
    <div class="video-container">
        <video 
            controls 
            class="video-player"
            preload="none"
            @if($media->thumbnail_path)
                poster="{{ $media->thumbnail_path }}"
            @endif
        >
            <source src="{{ route('content.stream', ['id' => $media->id]) }}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>

    <!-- Video Title -->
    <div class="gap-3 mt-3 d-flex align-items-center">
    <h2 class="h3-semibold">{{ $media->title }}</h2>
    <h4 class="h6-ragular card-status {{ $media->status }}">
      {{ ucfirst($media->status) }}
    </h4>
    </div>

    <!-- Video Details -->
    <div class="d-flex justify-content-between align-items-center">
    <div class="gap-3 mt-2 d-flex align-items-center">
      <span class="h5-ragular" style="color:#ADADAD;">
      <x-format-duration :seconds="$media->duration" />
      </span>
      <span class="h5-ragular" style="color:#ADADAD;">Uploaded
      {{ $media->created_at->diffForHumans() }}</span>
      <span class="h5-ragular" style="color:#ADADAD;">by {{ $media->user->name }}</span>
    </div>
    </div>

    <!-- Video Description -->
    <div class="mt-3 single-discription">
        <div class="h5-ragular description-content-wrapper" id="description-content-{{ $media->id }}">
            <div class="quill-content description-text" id="description-text-{{ $media->id }}" style="white-space: pre-wrap;">{!! $media->description !!}</div>
            <button class="btn-nothing read-more-btn" id="read-more-desc-{{ $media->id }}" style="display:none; color: var(--primary-color); font-weight: 500; padding: 0; margin-top: 8px;">
                Read more
            </button>
            <button class="btn-nothing read-less-btn" id="read-less-desc-{{ $media->id }}" style="display:none; color: var(--primary-color); font-weight: 500; padding: 0; margin-top: 8px;">
                Show less
            </button>
        </div>
    </div>

    <!-- Video Mentions  -->
     <div class="gap-4 mt-3 d-flex align-items-center">
      <h3 class="h5-semibold">Mentioned to :</h3>

      <div class="flex-wrap gap-3 d-flex align-items-center">
        @if($media->mention && is_array(json_decode($media->mention, true)))
          @foreach(json_decode($media->mention, true) as $mentionedUser)
            <div style="padding: 10px 20px; border-radius: 32px; border: 1px solid #EDEDED;">
              <h3 class="h6-ragular" style="color:#7B7B7B;">{{ '@' . $mentionedUser }}</h3>
            </div>
          @endforeach
        @else
          <div style="padding: 10px 20px; border-radius: 32px; border: 1px solid #EDEDED;">
            <h3 class="h6-ragular" style="color:#7B7B7B;">No mentions</h3>
          </div>
        @endif
      </div>
     </div>

    <!-- Video Assetes -->
    <div class="gap-3 mt-4 d-flex align-items-center">
    <!-- PDF  -->
    @if($media->pdf)
    <a href="{{ $media->pdf }}" target="_blank" class="d-flex align-items-center justify-content-between w-100" style="border: 1px solid var(--Silver-100, #EDEDED); border-radius: 12px; padding: 12px 24px;">
      <span class="gap-2 d-flex align-items-center">
      <span style="background-color: #F1F9FA; border-radius: 8px; padding: 12px;">
      <x-svg-icon name="document" size="24" color="Black" />
      </span>
      <span class="d-flex flex-column">
      <span class="h5-semibold" style="color:#000;">Document</span>
      <span class="h5-ragular" style="color:#ADADAD;">PDF</span>
      </span>
      </span>
      <span> <x-svg-icon name="pop-out" size="24" color="Black" /></span>
    </a>
    @endif

    <!-- Image -->
    @if($media->image_path)
    <x-button type="button" data-bs-toggle="modal" data-bs-target="#viewImageModal"
      class="d-flex align-items-center justify-content-between w-100 btn-nothing"
      style="border: 1px solid var(--Silver-100, #EDEDED); border-radius: 12px; padding: 12px 24px;">
      <span class="gap-2 d-flex align-items-center">
      <span style="background-color: #F1F9FA; border-radius: 8px; padding: 12px;">
      <x-svg-icon name="document" size="24" color="Black" />
      </span>
      <span class="d-flex flex-column">
      <span class="h5-semibold" style="color:#000;">Image</span>
      <span class="h5-ragular" style="color:#ADADAD;">image</span>
      </span>
      </span>
      <span> <x-svg-icon name="expand" size="24" color="Black" /></span>
    </x-button>
    <x-modal id="viewImageModal" title="Image Preview" :image="$media->image_path" />
    @endif
    </div>

    
    <!-- Assigned Reviewers and Assign Button -->
    <div class="gap-3 mt-3 d-flex align-items-center">
        <h3 class="h5-semibold" style="color:black;">Assigned to :</h3>
        @php
            $reviewers_list = $assignedReviewers ?? collect();
            $total_reviewers = $reviewers_list->count();
            $visible_reviewers = $reviewers_list->take(2);
            $hidden_reviewers_count = $total_reviewers - $visible_reviewers->count();
        @endphp
        <div class="gap-1 d-flex align-items-center">
            @foreach($visible_reviewers as $reviewer)
                <img
                    src="{{ $reviewer->profile_image ?? asset('images/global/user-placeholder.png') }}"
                    class="user-profile-img"
                    style="width:40px;height:40px;border-radius:50%;object-fit:cover; border:2px solid #fff; box-shadow:0 0 2px #35758C;"
                    alt="{{ $reviewer->name }}"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    title="{{ $reviewer->name }}"
                />
            @endforeach
            @if($hidden_reviewers_count > 0)
                @php
                    $hidden_reviewers = $reviewers_list->slice(2);
                    $hidden_names = $hidden_reviewers->pluck('name')->implode(', ');
                @endphp
                <span
                    class="badge rounded-pill bg-secondary"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    title="{{ $hidden_names }}"
                    style="cursor:pointer;"
                >+{{ $hidden_reviewers_count }} more</span>
            @endif
        </div>
        @if(auth()->user()->hasRole('admin'))
            <div class="assign-to-btn" data-bs-toggle="modal"
                data-bs-target="#assignReviewerModal{{ $media->id }}" onclick="event.stopPropagation();">
                <x-svg-icon name="plus" size="12" color="#35758C" />
            </div>
        @endif
    </div>

    <!-- Assign Reviewer Modal -->
    <x-modal id="assignReviewerModal{{ $media->id }}" title="Assign Reviewers">
        <form action="{{ route('content.assignTo', $media->id) }}" method="POST" class="mt-3" novalidate>
            @csrf
            <div class="mb-4 form-infield assignment-select">
                <x-text_label for="reviewer_ids_{{ $media->id }}" :required="true">Assign to Reviewers</x-text_label>
                <p class="mb-1 h5-ragular" style="color:#adadad; margin-top:-6px;">You can select multiple reviewers</p>
                <select class="select2-reviewers form-control" name="reviewer_ids[]" multiple="multiple"
                    id="reviewer_ids_{{ $media->id }}" required>
                    @foreach($reviewers as $reviewer)
                        <option 
                            value="{{ $reviewer->id }}" 
                            data-profile-image="{{ $reviewer->profile_image ?? '' }}"
                            {{ $assignedReviewers->contains('id', $reviewer->id) ? 'selected' : '' }}>
                            {{ $reviewer->name }}
                        </option>
                    @endforeach
                </select>
                <div id="reviewer_ids-error-container_{{ $media->id }}">
                    <x-input-error :messages="$errors->get('reviewer_ids')" class="mt-2" />
                </div>
            </div>
            <div class="modal-footer">
                <x-button type="button" class="px-4 bg-trans-btn" data-bs-dismiss="modal">Cancel</x-button>
                <x-button type="submit" class="px-4">Assign Reviewers</x-button>
            </div>
        </form>
    </x-modal>

   <!-- Admin Comments -->
   <div class="mt-4">
        <h3 class="mb-2 h4-semibold">Admin Comments (Visible to Users)</h3>
        <x-comments 
            :commentsData="$adminComments"
            :mediaId="$media->id"
            :enableReplies="false"
            :enableLikes="false"
            :enableDelete="true"
            :showAddComment="true"
            commentRoute="AdminComment.add"
            deleteRoute="AdminComment.delete"
        />
    </div>
    
    <div class="gap-2 mt-5 d-flex justify-content-end align-items-center">
      <form action="{{ route('media.changeStatus', $media->id) }}" method="POST" style="display:inline;">
        @csrf
        <input type="hidden" name="status" value="inreview">
        <x-button type="submit">Next (In Review)</x-button>
      </form>
    </div>
</section>

@endsection

@push('scripts')
<script src="{{ asset('js/validations.js') }}"></script>
<script>
   $(document).ready(function () {
            // Initialize Select2 for reviewer assignment with better modal handling
            function initializeReviewerSelect2(modalId) {
                $(`#${modalId} .select2-reviewers`).select2({
                    placeholder: 'Select reviewers',
                    dropdownParent: $(`#${modalId}`),
                    width: '100%',
                    closeOnSelect: false,
                    allowClear: true,
                    templateResult: formatReviewer,
                    templateSelection: formatReviewerSelection
                });
            }

            // Format reviewer display in dropdown
            function formatReviewer(reviewer) {
                if (!reviewer.id) return reviewer.text;
                // Get the profile image from the option's data attribute
                var profileImage = $(reviewer.element).data('profile-image');
                var iconHtml = '';
                if (profileImage) {
                    iconHtml = `<img src="${profileImage}" class="user-profile-img" style="width:32px;height:32px;border-radius:50%;object-fit:cover;" alt="User Image" />`;
                } else {
                    iconHtml = `<div class="comment-container-user-icon">
                        <x-svg-icon name="user" size="18" color="#35758c" />
                    </div>`;
                }
                return $(
                    `<div class="gap-3 py-2 d-flex align-items-center">
                        ${iconHtml}
                        <div>
                            <div class="mb-0 h5-ragular">${reviewer.text}</div>
                            <div class="h6-ragular">Reviewer</div>
                        </div>
                    </div>`
                );
            }

            // Format reviewer selection display
            function formatReviewerSelection(reviewer) {
                if (!reviewer.id) return reviewer.text;
                return reviewer.text;
            }

            // Initialize Select2 when modal is shown
            $('.modal').on('shown.bs.modal', function () {
                const modalId = $(this).attr('id');
                if (modalId.startsWith('assignReviewerModal')) {
                    initializeReviewerSelect2(modalId);
                }
            });

            // Reset Select2 when modal is hidden
            $('.modal').on('hidden.bs.modal', function () {
                const modalId = $(this).attr('id');
                if (modalId.startsWith('assignReviewerModal')) {
                    $(`#${modalId} .select2-reviewers`).select2('destroy');
                }
            });

            // Add validation to ensure reviewer_ids[] is sent as array of integers
            $('form[action*="content.assignTo"]').on('submit', function (e) {
                var $select = $(this).find('.select2-reviewers');
                var selected = $select.val() || [];
                // Convert all values to integers
                var intSelected = selected.map(function(val) { return parseInt(val, 10); });
                // Remove all current options and add integer options
                $select.find('option').prop('selected', false);
                intSelected.forEach(function(val) {
                    $select.find('option[value="' + val + '"]').prop('selected', true);
                });
            });
        });
</script>
<script>
  document.querySelectorAll('.reply-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var replyId = this.getAttribute('data-reply-id');
      var replyInput = document.getElementById('reply-container-' + replyId);
      if (replyInput.style.display === 'none' || replyInput.style.display === '') {
        replyInput.style.display = 'block';
        var inputField = replyInput.querySelector('input, textarea');
        if (inputField) inputField.focus();
      } else {
        replyInput.style.display = 'none';
      }
    });
  });
</script>
@endpush

<script src="{{ asset('js/descriptonReadMore.js') }}"></script>