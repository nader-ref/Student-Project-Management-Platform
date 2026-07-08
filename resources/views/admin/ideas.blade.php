@extends('layouts.admin')

@section('title', 'Ideas · Admin · Projects Hub')

@section('content')
    <div class="admin-page-header">
        <span class="admin-page-header-icon"><i class="fas fa-lightbulb"></i></span>
        <div>
            <h1>Project Ideas</h1>
            <p>Read-only idea overview.</p>
        </div>
    </div>

    <section class="data-card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Idea</th>
                        <th>Supervisor</th>
                        <th>Requester</th>
                        <th>Members</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ideas as $idea)
                        <tr>
                            <td><strong>{{ $idea['title'] }}</strong></td>
                            <td>{{ $idea['supervisor'] }}</td>
                            <td>{{ $idea['requester'] }}</td>
                            <td>{{ $idea['members'] }}</td>
                            <td>
                                <span @class([
                                    'badge',
                                    'badge-pending' => $idea['status'] === 'Pending',
                                    'badge-success' => $idea['status'] === 'Accepted',
                                    'badge-danger' => $idea['status'] === 'Rejected',
                                ])>{{ $idea['status'] }}</span>
                            </td>
                            <td>{{ $idea['created_at']?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">No project ideas found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
