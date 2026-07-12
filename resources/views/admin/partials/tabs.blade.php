<div class="dash-nav-shell admin-tabs-shell">
    <nav class="tabs-container dash-nav admin-tabs" aria-label="Admin navigation">
        <a href="{{ route('admin.dashboard') }}" @class(['admin-tab-link', 'active' => request()->routeIs('admin.dashboard')]) title="Dashboard">
            <i class="fas fa-gauge-high"></i>
            <span class="tab-label">Dashboard</span>
        </a>
        <a href="{{ route('admin.users') }}" @class(['admin-tab-link', 'active' => request()->routeIs('admin.users', 'admin.users.reset-password')]) title="Users">
            <i class="fas fa-users"></i>
            <span class="tab-label">Users</span>
        </a>
        <a href="{{ route('admin.projects') }}" @class(['admin-tab-link', 'active' => request()->routeIs('admin.projects')]) title="Projects">
            <i class="fas fa-diagram-project"></i>
            <span class="tab-label">Projects</span>
        </a>
        <a href="{{ route('admin.requests') }}" @class(['admin-tab-link', 'active' => request()->routeIs('admin.requests')]) title="Requests">
            <i class="fas fa-inbox"></i>
            <span class="tab-label">Requests</span>
        </a>
        <a href="{{ route('admin.ideas') }}" @class(['admin-tab-link', 'active' => request()->routeIs('admin.ideas')]) title="Ideas">
            <i class="fas fa-lightbulb"></i>
            <span class="tab-label">Ideas</span>
        </a>
        <a href="{{ route('admin.submissions') }}" @class(['admin-tab-link', 'active' => request()->routeIs('admin.submissions')]) title="Submissions">
            <i class="fas fa-file-arrow-up"></i>
            <span class="tab-label">Submissions</span>
        </a>
        <a href="{{ route('admin.activity') }}" @class(['admin-tab-link', 'active' => request()->routeIs('admin.activity')]) title="Activity">
            <i class="fas fa-clock-rotate-left"></i>
            <span class="tab-label">Activity</span>
        </a>
    </nav>
</div>
