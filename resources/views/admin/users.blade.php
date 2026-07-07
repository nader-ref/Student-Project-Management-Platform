@extends('layouts.admin')

@section('title', 'Users · Admin · Projects Hub')

@section('content')
    <div class="admin-page-header">
        <h1>Users</h1>
        <p>Account overview with lifecycle actions.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <section class="data-card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>University number</th>
                        <th>Email</th>
                        <th>Email status</th>
                        <th>Role</th>
                        <th>Account status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user['name'] }}</td>
                            <td>{{ $user['university_number'] }}</td>
                            <td>
                                @if ($user['email'])
                                    {{ $user['email'] }}
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </td>
                            <td>
                                <span @class([
                                    'badge',
                                    'badge-success' => $user['email_status'] === 'Complete',
                                    'badge-pending' => $user['email_status'] === 'Pending',
                                ])>{{ $user['email_status'] }}</span>
                            </td>
                            <td><span class="badge badge-neutral">{{ $user['role'] }}</span></td>
                            <td>
                                <span @class([
                                    'badge',
                                    'badge-success' => $user['is_active'],
                                    'badge-danger' => ! $user['is_active'],
                                ])>{{ $user['status'] }}</span>
                            </td>
                            <td>
                                @if ($user['id'] !== auth()->id())
                                    <a href="{{ route('admin.users.reset-password', $user['id']) }}" class="action-btn">Reset password</a>
                                @endif
                                @if ($user['can_deactivate'])
                                    <form method="POST" action="{{ route('admin.users.deactivate', $user['id']) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="action-btn action-btn--danger">Deactivate</button>
                                    </form>
                                @elseif ($user['is_active'])
                                    @if ($user['id'] === auth()->id())
                                        <span class="text-muted">—</span>
                                    @endif
                                @else
                                    <form method="POST" action="{{ route('admin.users.activate', $user['id']) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="action-btn action-btn--success">Activate</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
