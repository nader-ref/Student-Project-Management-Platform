@php
    $mySubmissions = $submissions->where('student_email', Session::get('email'));
    $teamSubmissions = $submissions->where('student_email', '!=', Session::get('email'));
@endphp

<div class="tab-panel-header">
    <h2><i class="fas fa-file-upload"></i> File Submissions</h2>
    <p>Upload seminar reports, presentations, and deliverables for your supervisor to review.</p>
</div>

<form method="POST" action="{{ url('/student/submission') }}" enctype="multipart/form-data" class="request-form-pro" style="margin-bottom: 1.5rem;">
    @csrf
    <div class="form-pro-card">
        <div class="form-pro-card-header">
            <span class="form-step-badge">01</span>
            <div>
                <h3>New Submission</h3>
                <p>PDF, Word, PowerPoint, or ZIP — max 10 MB</p>
            </div>
        </div>
        <div class="form-pro-card-body">
            <div class="form-grid">
                <div class="form-field form-field-pro">
                    <label><i class="fas fa-flag"></i> Milestone</label>
                    <select name="milestone" required>
                        @foreach ($milestoneLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field form-field-pro">
                    <label><i class="fas fa-heading"></i> Title</label>
                    <input type="text" name="title" required placeholder="Seminar 1 Report">
                </div>
            </div>
            <div class="form-field form-field-pro" style="margin-top: 1rem;">
                <label><i class="fas fa-paperclip"></i> File</label>
                <input type="file" name="file" required accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.rar">
            </div>
            <div class="form-field form-field-pro" style="margin-top: 1rem;">
                <label><i class="fas fa-comment"></i> Notes (optional)</label>
                <textarea name="notes" rows="3" placeholder="Brief description for your supervisor..."></textarea>
            </div>
            <div class="form-pro-actions" style="padding: 0; margin-top: 1rem;">
                <button type="submit" class="btn-primary"><i class="fas fa-upload"></i> Upload</button>
            </div>
        </div>
    </div>
</form>

<h3 style="font-size: 1rem; margin-bottom: 1rem; color: #0a2942;"><i class="fas fa-user"></i> My Uploads</h3>
@if ($mySubmissions->isEmpty())
    <div class="empty-state" style="margin-bottom: 1.5rem;">
        <div class="empty-state-icon"><i class="fas fa-file-upload"></i></div>
        <h3>No files uploaded yet</h3>
        <p>Submit your first deliverable using the form above.</p>
    </div>
@else
    <div class="request-list" style="margin-bottom: 1.5rem;">
        @foreach ($mySubmissions as $sub)
            <article class="request-item {{ $sub->status === 'approved' ? 'is-accepted' : ($sub->status === 'needs_revision' ? 'is-rejected' : 'is-pending') }}">
                <div class="request-item-body">
                    <div class="request-item-top">
                        <div>
                            <div class="request-ref">{{ $milestoneLabels[$sub->milestone] ?? $sub->milestone }}</div>
                            <h3>{{ $sub->title }}</h3>
                        </div>
                        <span class="status-pill {{ $sub->status === 'approved' ? 'accepted' : ($sub->status === 'needs_revision' ? 'rejected' : 'pending') }}">
                            {{ ucwords(str_replace('_', ' ', $sub->status)) }}
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
                    </div>
                    @if ($sub->supervisor_feedback)
                        <div class="reply-bubble">
                            <label>Supervisor Feedback</label>
                            {{ $sub->supervisor_feedback }}
                        </div>
                    @endif
                    <div class="empty-state-actions" style="margin-top: 0.75rem;">
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
    <h3 style="font-size: 1rem; margin-bottom: 1rem; color: #0a2942;"><i class="fas fa-users"></i> Team Uploads</h3>
    <div class="request-list">
        @foreach ($teamSubmissions as $sub)
            <article class="request-item">
                <div class="request-item-body">
                    <div class="request-item-top">
                        <div>
                            <div class="request-ref">{{ $sub->student_name }}</div>
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
