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
                <x-link_btn href="" style="background-color: transparent; color: #BB1313; border: 1px solid #BB1313;">
                    <x-svg-icon name="shield-block" size="20" />
                    <span>Blocked Users</span>
                </x-link_btn>

                <x-link_btn href="{{  route('admin.register') }}">
                    <x-svg-icon name="plus3" size="20" />
                    <span>Add new user</span>
                </x-link_btn>

            </div>
        </div>

        <div class="user-count">
            <h3 class="h5-ragular">Total Users</h3>
            <h2 class="h3-semibold">1,234</h2>
        </div>

        <div class="table-u-container">
            <div class="mb-3 filters-container w-100" data-url="{{ route('users.index') }}">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="w-25">
                        <x-search_input id="search_input" type="text" name="search" placeholder="Search user name..."
                            value="{{ request('search') }}" class="w-100" />
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead style="background:#F1F9FA;">
                        <tr>
                            <th style="width:30%;">Name</th>
                            <th style="width:30%;">Email</th>
                            <th style="width:15%;">Role</th>
                            <th style="width:15%;">Status</th>
                            <th style="width:10%; color:#35758C;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
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

    </section>

@endsection

@push('scripts')
    <script src="{{ asset('js/filters.js') }}"></script>
@endpush