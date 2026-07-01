<div class="tabs-container dash-nav" id="main-tabs">
    <button class="tab-btn active" data-tab="dashboard">
        <i class="fas fa-th-large"></i> Overview
    </button>

    @if ($enrollmentMode === 'enrolled')
        <button class="tab-btn" data-tab="my-project">
            <i class="fas fa-folder-open"></i> My Project
        </button>
        <button class="tab-btn" data-tab="team">
            <i class="fas fa-users"></i> Team
        </button>
        <button class="tab-btn" data-tab="timeline">
            <i class="fas fa-calendar-alt"></i> Timeline
            @if ($nextMilestone && $nextMilestone['days_left'] <= 14)
                <span class="form-badge optional-badge badge-inline">{{ $nextMilestone['days_left'] }}d</span>
            @endif
        </button>
        <button class="tab-btn" data-tab="progress">
            <i class="fas fa-chart-line"></i> Progress
        </button>
        <button class="tab-btn" data-tab="submissions">
            <i class="fas fa-file-upload"></i> Submissions
            @if (isset($submissions) && $submissions->where('status', 'needs_revision')->where('submitted_by_user_id', auth()->id())->count() > 0)
                <span class="form-badge optional-badge badge-inline">!</span>
            @endif
        </button>
    @elseif ($enrollmentMode === 'pending')
        <button class="tab-btn" data-tab="pending-status">
            <i class="fas fa-clock"></i> Application Status
        </button>
    @else
        <button class="tab-btn" data-tab="projects">
            <i class="fas fa-folder-open"></i> Projects
        </button>
        <button class="tab-btn" data-tab="request">
            <i class="fas fa-file-signature"></i> Request
        </button>
        <button class="tab-btn" data-tab="idea">
            <i class="far fa-lightbulb"></i> New Idea
        </button>
    @endif

    <button class="tab-btn" data-tab="message">
        <i class="far fa-envelope"></i> {{ $enrollmentMode === 'enrolled' ? 'Messages' : 'Contact' }}
    </button>
</div>
