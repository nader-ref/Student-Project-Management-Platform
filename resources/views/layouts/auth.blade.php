<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Projects Hub')</title>
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
    @stack('scripts')
</body>
</html>
