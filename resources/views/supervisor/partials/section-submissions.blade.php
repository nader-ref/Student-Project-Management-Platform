@php
    $sortedSubmissions = $submissions->sortBy(fn ($sub) => $sub->isPending() ? 0 : ($sub->needsRevision() ? 1 : 2))->values();
    $pendingCount = $submissions->where('status', 'submitted')->count();
@endphp

<div class="tab-panel-header">
    <h2><i class="fas fa-file-upload"></i> Student Submissions</h2>
    <p>Review files uploaded by your project teams and provide feedback.</p>
    @if ($pendingCount > 0)
        <p class="tab-panel-alert tab-panel-alert--pending">
            <i class="fas fa-hourglass-half"></i> {{ $pendingCount }} submission{{ $pendingCount === 1 ? '' : 's' }} pending review
        </p>
    @endif
</div>

@if ($submissions->isEmpty())
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-file-upload"></i></div>
        <h3>No submissions yet</h3>
        <p>Students will upload seminar deliverables here once enrolled in your projects.</p>
    </div>
@else
    <div class="request-list">
        @foreach ($sortedSubmissions as $sub)
            <article class="request-item {{ $sub->isApproved() ? 'is-accepted' : ($sub->needsRevision() ? 'is-rejected' : 'is-pending') }}">
                <div class="request-item-body">
                    <div class="request-item-top">
                        <div>
                            <div class="request-ref">{{ $sub->project->name ?? 'Project #'.$sub->project_id }}</div>
                            <h3>{{ $sub->title }}</h3>
                        </div>
                        <span class="status-pill {{ $sub->isApproved() ? 'accepted' : ($sub->needsRevision() ? 'rejected' : 'pending') }}">
                            @if ($sub->isPending())
                                <i class="fas fa-hourglass-half"></i>
                            @elseif ($sub->needsRevision())
                                <i class="fas fa-exclamation-circle"></i>
                            @elseif ($sub->isApproved())
                                <i class="fas fa-check-circle"></i>
                            @endif
                            {{ $sub->statusLabel() }}
                        </span>
                    </div>
                    <div class="request-meta-grid">
                        <div class="meta-block">
                            <label>Student</label>
                            <span>{{ $sub->submittedBy?->name ?? '—' }}</span>
                        </div>
                        <div class="meta-block">
                            <label>Milestone</label>
                            <span>{{ $milestoneLabels[$sub->milestone] ?? $sub->milestone }}</span>
                        </div>
                        <div class="meta-block">
                            <label>File</label>
                            <span>{{ $sub->original_filename }}</span>
                        </div>
                        <div class="meta-block">
                            <label>Uploaded</label>
                            <span>{{ $sub->created_at?->format('M d, Y H:i') }}</span>
                        </div>
                        @if ($sub->reviewed_at)
                            <div class="meta-block">
                                <label>Last Reviewed</label>
                                <span>{{ $sub->reviewed_at->format('M d, Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                    @if ($sub->notes)
                        <div class="meta-block team-block">
                            <label>Student Notes</label>
                            <span>{{ $sub->notes }}</span>
                        </div>
                    @endif
                    @if ($sub->supervisor_feedback)
                        <div class="reply-bubble {{ $sub->needsRevision() ? 'reply-bubble--revision' : '' }}">
                            <label>{{ $sub->needsRevision() ? 'Revision Required — Current Feedback' : 'Current Feedback' }}</label>
                            {{ $sub->supervisor_feedback }}
                        </div>
                    @endif
                    <form action="{{ url('/supervisor/submission/review') }}" method="POST" class="review-form-panel">
                        @csrf
                        <input type="hidden" name="submission_id" value="{{ $sub->id }}">
                        <div class="form-grid">
                            <div class="form-field form-field-pro">
                                <label>Review Status</label>
                                <select name="status" required>
                                    <option value="submitted" {{ $sub->status === 'submitted' ? 'selected' : '' }}>Pending Review</option>
                                    <option value="approved" {{ $sub->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="needs_revision" {{ $sub->status === 'needs_revision' ? 'selected' : '' }}>Revision Required</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-field form-field-pro form-field-pro--spaced">
                            <label>
                                Feedback
                                <span class="form-badge required-badge field-required-badge">Required for revision</span>
                            </label>
                            <textarea name="supervisor_feedback" rows="4" placeholder="Provide feedback for the student. Required when marking as Revision Required.">{{ old('supervisor_feedback', $sub->supervisor_feedback) }}</textarea>
                        </div>
                        <div class="form-pro-actions form-pro-actions--compact">
                            <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Save Review</button>
                            <a href="{{ route('supervisor.submission.download', $sub) }}" class="btn-secondary">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </form>
                </div>
            </article>
        @endforeach
    </div>
@endif
