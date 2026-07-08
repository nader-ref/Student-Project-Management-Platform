@php
    $mode = $enrollmentMode ?? 'discovery';
    $dashboardUrl = url('/StudentDashboard');
    $onDashboard = $onDashboard ?? request()->is('StudentDashboard');
    $revisionCount = isset($submissions)
        ? $submissions->where('status', 'needs_revision')->where('submitted_by_user_id', auth()->id())->count()
        : 0;
    $timelineBadge = ($nextMilestone ?? null) && $nextMilestone['days_left'] <= 14
        ? $nextMilestone['days_left'] . 'd'
        : null;
@endphp

<nav class="navbar-portal-nav" id="portal-nav" aria-label="Portal navigation">
    @if ($mode === 'enrolled')
        @if ($onDashboard)
            <button type="button" class="navbar-link portal-nav-trigger" data-tab="dashboard">
                <i class="fas fa-th-large"></i><span>Overview</span>
            </button>
        @else
            <a href="{{ $dashboardUrl }}" class="navbar-link">
                <i class="fas fa-th-large"></i><span>Overview</span>
            </a>
        @endif

        <div class="navbar-nav-group" data-nav-group="project">
            <button type="button" class="navbar-link navbar-menu-trigger" aria-expanded="false" aria-haspopup="true">
                <i class="fas fa-folder-open"></i><span>Project</span><i class="fas fa-chevron-down navbar-menu-chevron" aria-hidden="true"></i>
            </button>
            <div class="navbar-portal-dropdown" role="menu" hidden>
                @if ($onDashboard)
                    <button type="button" class="navbar-dropdown-item portal-nav-trigger" data-tab="my-project" role="menuitem"><i class="fas fa-folder-open"></i> My Project</button>
                    <button type="button" class="navbar-dropdown-item portal-nav-trigger" data-tab="team" role="menuitem"><i class="fas fa-users"></i> Team</button>
                    <button type="button" class="navbar-dropdown-item portal-nav-trigger" data-tab="timeline" role="menuitem">
                        <i class="fas fa-calendar-alt"></i> Timeline
                        @if ($timelineBadge)<span class="form-badge optional-badge badge-inline">{{ $timelineBadge }}</span>@endif
                    </button>
                @else
                    <a href="{{ $dashboardUrl }}?tab=my-project" class="navbar-dropdown-item" role="menuitem"><i class="fas fa-folder-open"></i> My Project</a>
                    <a href="{{ $dashboardUrl }}?tab=team" class="navbar-dropdown-item" role="menuitem"><i class="fas fa-users"></i> Team</a>
                    <a href="{{ $dashboardUrl }}?tab=timeline" class="navbar-dropdown-item" role="menuitem"><i class="fas fa-calendar-alt"></i> Timeline</a>
                @endif
            </div>
        </div>

        <div class="navbar-nav-group" data-nav-group="work">
            <button type="button" class="navbar-link navbar-menu-trigger" aria-expanded="false" aria-haspopup="true">
                <i class="fas fa-briefcase"></i><span>Work</span><i class="fas fa-chevron-down navbar-menu-chevron" aria-hidden="true"></i>
            </button>
            <div class="navbar-portal-dropdown" role="menu" hidden>
                @if ($onDashboard)
                    <button type="button" class="navbar-dropdown-item portal-nav-trigger" data-tab="progress" role="menuitem"><i class="fas fa-chart-line"></i> Progress</button>
                    <button type="button" class="navbar-dropdown-item portal-nav-trigger" data-tab="submissions" role="menuitem">
                        <i class="fas fa-file-upload"></i> Submissions
                        @if ($revisionCount > 0)<span class="form-badge optional-badge badge-inline">!</span>@endif
                    </button>
                @else
                    <a href="{{ $dashboardUrl }}?tab=progress" class="navbar-dropdown-item" role="menuitem"><i class="fas fa-chart-line"></i> Progress</a>
                    <a href="{{ $dashboardUrl }}?tab=submissions" class="navbar-dropdown-item" role="menuitem"><i class="fas fa-file-upload"></i> Submissions</a>
                @endif
            </div>
        </div>

        @if ($onDashboard)
            <button type="button" class="navbar-link portal-nav-trigger" data-tab="message">
                <i class="fas fa-envelope"></i><span>Messages</span>
            </button>
        @else
            <a href="{{ url('/StudentDashboard/replay') }}" class="navbar-link {{ request()->is('StudentDashboard/replay') ? 'active' : '' }}">
                <i class="fas fa-envelope"></i><span>Messages</span>
            </a>
        @endif

    @elseif ($mode === 'pending')
        @if ($onDashboard)
            <button type="button" class="navbar-link portal-nav-trigger" data-tab="dashboard">
                <i class="fas fa-th-large"></i><span>Overview</span>
            </button>
            <button type="button" class="navbar-link portal-nav-trigger" data-tab="pending-status">
                <i class="fas fa-clock"></i><span>Application</span>
            </button>
            <button type="button" class="navbar-link portal-nav-trigger" data-tab="message">
                <i class="fas fa-envelope"></i><span>Contact</span>
            </button>
        @else
            <a href="{{ $dashboardUrl }}" class="navbar-link"><i class="fas fa-th-large"></i><span>Overview</span></a>
            <a href="{{ $dashboardUrl }}?tab=pending-status" class="navbar-link"><i class="fas fa-clock"></i><span>Application</span></a>
            <a href="{{ $dashboardUrl }}?tab=message" class="navbar-link"><i class="fas fa-envelope"></i><span>Contact</span></a>
        @endif

        <a href="{{ url('/StudentDashboard/acceptance') }}" class="navbar-link {{ request()->is('StudentDashboard/acceptance') ? 'active' : '' }}">
            <i class="fas fa-clipboard-check"></i><span>Requests</span>
        </a>
        <a href="{{ url('/StudentDashboard/replay') }}" class="navbar-link {{ request()->is('StudentDashboard/replay') ? 'active' : '' }}">
            <i class="fas fa-inbox"></i><span>Inbox</span>
        </a>

    @else
        @if ($onDashboard)
            <button type="button" class="navbar-link portal-nav-trigger" data-tab="dashboard">
                <i class="fas fa-th-large"></i><span>Overview</span>
            </button>
        @else
            <a href="{{ $dashboardUrl }}" class="navbar-link"><i class="fas fa-th-large"></i><span>Overview</span></a>
        @endif

        <div class="navbar-nav-group" data-nav-group="project">
            <button type="button" class="navbar-link navbar-menu-trigger" aria-expanded="false" aria-haspopup="true">
                <i class="fas fa-folder-open"></i><span>Project</span><i class="fas fa-chevron-down navbar-menu-chevron" aria-hidden="true"></i>
            </button>
            <div class="navbar-portal-dropdown" role="menu" hidden>
                @if ($onDashboard)
                    <button type="button" class="navbar-dropdown-item portal-nav-trigger" data-tab="projects" role="menuitem"><i class="fas fa-search"></i> Browse Projects</button>
                    <button type="button" class="navbar-dropdown-item portal-nav-trigger" data-tab="request" role="menuitem"><i class="fas fa-file-signature"></i> Submit Request</button>
                    <button type="button" class="navbar-dropdown-item portal-nav-trigger" data-tab="idea" role="menuitem"><i class="fas fa-lightbulb"></i> New Idea</button>
                @else
                    <a href="{{ $dashboardUrl }}?tab=projects" class="navbar-dropdown-item" role="menuitem"><i class="fas fa-search"></i> Browse Projects</a>
                    <a href="{{ $dashboardUrl }}?tab=request" class="navbar-dropdown-item" role="menuitem"><i class="fas fa-file-signature"></i> Submit Request</a>
                    <a href="{{ $dashboardUrl }}?tab=idea" class="navbar-dropdown-item" role="menuitem"><i class="fas fa-lightbulb"></i> New Idea</a>
                @endif
            </div>
        </div>

        @if ($onDashboard)
            <button type="button" class="navbar-link portal-nav-trigger" data-tab="message">
                <i class="fas fa-envelope"></i><span>Contact</span>
            </button>
        @else
            <a href="{{ $dashboardUrl }}?tab=message" class="navbar-link"><i class="fas fa-envelope"></i><span>Contact</span></a>
        @endif

        <a href="{{ url('/StudentDashboard/acceptance') }}" class="navbar-link {{ request()->is('StudentDashboard/acceptance') ? 'active' : '' }}">
            <i class="fas fa-clipboard-check"></i><span>Requests</span>
        </a>
        <a href="{{ url('/StudentDashboard/acceptanceidea') }}" class="navbar-link {{ request()->is('StudentDashboard/acceptanceidea') ? 'active' : '' }}">
            <i class="fas fa-lightbulb"></i><span>Ideas</span>
        </a>
        <a href="{{ url('/StudentDashboard/replay') }}" class="navbar-link {{ request()->is('StudentDashboard/replay') ? 'active' : '' }}">
            <i class="fas fa-inbox"></i><span>Inbox</span>
        </a>
    @endif
