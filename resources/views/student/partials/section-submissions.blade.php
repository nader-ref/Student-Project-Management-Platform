@php
    $currentUserId = auth()->id();
    $mySubmissions = $submissions->where('submitted_by_user_id', $currentUserId);
    $teamSubmissions = $submissions->where('submitted_by_user_id', '!=', $currentUserId);
@endphp

<div class="tab-panel-header">
    <h2><i class="fas fa-file-upload"></i> File Submissions</h2>
    <p>Upload seminar reports, presentations, and deliverables for your supervisor to review.</p>
</div>

<form method="POST" action="{{ url('/student/submission') }}" enctype="multipart/form-data" class="request-form-pro submission-upload-form">
    @csrf

    @if ($errors->hasAny(['milestone', 'title', 'file', 'notes']))
        <div class="form-pro-alert error form-pro-alert--spaced">
            <i class="fas fa-exclamation-circle"></i>
            Please fix the errors below and try uploading again.
        </div>
    @endif

    <div class="form-pro-card">
        <div class="form-pro-card-header">
            <span class="form-step-badge">01</span>
            <div>
                <h3>New Submission</h3>
                <p>Upload one deliverable at a time for supervisor review.</p>
            </div>
        </div>
        <div class="form-pro-card-body">
            <div class="form-grid">
                <div class="form-field form-field-pro">
                    <label><i class="fas fa-flag"></i> Milestone</label>
                    <select name="milestone" required>
                        @foreach ($milestoneLabels as $key => $label)
                            <option value="{{ $key }}" {{ old('milestone') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('milestone')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-field form-field-pro">
                    <label>
                        <i class="fas fa-heading"></i> Title
                        <span class="form-badge required-badge field-required-badge">Required</span>
                    </label>
                    <input type="text" name="title" required placeholder="Seminar 1 Report" value="{{ old('title') }}">
                    @error('title')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="form-field form-field-pro form-field-pro--spaced">
                <span class="form-field-label" id="submission-file-label">
                    <i class="fas fa-paperclip"></i> File
                    <span class="form-badge required-badge field-required-badge">Required</span>
                </span>
                <div
                    class="file-upload-zone"
                    id="submission-file-zone"
                    role="group"
                    aria-labelledby="submission-file-label"
                >
                    <i class="fas fa-cloud-upload-alt" aria-hidden="true"></i>
                    <span class="file-upload-zone-text">Click or drag a file here</span>
                    <span class="file-upload-zone-hint">PDF, Word, PowerPoint, ZIP, or RAR — max 10 MB</span>
                    <span class="file-upload-filename" id="submission-file-name" hidden></span>
                    <input
                        type="file"
                        name="file"
                        required
                        accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.rar"
                        id="submission-file-input"
                        aria-labelledby="submission-file-label"
                    >
                </div>
                @error('file')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-field form-field-pro form-field-pro--spaced">
                <label><i class="fas fa-comment"></i> Notes <span class="form-badge optional-badge field-required-badge">Optional</span></label>
                <textarea name="notes" rows="3" placeholder="Brief description for your supervisor...">{{ old('notes') }}</textarea>
                @error('notes')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-pro-actions form-pro-actions--compact">
                <button type="submit" class="btn-primary" data-loading-label="Uploading..."><i class="fas fa-upload"></i> Upload</button>
            </div>
        </div>
    </div>
</form>

<h3 class="subsection-heading"><i class="fas fa-user"></i> My Uploads</h3>
@if ($mySubmissions->isEmpty())
    <div class="empty-state subsection-block">
        <div class="empty-state-icon"><i class="fas fa-file-upload"></i></div>
        <h3>No files uploaded yet</h3>
        <p>Submit your first deliverable using the form above.</p>
    </div>
@else
    <div class="request-list subsection-block">
        @foreach ($mySubmissions as $sub)
            <article class="request-item {{ $sub->isApproved() ? 'is-accepted' : ($sub->needsRevision() ? 'is-rejected' : 'is-pending') }}">
                <div class="request-item-body">
                    <div class="request-item-top">
                        <div>
                            <div class="request-ref">{{ $milestoneLabels[$sub->milestone] ?? $sub->milestone }}</div>
                            <h3>{{ $sub->title }}</h3>
                        </div>
                        <span class="status-pill {{ $sub->isApproved() ? 'accepted' : ($sub->needsRevision() ? 'rejected' : 'pending') }}">
                            @if ($sub->needsRevision())
                                <i class="fas fa-exclamation-circle"></i>
                            @endif
                            {{ $sub->statusLabel() }}
                        </span>
                    </div>
                    <div class="request-meta-grid">
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
                                <label>Reviewed</label>
                                <span>{{ $sub->reviewed_at->format('M d, Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                    @if ($sub->needsRevision())
                        <div class="reply-bubble reply-bubble--revision">
                            <label><i class="fas fa-redo"></i> Revision Required — Supervisor Feedback</label>
                            {{ $sub->supervisor_feedback }}
                        </div>
                    @elseif ($sub->supervisor_feedback)
                        <div class="reply-bubble">
                            <label>Supervisor Feedback</label>
                            {{ $sub->supervisor_feedback }}
                        </div>
                    @endif
                    <div class="empty-state-actions empty-state-actions--compact">
                        <a href="{{ route('student.submission.download', $sub) }}" class="btn-secondary">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@endif

@if ($teamSubmissions->isNotEmpty())
    <h3 class="subsection-heading"><i class="fas fa-users"></i> Team Uploads</h3>
    <div class="request-list">
        @foreach ($teamSubmissions as $sub)
            <article class="request-item">
                <div class="request-item-body">
                    <div class="request-item-top">
                        <div>
                            <div class="request-ref">{{ $sub->submittedBy?->name ?? '—' }}</div>
                            <h3>{{ $sub->title }}</h3>
                        </div>
                        <span class="status-pill pending">{{ $milestoneLabels[$sub->milestone] ?? $sub->milestone }}</span>
                    </div>
                    <div class="meta-block">
                        <label>File</label>
                        <span>{{ $sub->original_filename }}</span>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@endif
