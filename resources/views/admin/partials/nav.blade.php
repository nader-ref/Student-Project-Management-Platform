@php
    $navName = auth()->user()?->name ?? 'Administrator';
    $navEmail = auth()->user()?->email ?? '';
    $navInitial = strtoupper(mb_substr($navName, 0, 1));
@endphp

<header class="navbar-pro">
    <div class="navbar-pro-inner navbar-pro-inner--dashboard">
        <a href="{{ route('admin.dashboard') }}" class="navbar-brand">
            <span class="navbar-brand-icon"><i class="fas fa-shield-halved"></i></span>
            <span class="navbar-brand-text">
                <strong>Projects Hub</strong>
                <small>Admin Command Center</small>
            </span>
        </a>

        <div class="navbar-actions">
            @include('partials.navbar-notifications')

            <div class="navbar-user-menu">
                <button type="button" class="navbar-user-chip navbar-user-trigger" aria-expanded="false" aria-haspopup="true" aria-controls="admin-user-dropdown">
                    <span class="navbar-user-avatar">{{ $navInitial }}</span>
                    <div class="navbar-user-info">
                        <strong>{{ $navName }}</strong>
                        <span>{{ $navEmail ?: 'Administrator' }}</span>
                    </div>
                    <i class="fas fa-chevron-down navbar-user-chevron" aria-hidden="true"></i>
                </button>

                <div class="navbar-user-dropdown" id="admin-user-dropdown" role="menu" hidden>
                    <a href="{{ route('admin.supervisors.create') }}" class="navbar-dropdown-item" role="menuitem">
                        <i class="fas fa-user-tie"></i>
                        <span>Create supervisor</span>
                    </a>
                    <a href="{{ route('admin.students.create') }}" class="navbar-dropdown-item" role="menuitem">
                        <i class="fas fa-user-graduate"></i>
                        <span>Create student</span>
                    </a>
                    <button type="button" class="navbar-dropdown-item" role="menuitem" id="admin-dark-toggle">
                        <i class="fas fa-moon"></i>
                        <span data-dark-label>Dark mode</span>
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="navbar-dropdown-item is-danger" role="menuitem">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="dash-nav-shell">
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

@push('scripts')
<script>
(function () {
    const menu = document.querySelector('.admin-dashboard .navbar-user-menu');
    if (menu) {
        const trigger = menu.querySelector('.navbar-user-trigger');
        const dropdown = menu.querySelector('.navbar-user-dropdown');
        if (!trigger || !dropdown) return;

        function close() {
            dropdown.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
            menu.classList.remove('is-open');
        }

        function open() {
            dropdown.hidden = false;
            trigger.setAttribute('aria-expanded', 'true');
            menu.classList.add('is-open');
        }

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdown.hidden ? open() : close();
        });

        document.addEventListener('click', close);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') close();
        });

        dropdown.addEventListener('click', function (e) {
            if (!e.target.closest('#admin-dark-toggle')) close();
            e.stopPropagation();
        });
    }

    const darkToggle = document.getElementById('admin-dark-toggle');
    const applyDark = window.applyAdminDarkMode || window.applyStudentDarkMode;
    if (darkToggle && applyDark) {
        function syncLabel() {
            const isDark = document.body.classList.contains('dark-mode');
            const label = darkToggle.querySelector('[data-dark-label]');
            const icon = darkToggle.querySelector('i');
            if (label) label.textContent = isDark ? 'Light mode' : 'Dark mode';
            if (icon) icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
        }
        syncLabel();
        darkToggle.addEventListener('click', function () {
            const isDark = !document.body.classList.contains('dark-mode');
            applyDark(isDark);
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            syncLabel();
        });
    }
})();
</script>
@endpush
