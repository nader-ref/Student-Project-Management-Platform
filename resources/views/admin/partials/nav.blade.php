@php
    $adminUser = auth()->user();
    $adminName = $adminUser?->name ?? 'Administrator';
    $adminEmail = $adminUser?->email ?? '';
    $adminInitial = strtoupper(mb_substr($adminName, 0, 1));
    $adminUnread = $adminUser?->unreadNotifications()->count() ?? 0;
    $notificationsActive = request()->routeIs('notifications.index');
@endphp

<nav class="admin-navbar" aria-label="Admin navigation">
    <div class="admin-navbar-inner">
        <a href="{{ route('admin.dashboard') }}" class="admin-brand">
            <span class="admin-brand-icon"><i class="fas fa-shield-halved"></i></span>
            <span class="admin-brand-text">
                <strong>Projects Hub</strong>
                <small>Admin Command Center</small>
            </span>
        </a>

        <div class="admin-nav">
            <a href="{{ route('admin.dashboard') }}" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.dashboard')])>
                <i class="fas fa-gauge-high"></i> Dashboard
            </a>
            <a href="{{ route('admin.users') }}" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.users', 'admin.users.reset-password')])>
                <i class="fas fa-users"></i> Users
            </a>
            <a href="{{ route('admin.projects') }}" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.projects')])>
                <i class="fas fa-diagram-project"></i> Projects
            </a>
            <a href="{{ route('admin.requests') }}" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.requests')])>
                <i class="fas fa-inbox"></i> Requests
            </a>
            <a href="{{ route('admin.ideas') }}" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.ideas')])>
                <i class="fas fa-lightbulb"></i> Ideas
            </a>
            <a href="{{ route('admin.submissions') }}" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.submissions')])>
                <i class="fas fa-file-arrow-up"></i> Submissions
            </a>
            <a href="{{ route('admin.activity') }}" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.activity')])>
                <i class="fas fa-clock-rotate-left"></i> Activity
            </a>
        </div>

        <div class="admin-actions">
            <a href="{{ route('notifications.index') }}"
               class="admin-icon-btn {{ $notificationsActive ? 'active' : '' }}"
               aria-label="Notifications">
                <i class="fas fa-bell"></i>
                @if ($adminUnread > 0)
                    <span class="admin-notification-badge">{{ $adminUnread > 99 ? '99+' : $adminUnread }}</span>
                @endif
            </a>

            <div class="admin-user-menu">
                <button type="button" class="admin-user-chip" aria-expanded="false" aria-haspopup="true" aria-controls="admin-user-dropdown">
                    <span class="admin-user-avatar">{{ $adminInitial }}</span>
                    <span class="admin-user-info">
                        <strong>{{ $adminName }}</strong>
                        <span>{{ $adminEmail ?: 'Administrator' }}</span>
                    </span>
                    <i class="fas fa-chevron-down admin-user-chevron" aria-hidden="true"></i>
                </button>

                <div class="admin-user-dropdown" id="admin-user-dropdown" role="menu" hidden>
                    <div class="admin-dropdown-header">
                        <strong>{{ $adminName }}</strong>
                        <span>{{ $adminEmail ?: 'Administrator' }}</span>
                    </div>
                    <a href="{{ route('admin.supervisors.create') }}" class="admin-dropdown-item" role="menuitem">
                        <i class="fas fa-user-tie"></i>
                        <span>Create supervisor</span>
                    </a>
                    <a href="{{ route('admin.students.create') }}" class="admin-dropdown-item" role="menuitem">
                        <i class="fas fa-user-graduate"></i>
                        <span>Create student</span>
                    </a>
                    <button type="button" class="admin-dropdown-item" role="menuitem" id="admin-dark-toggle">
                        <i class="fas fa-moon"></i>
                        <span data-dark-label>Dark mode</span>
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="admin-dropdown-item is-danger" role="menuitem">
                            <i class="fas fa-right-from-bracket"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>

@push('scripts')
<script>
(function () {
    const menu = document.querySelector('.admin-user-menu');
    if (menu) {
        const trigger = menu.querySelector('.admin-user-chip');
        const dropdown = menu.querySelector('.admin-user-dropdown');

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
    if (darkToggle && window.applyAdminDarkMode) {
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
            window.applyAdminDarkMode(isDark);
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            syncLabel();
        });
    }
})();
</script>
@endpush
