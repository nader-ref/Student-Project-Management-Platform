<nav class="tabs-container dash-nav {{ $enrollmentMode === 'enrolled' ? 'enrolled-mode' : '' }}" id="main-tabs" aria-label="Dashboard sections">
    <button type="button" class="tab-btn active" data-tab="dashboard" title="Overview">
        <i class="fas fa-th-large"></i>
        <span class="tab-label">Overview</span>
    </button>

    @if ($enrollmentMode === 'enrolled')
        <button type="button" class="tab-btn" data-tab="my-project" title="My Project">
            <i class="fas fa-folder-open"></i>
            <span class="tab-label">My Project</span>
        </button>
        <button type="button" class="tab-btn" data-tab="team" title="Team">
            <i class="fas fa-users"></i>
            <span class="tab-label">Team</span>
        </button>
        <button type="button" class="tab-btn" data-tab="timeline" title="Timeline">
            <i class="fas fa-calendar-alt"></i>
            <span class="tab-label">Timeline</span>
            @if ($nextMilestone && $nextMilestone['days_left'] <= 14)
                <span class="form-badge optional-badge badge-inline">{{ $nextMilestone['days_left'] }}d</span>
            @endif
        </button>
        <button type="button" class="tab-btn" data-tab="progress" title="Progress">
            <i class="fas fa-chart-line"></i>
            <span class="tab-label">Progress</span>
        </button>
        <button type="button" class="tab-btn" data-tab="submissions" title="Submissions">
            <i class="fas fa-file-upload"></i>
            <span class="tab-label">Submissions</span>
            @if (isset($submissions) && $submissions->where('status', 'needs_revision')->where('submitted_by_user_id', auth()->id())->count() > 0)
                <span class="form-badge optional-badge badge-inline">!</span>
            @endif
        </button>
    @elseif ($enrollmentMode === 'pending')
        <button type="button" class="tab-btn" data-tab="pending-status" title="Application Status">
            <i class="fas fa-clock"></i>
            <span class="tab-label">Application</span>
        </button>
    @else
        <button type="button" class="tab-btn" data-tab="projects" title="Projects">
            <i class="fas fa-folder-open"></i>
            <span class="tab-label">Projects</span>
        </button>
        <button type="button" class="tab-btn" data-tab="request" title="Request">
            <i class="fas fa-file-signature"></i>
            <span class="tab-label">Request</span>
        </button>
        <button type="button" class="tab-btn" data-tab="idea" title="New Idea">
            <i class="far fa-lightbulb"></i>
            <span class="tab-label">New Idea</span>
        </button>
    @endif

    <button type="button" class="tab-btn" data-tab="message" title="{{ $enrollmentMode === 'enrolled' ? 'Messages' : 'Contact' }}">
        <i class="far fa-envelope"></i>
        <span class="tab-label">{{ $enrollmentMode === 'enrolled' ? 'Messages' : 'Contact' }}</span>
    </button>
</nav>
