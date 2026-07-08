<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Projects Hub')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth/auth.css') }}?v={{ filemtime(public_path('css/auth/auth.css')) }}">
    @stack('styles')
</head>
<body class="auth-page auth-page--@yield('portal', 'student')">
    <div class="auth-shell">
        @yield('brand')
        <main class="auth-form-panel">
            <div class="auth-card">
                <div class="auth-card-accent"></div>
                <div class="auth-card-body">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.auth-form').forEach(function(form) {
                form.addEventListener('submit', function() {
                    const btn = form.querySelector('[type="submit"]');
                    if (!btn || btn.disabled) return;
                    btn.disabled = true;
                    btn.classList.add('is-loading');
                    const label = btn.dataset.loadingLabel || 'Please wait...';
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> ' + label;
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
