@extends('layouts.admin')

@section('title', 'Activity Log · Admin · Projects Hub')

@section('content')
    <div class="admin-page-header">
        <h1>Activity Log</h1>
        <p>Read-only audit trail of important administrative and workflow events.</p>
    </div>

    <section class="data-card">
        <div class="table-wrap">
            <table class="data-table">
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
                            <td>{{ $log->created_at?->format('M d, Y H:i') ?? '—' }}</td>
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
                            <td>{{ $log->description }}</td>
                            <td>{{ $log->metadataSummary() }}</td>
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
