@extends('layouts.app')
@section('title', 'HIS | Videos')
@section('content')

    <section>

        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h2-semibold" style="color:#35758C;">Content Management</h2>
                <p class="h5-ragular" style="color:#ADADAD;">Create, update, or save content as draft—all in one place .</p>
            </div>

            <div class="gap-3 d-flex align-items-center">
                @if(!auth()->user()->hasRole('reviewer'))
                <x-link_btn href="{{  route('content.store') }}">
                    <x-svg-icon name="content" size="20" />
                    <span>Add Video</span>
                </x-link_btn>
                <x-link_btn href="{{ route('articles.store') }}">
                    <x-svg-icon name="article" size="20" />
                    <span>Add Article</span>
                </x-link_btn>
                @endif
            </div>

        </div>

        <!-- Articles & Videos -->
        <div class="mt-4">
            @php
                $tabs = [
                    ['id' => 'videos', 'label' => 'Videos', 'route' => route('content.videos')],
                ];
                if (!auth()->user()->hasRole('reviewer')) {
                    $tabs[] = ['id' => 'articles', 'label' => 'Articles', 'route' => route('content.articles')];
                }
            @endphp
            <x-tabs_pages :tabs="$tabs" activeTab="videos" />
        </div>

        <div class="content-container">
            <div class="filters-container w-100" data-url="{{ route('content.videos') }}">
                <div class="d-flex justify-content-between align-items-center">

                    <div class="w-25">
                        <x-search_input id="search_input" type="text" name="search" placeholder="Search video name..."
                            value="{{ request('search') }}" class="w-100" />
                    </div>

                    <div class="gap-2 d-flex align-items-center">
                        @php
                            $filters = [
                                'category' => [
                                    'placeholder' => '-- Select Category --',
                                    'options' => $categories->mapWithKeys(fn($item) => [$item->name => ucwords(str_replace('_', ' ', $item->name))])->toArray()
                                ],
                                'status' => [
                                    'placeholder' => '-- Select status --',
                                    'options' => [
                                        'published' => 'Published',
                                        'pending' => 'Pending',
                                        'declined' => 'Declined',
                                        'in_review' => 'In review',
                                    ]
                                ],
                            ];
                        @endphp

                        @foreach($filters as $name => $data)
                            <x-filter_select name="{{ $name }}" class="form-control-select" :options="$data['options']"
                                placeholder="{{ $data['placeholder'] }}" :selected="request($name)">
                            </x-filter_select>
                        @endforeach

                        <x-button id="reset-filters">
                            <x-svg-icon name="refresh" size="16" /> Reset
                        </x-button>
                    </div>
                </div>
            </div>

            <div class="content-container-cards">
                @forelse ($media as $item)
                    <div class="content-container-card">
                        <div class="d-flex justify-content-between align-items-end w-100">
                            <div>
                                <h2 class="h4-semibold" style="color:black;">{{ $item->user->name }}</h2>
                                <span class="h6-ragular" style="color:#ADADAD;">Uploaded
                                    {{ $item->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="h6-ragular card-status {{ $item->status }}">
                                    {{ ucfirst($item->status) }}
                                </h4>
                            </div>
                        </div>

                        <a href="{{ route('content.video', ['id' => $item->id, 'status' => $item->status]) }}" class="w-100">
                            <div class="mt-3 content-container-card-img">
                                <img src="{{ $item->thumbnail_path}}" alt="{{ $item->title }}" />
                                <span class="c-v-span">Video</span>
                                <x-format-duration :seconds="$item->duration" class="c-d-span" />
                            </div>
                        </a>

                        <div class="video-card-content-content">
                            <div class="dashboard-video-card-content-content-top">
                                <h3 class="h5-semibold" style="margin-top:12px; line-height: 1.5em; color:black;">
                                    {{ $item->title }}
                                </h3>
                                <p class="h6-ragular" style="color:black;">{{ Str::words($item->description, 15, '...') }}</p>
                            </div>

                            @if($item->status === 'pending' || $item->status === 'inreview')
                                @if($item->status === 'pending' && auth()->user()->hasRole('admin'))
                                    <div class="gap-3 mt-3 d-flex align-items-center">
                                        <h3 class="h5-semibold" style="color:black;">Assigned to :</h3>
                                        @php
                                            $reviewers_list = $item->assigned_reviewers;
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
                                        <div class="assign-to-btn" data-bs-toggle="modal"
                                            data-bs-target="#assignReviewerModal{{ $item->id }}" onclick="event.stopPropagation();">
                                            <x-svg-icon name="plus" size="12" color="#35758C" />
                                        </div>
                                    </div>
                                @endif
                                <div class="mt-2 dashboard-video-card-content-content-down">
                                    <div class="gap-2 d-flex align-items-center">
                                        <a href="{{ route('content.edit', $item->id) }}" onclick="event.stopPropagation();">
                                            @if(!auth()->user()->hasRole('reviewer'))
                                            <x-svg-icon name="edit-pen2" size="12" color="Black" />
                                            @endif
                                        </a>
                                        <button class="btn-nothing delete-video-btn" data-bs-toggle="modal"
                                            data-bs-target="#deleteVideoModal{{ $item->id }}">
                                            @if(!auth()->user()->hasRole('reviewer'))
                                            <x-svg-icon name="trash" size="12" color="Black" />
                                            @endif
                                        </button>
                                    </div>
                                </div>
                            @elseif($item->status === 'published')
                                <div class="dashboard-video-card-content-content-down">
                                    <div class="gap-2 d-flex align-items-center">
                                        <a href="{{ route('content.edit', $item->id) }}" onclick="event.stopPropagation();">
                                            @if(!auth()->user()->hasRole('reviewer'))
                                            <x-svg-icon name="edit-pen2" size="12" color="Black" />
                                            @endif
                                        </a>
                                        <button class="btn-nothing delete-video-btn" data-bs-toggle="modal"
                                            data-bs-target="#deleteVideoModal{{ $item->id }}">
                                            @if(!auth()->user()->hasRole('reviewer'))
                                            <x-svg-icon name="trash" size="12" color="Black" />
                                            @endif
                                        </button>
                                    </div>
                                    <div class="gap-3 d-flex align-items-center">
                                        <div>
                                            <x-svg-icon name="eye" size="12" color="Black" />
                                            <span class="h6-ragular">{{ $item->views}}</span>
                                        </div>
                                        <div>
                                            <x-svg-icon name="message" size="12" color="Black" />
                                            <span class="h6-ragular">{{ $item->comments_count }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-5 text-center" style="grid-column: 1 / -1;">
                        <p class="h5-ragular" style="color:#ADADAD;">No videos found</p>
                    </div>
                @endforelse
            </div>
            </div>
            <div class="bottom-vid-pagination d-flex justify-content-between align-items-center">
                <!-- Table Info and Pagination -->
                @if($media->count())
                    <x-table-info :paginator="$media" />
                    <x-pagination :paginator="$media" :appends="request()->query()" />
                @endif
            </div>
            </div>

            <!-- Assign Reviewer Modals -->
            @foreach($media as $item)
                @if($item->status === 'pending')
                    <x-modal id="assignReviewerModal{{ $item->id }}" title="Assign Reviewers">
                        <form action="{{ route('content.assignTo', $item->id) }}" method="POST" class="mt-3" novalidate>
                            @csrf
                            <div class="mb-4 form-infield assignment-select">
                                <x-text_label for="reviewer_ids_{{ $item->id }}" :required="true">Assign to Reviewers</x-text_label>
                                <p class="mb-1 h5-ragular" style="color:#adadad; margin-top:-6px;">You can select multiple reviewers
                                </p>
                                <select class="select2-reviewers form-control" name="reviewer_ids[]" multiple="multiple"
                                    id="reviewer_ids_{{ $item->id }}" required>
                                    @foreach($reviewers as $reviewer)
                                        <option 
                                            value="{{ $reviewer->id }}" 
                                            data-profile-image="{{ $reviewer->profile_image ?? '' }}"
                                            {{ $item->assigned_reviewers->contains($reviewer->id) ? 'selected' : '' }}>
                                            {{ $reviewer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="reviewer_ids-error-container_{{ $item->id }}">
                                    <x-input-error :messages="$errors->get('reviewer_ids')" class="mt-2" />
                                </div>
                            </div>
                            <div class="modal-footer">
                                <x-button type="button" class="px-4 bg-trans-btn" data-bs-dismiss="modal">Cancel</x-button>
                                <x-button type="submit" class="px-4">Assign Reviewers</x-button>
                            </div>
                        </form>
                    </x-modal>
                @endif
            @endforeach

            <!-- Delete Video Modals -->
            @foreach($media as $item)
                <x-modal id="deleteVideoModal{{ $item->id }}" title="Delete Video">
                    <div class="my-3">
                        <p class="h3-semibold" style="color:black;">Are you sure you want to delete the video
                            "{{ $item->title }}"?
                        </p>
                    </div>
                    <div class="modal-footer">
                        <x-button type="button" class="px-4 bg-trans-btn" data-bs-dismiss="modal">Cancel</x-button>
                        @if(!auth()->user()->hasRole('reviewer'))
                        <form action="{{ route('content.destroy', $item->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" class="px-4 btn-danger">Delete</x-button>
                        </form>
                        @endif
                    </div>
                </x-modal>
            @endforeach


    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/filters.js') }}"></script>
    <script src="{{ asset('js/validations.js') }}"></script>
    <script src="{{ asset('js/tooltips.js') }}"></script>
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
@endpush