</nav>

@once
    @push('scripts')
    <script>
    (function() {
        document.querySelectorAll('.navbar-nav-group').forEach(function(group) {
            const trigger = group.querySelector('.navbar-menu-trigger');
            const menu = group.querySelector('.navbar-portal-dropdown');
            if (!trigger || !menu) return;

            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const willOpen = menu.hidden;

                document.querySelectorAll('.navbar-nav-group.is-open').forEach(function(other) {
                    if (other === group) return;
                    other.classList.remove('is-open');
                    const otherMenu = other.querySelector('.navbar-portal-dropdown');
                    const otherTrigger = other.querySelector('.navbar-menu-trigger');
                    if (otherMenu) otherMenu.hidden = true;
                    if (otherTrigger) otherTrigger.setAttribute('aria-expanded', 'false');
                });

                menu.hidden = !willOpen;
                trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                group.classList.toggle('is-open', willOpen);
            });

            menu.querySelectorAll('.navbar-dropdown-item, .portal-nav-trigger').forEach(function(item) {
                item.addEventListener('click', function() {
                    menu.hidden = true;
                    trigger.setAttribute('aria-expanded', 'false');
                    group.classList.remove('is-open');
                });
            });
        });

        document.addEventListener('click', function() {
            document.querySelectorAll('.navbar-nav-group.is-open').forEach(function(group) {
                group.classList.remove('is-open');
                const menu = group.querySelector('.navbar-portal-dropdown');
                const trigger = group.querySelector('.navbar-menu-trigger');
                if (menu) menu.hidden = true;
                if (trigger) trigger.setAttribute('aria-expanded', 'false');
            });
        });
    })();
    </script>
    @endpush
@endonce
