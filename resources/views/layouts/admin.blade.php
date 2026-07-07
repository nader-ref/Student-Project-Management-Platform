<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin · Projects Hub')</title>
    <link rel="stylesheet" href="{{ asset('css/admin/admin.css') }}?v={{ filemtime(public_path('css/admin/admin.css')) }}">
    @stack('styles')
</head>
<body>
    <div class="admin-shell">
        <header class="admin-topbar">
            <div class="admin-topbar-inner">
                <div class="admin-brand">
                    <span class="admin-brand-kicker">Projects Hub</span>
                    <span class="admin-brand-title">@yield('brand_title', 'Administration')</span>
                </div>
                <div class="admin-userbar">
                    <span class="admin-user-name">
                        @hasSection('user_name')
                            @yield('user_name')
                        @else
                            {{ auth()->user()->name }}
                        @endif
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="admin-logout">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        @include('admin.partials.nav')

        <main class="admin-main">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
