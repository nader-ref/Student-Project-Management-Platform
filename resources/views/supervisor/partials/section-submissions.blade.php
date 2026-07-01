<div class="tab-panel-header">
    <h2><i class="fas fa-file-upload"></i> Student Submissions</h2>
    <p>Review files uploaded by your project teams and provide feedback.</p>
</div>

@if ($submissions->isEmpty())
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-file-upload"></i></div>
        <h3>No submissions yet</h3>
        <p>Students will upload seminar deliverables here once enrolled in your projects.</p>
    </div>
@else
    <div class="request-list">
        @foreach ($submissions as $sub)
            <article class="request-item {{ $sub->status === 'approved' ? 'is-accepted' : ($sub->status === 'needs_revision' ? 'is-rejected' : 'is-pending') }}">
                <div class="request-item-body">
                    <div class="request-item-top">
                        <div>
                            <div class="request-ref">{{ $sub->project->name ?? 'Project #'.$sub->project_id }}</div>
                            <h3>{{ $sub->title }}</h3>
                        </div>
                        <span class="status-pill {{ $sub->status === 'approved' ? 'accepted' : ($sub->status === 'needs_revision' ? 'rejected' : 'pending') }}">
                            {{ ucwords(str_replace('_', ' ', $sub->status)) }}
                        </span>
                    </div>
                    <div class="request-meta-grid">
                        <div class="meta-block">
                            <label>Student</label>
                            <span>{{ $sub->submittedBy?->name ?? $sub->student_name }}</span>
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
                    </div>
                    @if ($sub->notes)
                        <div class="meta-block team-block">
                            <label>Student Notes</label>
                            <span>{{ $sub->notes }}</span>
                        </div>
                    @endif
                    <form action="{{ url('/supervisor/submission/review') }}" method="POST" class="reply-form-inline">
                        @csrf
                        <input type="hidden" name="submission_id" value="{{ $sub->id }}">
                        <div class="form-grid">
                            <div class="form-field form-field-pro">
                                <label>Review Status</label>
                                <select name="status" required>
                                    <option value="submitted" {{ $sub->status === 'submitted' ? 'selected' : '' }}>Submitted</option>
                                    <option value="approved" {{ $sub->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="needs_revision" {{ $sub->status === 'needs_revision' ? 'selected' : '' }}>Needs Revision</option>
                                </select>
                            </div>
                            <div class="form-field form-field-pro">
                                <label>Feedback</label>
                                <input type="text" name="supervisor_feedback" value="{{ $sub->supervisor_feedback }}" placeholder="Optional feedback for the student">
                            </div>
                        </div>
                        <div class="form-pro-actions" style="padding: 0; margin-top: 0.75rem; gap: 0.5rem;">
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
