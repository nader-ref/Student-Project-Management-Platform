@extends('layouts.admin')

@section('title', 'Requests · Admin · Projects Hub')

@section('content')
    @include('admin.partials.page-hero', [
        'title' => 'Project Requests',
        'description' => 'Read-only request overview.',
        'breadcrumb' => '<span>Admin</span><span class="sep">/</span><span>Requests</span>',
    ])

    <section class="data-card">
        <div class="data-card-header-bar">
            <h2><i class="fas fa-inbox"></i> Request queue</h2>
            <span>{{ count($requests) }} total</span>
        </div>
        <div class="table-wrap">
            <table class="data-table data-table--compact">
                <thead>
                    <tr>
                        <th>Request</th>
                        <th>Project</th>
                        <th>Requester</th>
                        <th>Members</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $request)
                        <tr>
                            <td><span class="request-code">{{ $request['code'] }}</span></td>
                            <td>{{ $request['project'] }}</td>
                            <td>{{ $request['requester'] }}</td>
                            <td>{{ $request['members'] }}</td>
                            <td>
                                <span @class([
                                    'badge',
                                    'badge-pending' => $request['status'] === 'Pending',
                                    'badge-success' => $request['status'] === 'Accepted',
                                    'badge-danger' => $request['status'] === 'Rejected',
                                ])>{{ $request['status'] }}</span>
                            </td>
                            <td>{{ $request['created_at']?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">No project requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
