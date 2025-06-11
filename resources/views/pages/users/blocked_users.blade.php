@extends('layouts.app')
@section('title', 'HIS | Blocked Users')
@section('content')
  <section>
    <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="h2-semibold" style="color:#35758C;">Blocked Users</h2>
      <p class="h5-ragular" style="color:#ADADAD;">View and manage blocked user accounts.</p>
    </div>

    <div class="gap-3 d-flex align-items-center">
      <x-link_btn href="{{ route('users.index') }}">
      <x-svg-icon name="user" size="18" />
      <span>All Users</span>
      </x-link_btn>
    </div>
    </div>

    <div class="user-count">
    <h3 class="h5-ragular">Total Blocked Users</h3>
    <h2 class="h3-semibold">{{ $users->count() }}</h2>
    </div>

    <div class="table-u-container">
    <div class="mb-3 filters-container w-100" data-url="{{ route('users.blocked') }}">
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
        <h4 class="h6-ragular card-status blocked">
        Blocked
        </h4>
      </td>
      <td>
        <div class="gap-3 d-flex align-items-center">
        <button class="btn-nothing" data-bs-toggle="modal" data-bs-target="#unblockUserModal{{ $user->id }}">
        <x-svg-icon name="unblock" size="18" color="#35758C" />
        </button>
        </div>
      </td>
      </tr>

      <!-- Unblock Modal for User -->
      <x-modal id="unblockUserModal{{ $user->id }}" title="Unblock User">
      <div class="my-3">
        <p class="h4-ragular" style="color:#000;">Are you sure you want to unblock the user
        "{{ $user->name }}"?</p>
      </div>
      <div class="modal-footer">
        <x-button type="button" style="color:#35758C; background-color:transparent; border:1px solid #35758C;"
        data-bs-dismiss="modal">Cancel</x-button>
        <form action="{{ route('users.restore', $user->id) }}" method="POST">
        @csrf
        <x-button type="submit" style="background-color:#35758C; color:#fff;">Unblock</x-button>
        </form>
      </div>
      </x-modal>
      @empty
      <tr>
      <td colspan="5" class="text-center">
        <x-data-not-found>No blocked users found.</x-data-not-found>
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