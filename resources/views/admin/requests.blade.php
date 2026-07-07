@extends('layouts.admin')

@section('title', 'Requests · Admin · Projects Hub')

@section('content')
    <div class="admin-page-header">
        <h1>Project Requests</h1>
        <p>Read-only request overview.</p>
    </div>

    <section class="data-card">
        <div class="table-wrap">
            <table class="data-table">
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
