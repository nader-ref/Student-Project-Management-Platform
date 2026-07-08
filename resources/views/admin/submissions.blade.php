@extends('layouts.admin')

@section('title', 'Submissions · Admin · Projects Hub')

@section('content')
    @include('admin.partials.page-hero', [
        'title' => 'Submissions',
        'description' => 'Read-only submission overview.',
        'breadcrumb' => '<span>Admin</span><span class="sep">/</span><span>Submissions</span>',
    ])

    <section class="data-card">
        <div class="data-card-header-bar">
            <h2><i class="fas fa-file-arrow-up"></i> Uploaded deliverables</h2>
            <span>{{ count($submissions) }} total</span>
        </div>
        <div class="table-wrap">
            <table class="data-table data-table--compact">
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
                                    'badge-info' => $submission['status'] === 'Pending Review',
                                    'badge-success' => $submission['status'] === 'Approved',
                                    'badge-warning' => $submission['status'] === 'Revision Required',
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
