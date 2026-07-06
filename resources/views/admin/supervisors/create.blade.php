<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Supervisor · Admin · Projects Hub</title>
    <style>
        :root {
            --admin-navy: #0f172a;
            --admin-slate: #1e293b;
            --admin-accent: #6366f1;
            --admin-accent-hover: #4f46e5;
            --admin-bg: #f1f5f9;
            --admin-card: #ffffff;
            --admin-border: #e2e8f0;
            --admin-muted: #64748b;
            --admin-text: #0f172a;
            --admin-success: #059669;
            --admin-success-bg: #ecfdf5;
            --admin-danger: #dc2626;
            --admin-danger-bg: #fef2f2;
            --admin-radius: 14px;
            --admin-shadow: 0 1px 3px rgba(15, 23, 42, 0.08), 0 8px 24px rgba(15, 23, 42, 0.06);
        }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            background: var(--admin-bg);
            color: var(--admin-text);
            line-height: 1.5;
            min-height: 100vh;
        }
        a { color: inherit; text-decoration: none; }
        .admin-shell { min-height: 100vh; display: flex; flex-direction: column; }
        .admin-topbar {
            background: linear-gradient(135deg, var(--admin-navy) 0%, var(--admin-slate) 100%);
            color: #fff;
            padding: 0 24px;
            border-bottom: 3px solid var(--admin-accent);
        }
        .admin-topbar-inner {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            min-height: 64px;
            flex-wrap: wrap;
            padding: 12px 0;
        }
        .admin-brand { display: flex; flex-direction: column; gap: 2px; }
        .admin-brand-kicker {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #a5b4fc;
        }
        .admin-brand-title { font-size: 18px; font-weight: 800; }
        .admin-userbar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .admin-user-name { font-size: 14px; color: #cbd5e1; }
        .admin-logout {
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 999px;
            background: rgba(255,255,255,0.08);
            color: #fff;
            cursor: pointer;
            font-weight: 700;
            font-size: 13px;
            padding: 8px 16px;
            transition: background 0.15s ease;
        }
        .admin-logout:hover { background: rgba(255,255,255,0.16); }
        .admin-nav-wrap {
            background: #fff;
            border-bottom: 1px solid var(--admin-border);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .admin-nav {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            gap: 6px;
            overflow-x: auto;
            scrollbar-width: thin;
        }
        .admin-nav-link {
            display: inline-flex;
            align-items: center;
            padding: 14px 16px;
            font-size: 14px;
            font-weight: 700;
            color: var(--admin-muted);
            border-bottom: 3px solid transparent;
            white-space: nowrap;
            transition: color 0.15s ease, border-color 0.15s ease;
        }
        .admin-nav-link:hover { color: var(--admin-accent); }
        .admin-nav-link.is-active {
            color: var(--admin-accent);
            border-bottom-color: var(--admin-accent);
        }
        .admin-main {
            flex: 1;
            max-width: 1280px;
            width: 100%;
            margin: 0 auto;
            padding: 28px 24px 40px;
        }
        .admin-page-header { margin-bottom: 24px; }
        .admin-page-header h1 {
            margin: 0 0 6px;
            font-size: clamp(1.5rem, 2vw, 2rem);
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .admin-page-header p {
            margin: 0;
            color: var(--admin-muted);
            font-size: 15px;
        }
        .form-card {
            background: var(--admin-card);
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius);
            box-shadow: var(--admin-shadow);
            padding: 28px;
            max-width: 640px;
        }
        .form-grid { display: grid; gap: 18px; }
        .form-field label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 700;
        }
        .form-field input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--admin-border);
            border-radius: 10px;
            font-size: 15px;
            font-family: inherit;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .form-field input:focus {
            outline: none;
            border-color: var(--admin-accent);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        .form-field.has-error input { border-color: var(--admin-danger); }
        .field-error {
            margin-top: 6px;
            color: var(--admin-danger);
            font-size: 13px;
            font-weight: 600;
        }
        .alert {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .alert-success {
            background: var(--admin-success-bg);
            color: var(--admin-success);
            border: 1px solid #a7f3d0;
        }
        .alert-error {
            background: var(--admin-danger-bg);
            color: var(--admin-danger);
            border: 1px solid #fecaca;
        }
        .form-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 8px;
        }
        .btn-primary {
            border: 0;
            border-radius: 10px;
            background: var(--admin-accent);
            color: #fff;
            cursor: pointer;
            font-weight: 700;
            font-size: 15px;
            padding: 12px 20px;
            transition: background 0.15s ease;
        }
        .btn-primary:hover { background: var(--admin-accent-hover); }
        .btn-link {
            color: var(--admin-accent);
            font-weight: 700;
            font-size: 14px;
        }
        @media (max-width: 720px) {
            .admin-main { padding: 20px 16px 32px; }
            .admin-nav { padding: 0 16px; }
            .form-card { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="admin-shell">
        <header class="admin-topbar">
            <div class="admin-topbar-inner">
                <div class="admin-brand">
                    <span class="admin-brand-kicker">Projects Hub</span>
                    <span class="admin-brand-title">Administration</span>
                </div>
                <div class="admin-userbar">
                    <span class="admin-user-name">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="admin-logout">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        <nav class="admin-nav-wrap" aria-label="Admin navigation">
            <div class="admin-nav">
                <a href="{{ route('admin.dashboard') }}" class="admin-nav-link">Dashboard</a>
                <a href="{{ route('admin.users') }}" class="admin-nav-link">Users</a>
                <a href="{{ route('admin.projects') }}" class="admin-nav-link">Projects</a>
                <a href="{{ route('admin.requests') }}" class="admin-nav-link">Requests</a>
                <a href="{{ route('admin.ideas') }}" class="admin-nav-link">Ideas</a>
                <a href="{{ route('admin.submissions') }}" class="admin-nav-link">Submissions</a>
                <a href="{{ route('admin.supervisors.create') }}" class="admin-nav-link is-active">Create Supervisor</a>
            </div>
        </nav>

        <main class="admin-main">
            <div class="admin-page-header">
                <h1>Create Supervisor</h1>
                <p>Provision a new supervisor account with login access and a linked supervisor profile.</p>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">
                    Please correct the errors below and try again.
                </div>
            @endif

            <section class="form-card">
                <form method="POST" action="{{ route('admin.supervisors.store') }}" class="form-grid">
                    @csrf

                    <div @class(['form-field', 'has-error' => $errors->has('name')])>
                        <label for="name">Full name</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name">
                        @error('name')<div class="field-error">{{ $message }}</div>@enderror
                    </div>

                    <div @class(['form-field', 'has-error' => $errors->has('university_number')])>
                        <label for="university_number">University number</label>
                        <input id="university_number" name="university_number" type="text" value="{{ old('university_number') }}" required autocomplete="off">
                        @error('university_number')<div class="field-error">{{ $message }}</div>@enderror
                    </div>

                    <div @class(['form-field', 'has-error' => $errors->has('email')])>
                        <label for="email">Email <span style="color:var(--admin-muted);font-weight:400;">(optional)</span></label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email">
                        @error('email')<div class="field-error">{{ $message }}</div>@enderror
                    </div>

                    <div @class(['form-field', 'has-error' => $errors->has('password')])>
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password">
                        @error('password')<div class="field-error">{{ $message }}</div>@enderror
                    </div>

                    <div @class(['form-field', 'has-error' => $errors->has('password_confirmation')])>
                        <label for="password_confirmation">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                        @error('password_confirmation')<div class="field-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Create supervisor account</button>
                        <a href="{{ route('admin.users') }}" class="btn-link">Back to users</a>
                        <a href="{{ route('admin.dashboard') }}" class="btn-link">Dashboard</a>
                    </div>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
