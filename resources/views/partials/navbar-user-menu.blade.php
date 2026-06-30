@php
    $menuOnDashboard = $menuOnDashboard ?? false;
    $menuLogoutUrl = $menuLogoutUrl ?? url('/Logout');
    $menuDashboardUrl = $menuDashboardUrl ?? url('/StudentDashboard');
@endphp

<div class="navbar-user-menu">
    <button
        type="button"
        class="navbar-user-chip navbar-user-trigger"
        aria-expanded="false"
        aria-haspopup="true"
        aria-controls="navbar-user-dropdown"
    >
        <span class="navbar-user-avatar">{{ $navInitial }}</span>
        <div class="navbar-user-info">
            <strong>{{ $navName }}</strong>
            <span>{{ $navEmail }}</span>
        </div>
        <i class="fas fa-chevron-down navbar-user-chevron" aria-hidden="true"></i>
    </button>

    <div class="navbar-user-dropdown" id="navbar-user-dropdown" role="menu" hidden>
        @if ($menuOnDashboard)
            <button type="button" class="navbar-dropdown-item dash-tab-trigger" data-tab="settings" role="menuitem">
                <i class="fas fa-sliders-h"></i>
                <span>Settings</span>
            </button>
        @else
            <a href="{{ $menuDashboardUrl }}?tab=settings" class="navbar-dropdown-item" role="menuitem">
                <i class="fas fa-sliders-h"></i>
                <span>Settings</span>
            </a>
        @endif
        <a href="{{ $menuLogoutUrl }}" class="navbar-dropdown-item is-danger" role="menuitem">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

@once
    @push('scripts')
    <script>
    (function() {
        document.querySelectorAll('.navbar-user-menu').forEach(function(menu) {
            const trigger = menu.querySelector('.navbar-user-trigger');
            const dropdown = menu.querySelector('.navbar-user-dropdown');
            if (!trigger || !dropdown) return;

            function closeMenu() {
                dropdown.hidden = true;
                trigger.setAttribute('aria-expanded', 'false');
                menu.classList.remove('is-open');
            }

            function openMenu() {
                document.querySelectorAll('.navbar-user-menu.is-open').forEach(function(other) {
                    if (other !== menu) {
                        other.classList.remove('is-open');
                        const d = other.querySelector('.navbar-user-dropdown');
                        const t = other.querySelector('.navbar-user-trigger');
                        if (d) d.hidden = true;
                        if (t) t.setAttribute('aria-expanded', 'false');
                    }
                });
                dropdown.hidden = false;
                trigger.setAttribute('aria-expanded', 'true');
                menu.classList.add('is-open');
            }

            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                if (dropdown.hidden) openMenu();
                else closeMenu();
            });

            dropdown.querySelectorAll('.navbar-dropdown-item').forEach(function(item) {
                item.addEventListener('click', function() {
                    closeMenu();
                });
            });
        });

        document.addEventListener('click', function() {
            document.querySelectorAll('.navbar-user-menu.is-open').forEach(function(menu) {
                menu.classList.remove('is-open');
                const dropdown = menu.querySelector('.navbar-user-dropdown');
                const trigger = menu.querySelector('.navbar-user-trigger');
                if (dropdown) dropdown.hidden = true;
                if (trigger) trigger.setAttribute('aria-expanded', 'false');
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.navbar-user-menu.is-open').forEach(function(menu) {
                    menu.classList.remove('is-open');
                    const dropdown = menu.querySelector('.navbar-user-dropdown');
                    const trigger = menu.querySelector('.navbar-user-trigger');
                    if (dropdown) dropdown.hidden = true;
                    if (trigger) trigger.setAttribute('aria-expanded', 'false');
                });
            }
        });
    })();
    </script>
    @endpush
@endonce
