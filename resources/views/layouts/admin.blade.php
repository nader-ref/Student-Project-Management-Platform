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
    <link rel="stylesheet" href="{{ asset('css/admin/admin.css') }}?v={{ filemtime(public_path('css/admin/admin.css')) }}">
    @stack('styles')
</head>
<body id="main-body" class="admin-portal">
    <script>
        (function () {
            const body = document.getElementById('main-body');
            if (!body) return;

            function applyDarkMode(isDark) {
                body.classList.toggle('dark-mode', isDark);
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

            window.addEventListener('storage', function (e) {
                if (e.key === 'theme') loadTheme();
            });

            if (localStorage.getItem('theme') === null) {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
                    applyDarkMode(e.matches);
                });
            }

            window.applyAdminDarkMode = applyDarkMode;
        })();
    </script>

    <div class="admin-shell">
        @include('admin.partials.nav')

        <main class="admin-main">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
