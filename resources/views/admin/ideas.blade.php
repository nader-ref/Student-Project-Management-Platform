@extends('layouts.admin')

@section('title', 'Ideas · Admin · Projects Hub')

@section('content')
    @include('admin.partials.page-hero', [
        'title' => 'Project Ideas',
        'description' => 'Read-only idea overview.',
        'breadcrumb' => '<span>Admin</span><span class="sep">/</span><span>Ideas</span>',
    ])

    <section class="data-card">
        <div class="data-card-header-bar">
            <h2><i class="fas fa-lightbulb"></i> Idea submissions</h2>
            <span>{{ count($ideas) }} total</span>
        </div>
        <div class="table-wrap">
            <table class="data-table data-table--compact">
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
