@extends('layouts.admin')

@section('title', 'Submissions · Admin · Projects Hub')

@push('styles')
<style>
    .badge-info {
        background: var(--admin-indigo-bg);
        color: var(--admin-indigo);
    }
</style>
@endpush

@section('content')
    <div class="admin-page-header">
        <h1>Submissions</h1>
        <p>Read-only submission overview.</p>
    </div>

    <section class="data-card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Project</th>
                        <th>Submitter</th>
                        <th>Milestone</th>
                        <th>Status</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($submissions as $submission)
                        <tr>
                            <td><strong>{{ $submission['title'] }}</strong></td>
                            <td>{{ $submission['project'] }}</td>
                            <td>{{ $submission['submitter'] }}</td>
                            <td><span class="badge badge-neutral">{{ $submission['milestone'] }}</span></td>
                            <td>
                                <span @class([
                                    'badge',
                                    'badge-info' => $submission['status'] === 'Submitted',
                                    'badge-success' => $submission['status'] === 'Approved',
                                    'badge-warning' => $submission['status'] === 'Needs revision',
                                ])>{{ $submission['status'] }}</span>
                            </td>
                            <td>{{ $submission['submitted_at']?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">No submissions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
