@extends('layouts.app')
@section('title', 'HIS | Users')
@section('content')
    <section>

        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h2-semibold" style="color:#35758C;">User Management</h2>
                <p class="h5-ragular" style="color:#ADADAD;">Manage user accounts, block users, and view user details.</p>
            </div>

            <div class="gap-3 d-flex align-items-center">
                <x-link_btn href="{{ route('users.blocked') }}"
                    style="background-color: transparent; color: #BB1313; border: 1px solid #BB1313;">
                    <x-svg-icon name="shield-block" size="20" />
                    <span>Blocked Users</span>
                </x-link_btn>

                <x-button style="background-color: #BB1313;" onclick="openNotificationModal()">
                    <x-svg-icon name="bell" size="20" />
                    <span>Send Notification</span>
                </x-button>

                <x-link_btn href="{{  route('admin.register') }}">
                    <x-svg-icon name="plus3" size="20" />
                    <span>Add new user</span>
                </x-link_btn>
            </div>
        </div>

        <div class="user-count">
            <h3 class="h5-ragular">Total Users</h3>
            <h2 class="h3-semibold">{{ $total_users }}</h2>
        </div>

        <div class="table-u-container">
            <div class="mb-3 filters-container w-100" data-url="{{ route('users.index') }}">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="w-25">
                        <x-search_input id="search_input" type="text" name="search" placeholder="Search user name..."
                            value="{{ request('search') }}" class="w-100" />
                    </div>
                    
                    <div style="visibility: hidden;" id="selection-controls">
                        <div class="flex-row gap-2 d-flex md-flex-col align-items-center">
                            <x-button type="button" class="bg-trans-btn selected-count">0 Employees selected</x-button>
                            <x-button type="button" style="background-color: #BB1313;" onclick="clearSelections()">Clear</x-button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead style="background:#F1F9FA;">
                        <tr>
                            <th style="width:5%;">
                                <input type="checkbox" id="selectAll" />
                            </th>
                            <th style="width:25%;">Name</th>
                            <th style="width:30%;">Email</th>
                            <th style="width:15%;">Role</th>
                            <th style="width:15%;">Status</th>
                            <th style="width:10%; color:#35758C;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td style="text-align: left;">
                                <input type="checkbox" class="userCheckbox" name="selected_users[]" value="{{ $user->id }}" />
                                </td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ ucfirst($user->role) }}</td>
                                <td>
                                    @if (!$user->deleted_at)
                                        <h4 class="h6-ragular card-status active">
                                            Active
                                        </h4>
                                    @endif
                                </td>
                                <td>
                                    <div class="gap-3 d-flex align-items-center">
                                        <a href="{{ route('users.edit', $user->id) }}">
                                            <x-svg-icon name="edit-pen2" size="18" color="#35758C" />
                                        </a>
                                        <button class="btn-nothing" data-bs-toggle="modal"
                                            data-bs-target="#deleteUserModal{{ $user->id }}">
                                            <x-svg-icon name="shield-block" size="18" color="#BB1313" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Block Modal for User -->
                            <x-modal id="deleteUserModal{{ $user->id }}" title="Block User">
                                <div class="my-3">
                                    <p class="h4-ragular" style="color:#000;">Are you sure you want to block the user
                                        "{{ $user->name }}"?</p>
                                </div>
                                <div class="modal-footer">
                                    <x-button type="button"
                                        style="color:#BB1313; background-color:transparent; border:1px solid #BB1313;"
                                        data-bs-dismiss="modal">Cancel</x-button>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <x-button type="submit" style="background-color:#BB1313; color:#fff;">Block</x-button>
                                    </form>
                                </div>
                            </x-modal>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">
                                    <x-data-not-found>No users found.</x-data-not-found>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

         <!-- Notification Modal -->
    <x-modal id="sendNotificationModal" title="Send Notification">
    <form action="" method="POST" id="notificationForm" novalidate>
      @csrf
      <input type="hidden" name="notification_token" value="{{ uniqid() }}">
      <div class="form-infield">
      <x-text_label for="notificationTitle" :required="true">Title</x-text_label>
      <x-text_input type="text" id="notificationTitle" name="title" placeholder="Enter notification title"
        data-required="true" data-name="Title" data-validate="Title" />
      <div id="notificationTitle-error-container">
        <x-input-error :messages="$errors->get('title')" />
      </div>
      </div>
      <div class="mb-3 form-infield">
      <x-text_label for="notificationBody" :required="true">Body</x-text_label>
      <x-textarea name="body" id="notificationBody" placeholder="Enter notification message" rows="4"
        data-required="true" data-name="Body" data-validate="Body" />
      <div id="notificationBody-error-container">
        <x-input-error :messages="$errors->get('body')" />
      </div>
      </div>
      <div id="receiverIdsContainer"></div>

      <div class="modal-footer">
        <x-button type="submit" style="background-color:#BB1313; color:#fff;" data-bs-dismiss="modal">Cancel</x-button>
      <x-button type="submit" class="px-4">Send</x-button>
      </div>
    </form>
    </x-modal>

        <div class="bottom-vid-pagination d-flex justify-content-between align-items-center">
            @if($users->count())
                <x-table-info :paginator="$users" />
                <x-pagination :paginator="$users" :appends="request()->query()" />
            @endif
        </div>

    </section>

