@extends('layouts.app')
@section('title', 'HIS | Users')
@section('content')
    <section>

        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h2-semibold" style="color:#35758C;">User Management</h2>
                <p class="h5-ragular" style="color:#ADADAD;">Manage user accounts, block users, and view user details.</p>
            </div>

            <x-link_btn href="{{  route('admin.register') }}">
                <x-svg-icon name="plus3" size="20" />
                <span>Add new user</span>
            </x-link_btn>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->role }}</td>
                        <td>
                            @if ($user->deleted_at)
                                <span class="text-danger">Blocked</span>
                            @else
                                <span class="text-success">Active</span>
                            @endif
                        </td>
                        <td>
                            @if (!$user->deleted_at)
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure?')">Block</button>
                                </form>
                            @else
                                <form action="{{ route('users.restore', $user->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Restore</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

@endsection

@push('scripts')
@endpush