@php
    $navName = Session::get('name') ?? 'Supervisor';
    $navEmail = Session::get('email') ?? '';
    $navInitial = strtoupper(substr($navName, 0, 1));
    $onDashboard = request()->is('supervisorDashboard');
@endphp

<header class="navbar-pro">
    <div class="navbar-pro-inner navbar-pro-inner--dashboard">
        <a href="{{ url('/supervisorDashboard') }}" class="navbar-brand">
            <span class="navbar-brand-icon"><i class="fas fa-cubes"></i></span>
            <span class="navbar-brand-text">
                <strong>Projects Hub</strong>
                <small>Supervisor Portal</small>
            </span>
        </a>

        <div class="navbar-actions">
            @include('partials.navbar-user-menu', [
                'menuOnDashboard' => $onDashboard,
                'menuLogoutUrl' => url('/supervisorDashboard/logout'),
                'menuDashboardUrl' => url('/supervisorDashboard'),
            ])
        </div>
    </div>
</header>