@endsection

@push('scripts')
    <script src="{{ asset('js/filters.js') }}"></script>
    <script src="{{ asset('js/showToast.js') }}"></script>
    <script>
        // Initialize selected users from localStorage
        let selectedUsers = JSON.parse(localStorage.getItem('selected_users')) || [];

        // Get current page user IDs
        const getCurrentPageUserIds = () => {
            return Array.from(document.querySelectorAll('.userCheckbox')).map(chk => chk.value);
        };

        // Update UI based on selected users
        const updateSelectionUI = () => {
            const userCheckboxes = document.querySelectorAll('.userCheckbox');
            const selectAllCheckbox = document.getElementById('selectAll');
            const selectionControls = document.getElementById('selection-controls');
            const selectedCountButton = document.querySelector('.selected-count');

            // Update checkbox states
            userCheckboxes.forEach(chk => {
                chk.checked = selectedUsers.includes(chk.value);
            });

            // Update select all checkbox
            const currentPageUserIds = getCurrentPageUserIds();
            const allCurrentPageSelected = currentPageUserIds.length > 0 &&
                currentPageUserIds.every(userId => selectedUsers.includes(userId));
            selectAllCheckbox.checked = allCurrentPageSelected;

            // Update selected count and visibility
            const selectedCount = selectedUsers.length;
            selectedCountButton.textContent = `${selectedCount} User${selectedCount !== 1 ? 's' : ''} selected`;
            selectionControls.style.visibility = selectedCount > 0 ? 'visible' : 'hidden';
        };

        // Handle individual checkbox changes
        const handleCheckboxChange = (checkbox) => {
            const userId = checkbox.value;
            if (checkbox.checked) {
                if (!selectedUsers.includes(userId)) {
                    selectedUsers.push(userId);
                }
            } else {
                selectedUsers = selectedUsers.filter(id => id !== userId);
            }
            localStorage.setItem('selected_users', JSON.stringify(selectedUsers));
            updateSelectionUI();
        };

        // Handle select all checkbox
        const handleSelectAll = () => {
            const selectAllCheckbox = document.getElementById('selectAll');
            const currentPageUserIds = getCurrentPageUserIds();

            if (selectAllCheckbox.checked) {
                // Add all current page users to selection
                currentPageUserIds.forEach(userId => {
                    if (!selectedUsers.includes(userId)) {
                        selectedUsers.push(userId);
                    }
                });
            } else {
                // Remove all current page users from selection
                selectedUsers = selectedUsers.filter(userId => !currentPageUserIds.includes(userId));
            }

            localStorage.setItem('selected_users', JSON.stringify(selectedUsers));
            updateSelectionUI();
        };

        // Clear selections
        const clearSelections = () => {
            selectedUsers = [];
            localStorage.removeItem('selected_users');
            updateSelectionUI();
        };

        // Open notification modal
        function openNotificationModal() {
            if (selectedUsers.length === 0) {
                showToast("Please select at least one user to send a notification.", 'danger');
                return;
            }

            const container = document.getElementById('receiverIdsContainer');
            container.innerHTML = '';

            selectedUsers.forEach(userId => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'receiver_ids[]';
                hiddenInput.value = userId;
                container.appendChild(hiddenInput);
            });

            const modal = new bootstrap.Modal(document.getElementById('sendNotificationModal'));
            modal.show();
        }

        // Handle notification form submission
        const notificationForm = document.getElementById('notificationForm');
        notificationForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                if (response.ok) {
                    clearSelections();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('sendNotificationModal'));
                    modal.hide();
                    showToast('Notification sent successfully.', 'success');
                } else {
                    const errors = await response.json();
                    showToast('Failed to send notification: ' + (errors.message || 'Unknown error'), 'danger');
                }
            } catch (error) {
                showToast('An error occurred while sending the notification.', 'danger');
            }
        });

        // Initialize event listeners
        document.addEventListener('DOMContentLoaded', () => {
            const userCheckboxes = document.querySelectorAll('.userCheckbox');
            const selectAllCheckbox = document.getElementById('selectAll');

            userCheckboxes.forEach(chk => {
                chk.addEventListener('change', () => handleCheckboxChange(chk));
            });

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', handleSelectAll);
            }

            updateSelectionUI();
        });
    </script>
@endpush