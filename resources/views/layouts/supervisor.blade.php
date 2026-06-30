<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Projects Hub · Supervisor')</title>
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
  
  <link rel="stylesheet" href="{{ asset('css/supervisorstyles/supervisor-theme.css') }}?v={{ filemtime(public_path('css/supervisorstyles/supervisor-theme.css')) }}">
  <link rel="stylesheet" href="{{ asset('css/supervisorstyles/supervisor-dashboard.css') }}?v={{ filemtime(public_path('css/supervisorstyles/supervisor-dashboard.css')) }}">
  <link rel="stylesheet" href="{{ asset('css/supervisorstyles/supervisor-dark-mode.css') }}?v={{ filemtime(public_path('css/supervisorstyles/supervisor-dark-mode.css')) }}">
  @stack('styles')
</head>

<body id="main-body" class="supervisor-portal">
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
    })();
  </script>
  @yield('content')
  @stack('scripts')
</body>

</html>
