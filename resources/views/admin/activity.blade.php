@extends('layouts.admin')

@section('title', 'Activity Log · Admin · Projects Hub')

@section('content')
    @include('admin.partials.page-hero', [
        'title' => 'Activity Log',
        'description' => 'Read-only audit trail of important administrative and workflow events.',
        'breadcrumb' => '<span>Admin</span><span class="sep">/</span><span>Activity</span>',
    ])

    <section class="data-card">
        <div class="data-card-header-bar">
            <h2><i class="fas fa-clock-rotate-left"></i> Audit trail</h2>
            <span>Read-only log</span>
        </div>
        <div class="table-wrap">
            <table class="data-table data-table--compact data-table--activity">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Actor</th>
                        <th>Action</th>
                        <th>Target / Subject</th>
                        <th>Description</th>
                        <th>Metadata</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="cell-time">{{ $log->created_at?->format('M d, Y H:i') ?? '—' }}</td>
                            <td>{{ $log->actorLabel() }}</td>
                            <td><span class="request-code">{{ $log->action }}</span></td>
                            <td>
                                @if ($log->targetUser)
                                    <div>{{ $log->targetLabel() }}</div>
                                @endif
                                @if ($log->subject_type && $log->subject_id)
                                    <div class="text-muted">{{ $log->subjectLabel() }}</div>
                                @elseif (! $log->targetUser)
                                    —
                                @endif
                            </td>
                            <td class="cell-description">{{ $log->description }}</td>
                            <td class="cell-metadata">{{ $log->metadataSummary() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">No activity logged yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="pagination-wrap">
                {{ $logs->links() }}
            </div>
        @endif
    </section>
@endsection
