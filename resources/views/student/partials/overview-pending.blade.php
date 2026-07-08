<div class="dash-kpi-grid">
    <div class="kpi-card pending">
        <div class="kpi-card-top">
            <span class="kpi-label">Status</span>
            <span class="kpi-icon"><i class="fas fa-hourglass-half"></i></span>
        </div>
        <div class="kpi-value">Pending</div>
        <div class="kpi-meta">Awaiting supervisor decision</div>
    </div>
    <div class="kpi-card total">
        <div class="kpi-card-top">
            <span class="kpi-label">Applications</span>
            <span class="kpi-icon"><i class="fas fa-file-alt"></i></span>
        </div>
        <div class="kpi-value">{{ ($pendingRequest ? 1 : 0) + ($pendingIdea ? 1 : 0) }}</div>
        <div class="kpi-meta">Active submission(s)</div>
    </div>
</div>

<div class="quick-actions-grid">
    <button type="button" class="quick-action-card dash-tab-trigger" data-tab="pending-status">
        <span class="quick-action-icon"><i class="fas fa-clipboard-list"></i></span>
        <div>
            <strong>View Application</strong>
            <span>See full submission details</span>
        </div>
    </button>
    <a href="{{ url('/StudentDashboard/replay') }}" class="quick-action-card">
        <span class="quick-action-icon"><i class="fas fa-envelope"></i></span>
        <div>
            <strong>Messages</strong>
            <span>Contact your supervisor</span>
        </div>
    </a>
    <button type="button" class="quick-action-card quick-action-card--settings dash-tab-trigger" data-tab="settings">
        <span class="quick-action-icon"><i class="fas fa-sliders-h"></i></span>
        <div>
            <strong>Settings</strong>
            <span>Password &amp; appearance</span>
        </div>
    </button>
</div>

<div class="activity-card">
    <h3><i class="fas fa-info-circle"></i> What happens next?</h3>
    <div class="activity-list">
        <div class="activity-item">
            <i class="fas fa-clock"></i>
            <span>Your supervisor will review your application.</span>
        </div>
        <div class="activity-item">
            <i class="fas fa-bell"></i>
            <span>Check the Application Status tab for updates.</span>
        </div>
        <div class="activity-item">
            <i class="fas fa-check"></i>
            <span>Once accepted, your dashboard switches to Project Workspace mode.</span>
        </div>
    </div>
</div>
