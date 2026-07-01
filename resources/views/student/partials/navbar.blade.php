@php
    $navName = auth()->user()?->name ?? 'Student';
    $navEmail = auth()->user()?->email ?? '';
    $navInitial = strtoupper(substr($navName, 0, 1));
    $onDashboard = request()->is('StudentDashboard');
@endphp

<header class="navbar-pro">
    <div class="navbar-pro-inner {{ $onDashboard ? 'navbar-pro-inner--dashboard' : '' }}">
        <a href="{{ url('/StudentDashboard') }}" class="navbar-brand">
            <span class="navbar-brand-icon"><i class="fas fa-cubes"></i></span>
            <span class="navbar-brand-text">
                <strong>Projects Hub</strong>
                <small>Student Portal</small>
            </span>
        </a>

        @unless ($onDashboard)
            <nav class="navbar-quick-links" aria-label="Main navigation">
                <a href="{{ url('/StudentDashboard') }}" class="navbar-link">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
                @if (($enrollmentMode ?? 'discovery') === 'enrolled')
                    <a href="{{ url('/StudentDashboard?tab=my-project') }}" class="navbar-link">
                        <i class="fas fa-folder-open"></i>
                        <span>My Project</span>
                    </a>
                    <a href="{{ url('/StudentDashboard/replay') }}" class="navbar-link {{ request()->is('StudentDashboard/replay') ? 'active' : '' }}">
                        <i class="fas fa-envelope"></i>
                        <span>Messages</span>
                    </a>
                @elseif (($enrollmentMode ?? 'discovery') === 'pending')
                    <a href="{{ url('/StudentDashboard?tab=pending-status') }}" class="navbar-link">
                        <i class="fas fa-clock"></i>
                        <span>Application</span>
                    </a>
                    <a href="{{ url('/StudentDashboard/acceptance') }}" class="navbar-link {{ request()->is('StudentDashboard/acceptance') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Requests</span>
                    </a>
                    <a href="{{ url('/StudentDashboard/replay') }}" class="navbar-link {{ request()->is('StudentDashboard/replay') ? 'active' : '' }}">
                        <i class="fas fa-envelope"></i>
                        <span>Messages</span>
                    </a>
                @else
                    <a href="{{ url('/StudentDashboard/acceptance') }}" class="navbar-link {{ request()->is('StudentDashboard/acceptance') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Requests</span>
                    </a>
                    <a href="{{ url('/StudentDashboard/acceptanceidea') }}" class="navbar-link {{ request()->is('StudentDashboard/acceptanceidea') ? 'active' : '' }}">
                        <i class="fas fa-lightbulb"></i>
                        <span>Ideas</span>
                    </a>
                    <a href="{{ url('/StudentDashboard/replay') }}" class="navbar-link {{ request()->is('StudentDashboard/replay') ? 'active' : '' }}">
                        <i class="fas fa-envelope"></i>
                        <span>Messages</span>
                    </a>
                @endif
            </nav>
        @endunless

        <div class="navbar-actions">
            @include('partials.navbar-user-menu', [
                'menuOnDashboard' => $onDashboard,
                'menuLogoutUrl' => route('logout'),
                'menuDashboardUrl' => url('/StudentDashboard'),
            ])
        </div>
    </div>
</header>
