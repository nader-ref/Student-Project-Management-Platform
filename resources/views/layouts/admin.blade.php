<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin · Projects Hub')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @php
        $studentStyles = [
            'base',
            'navbar',
            'tabs',
            'layout',
            'cards',
            'forms',
            'dark-mode-core',
            'acceptance',
            'form-pro',
            'student-dashboard',
            'responsive',
        ];
    @endphp

    @foreach ($studentStyles as $stylesheet)
        <link rel="stylesheet" href="{{ asset('css/studentstyles/' . $stylesheet . '.css') }}?v={{ filemtime(public_path('css/studentstyles/' . $stylesheet . '.css')) }}">
    @endforeach

    <link rel="stylesheet" href="{{ asset('css/adminstyles/admin-theme.css') }}?v={{ filemtime(public_path('css/adminstyles/admin-theme.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/adminstyles/admin-dashboard.css') }}?v={{ filemtime(public_path('css/adminstyles/admin-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/adminstyles/admin-dark-mode.css') }}?v={{ filemtime(public_path('css/adminstyles/admin-dark-mode.css')) }}">
    @stack('styles')
</head>

<body id="main-body" class="admin-portal">
    <script>
        (function() {
            const body = document.getElementById('main-body');
            if (!body) return;

            function applyDarkMode(isDark) {
                body.classList.toggle('dark-mode', isDark);
                const toggle = document.getElementById('dark-toggle');
                if (toggle) toggle.classList.toggle('off', !isDark);
            }

            function loadTheme() {
                const savedTheme = localStorage.getItem('theme');
                if (savedTheme !== null) {
                    applyDarkMode(savedTheme === 'dark');
                } else {
                    applyDarkMode(window.matchMedia('(prefers-color-scheme: dark)').matches);
                }
            }

            loadTheme();

            window.addEventListener('storage', function(e) {
                if (e.key === 'theme') loadTheme();
            });

            if (localStorage.getItem('theme') === null) {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                    applyDarkMode(e.matches);
                });
            }

            window.applyStudentDarkMode = applyDarkMode;
            window.applyAdminDarkMode = applyDarkMode;
        })();
    </script>

    <div class="dashboard admin-dashboard">
        @include('admin.partials.nav')

        <div class="content-panel">
            @yield('content')
        </div>

        <div class="dashboard-footer-accent" aria-hidden="true"></div>
    </div>

    @stack('scripts')
</body>

</html>
