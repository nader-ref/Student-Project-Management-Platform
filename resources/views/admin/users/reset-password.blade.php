<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password · Admin · Projects Hub</title>
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
            --admin-warning: #d97706;
            --admin-warning-bg: #fffbeb;
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
        .summary-card,
        .form-card {
            background: var(--admin-card);
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius);
            box-shadow: var(--admin-shadow);
            padding: 24px 28px;
            max-width: 640px;
        }
        .summary-card { margin-bottom: 20px; }
        .summary-card h2 {
            margin: 0 0 16px;
            font-size: 16px;
            font-weight: 800;
        }
        .summary-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }
        .summary-item span {
            display: block;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--admin-muted);
            margin-bottom: 4px;
        }
        .summary-item strong { font-size: 15px; }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
        }
        .badge-success { background: var(--admin-success-bg); color: var(--admin-success); }
        .badge-pending { background: var(--admin-warning-bg); color: var(--admin-warning); }
        .badge-danger { background: var(--admin-danger-bg); color: var(--admin-danger); }
        .badge-neutral { background: #f1f5f9; color: #475569; }
        .note {
            margin: 0 0 20px;
            padding: 14px 16px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            background: var(--admin-warning-bg);
            color: #92400e;
            border: 1px solid #fcd34d;
        }
        .note--inactive {
            background: var(--admin-danger-bg);
            color: var(--admin-danger);
            border-color: #fecaca;
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
            .form-card, .summary-card { padding: 20px; }
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
                <a href="{{ route('admin.users') }}" class="admin-nav-link is-active">Users</a>
                <a href="{{ route('admin.projects') }}" class="admin-nav-link">Projects</a>
                <a href="{{ route('admin.requests') }}" class="admin-nav-link">Requests</a>
                <a href="{{ route('admin.ideas') }}" class="admin-nav-link">Ideas</a>
                <a href="{{ route('admin.submissions') }}" class="admin-nav-link">Submissions</a>
                <a href="{{ route('admin.supervisors.create') }}" class="admin-nav-link">Create Supervisor</a>
                <a href="{{ route('admin.students.create') }}" class="admin-nav-link">Create Student</a>
            </div>
        </nav>

        <main class="admin-main">
            <div class="admin-page-header">
                <h1>Reset Password</h1>
                <p>Set a temporary password for {{ $user['name'] }}.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-error">
                    Please correct the errors below and try again.
                </div>
            @endif

            <section class="summary-card">
                <h2>User summary</h2>
                <div class="summary-grid">
                    <div class="summary-item">
                        <span>Name</span>
                        <strong>{{ $user['name'] }}</strong>
                    </div>
                    <div class="summary-item">
                        <span>University number</span>
                        <strong>{{ $user['university_number'] }}</strong>
                    </div>
                    <div class="summary-item">
                        <span>Role</span>
                        <strong><span class="badge badge-neutral">{{ $user['role'] }}</span></strong>
                    </div>
                    <div class="summary-item">
                        <span>Account status</span>
                        <strong>
                            <span @class([
                                'badge',
                                'badge-success' => $user['is_active'],
                                'badge-danger' => ! $user['is_active'],
                            ])>{{ $user['status'] }}</span>
                        </strong>
                    </div>
                    <div class="summary-item">
                        <span>Email status</span>
                        <strong>
                            <span @class([
                                'badge',
                                'badge-success' => $user['email_status'] === 'Complete',
                                'badge-pending' => $user['email_status'] === 'Pending',
                            ])>{{ $user['email_status'] }}</span>
                        </strong>
                    </div>
                </div>
            </section>

            <p class="note">Set a temporary password and share it securely with the user.</p>

            @if (! $user['is_active'])
                <p class="note note--inactive">This account is inactive and cannot log in until activated.</p>
            @endif

            <section class="form-card">
                <form method="POST" action="{{ route('admin.users.reset-password.store', $user['id']) }}" class="form-grid">
                    @csrf

                    <div @class(['form-field', 'has-error' => $errors->has('password')])>
                        <label for="password">New password</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password">
                        @error('password')<div class="field-error">{{ $message }}</div>@enderror
                    </div>

                    <div @class(['form-field', 'has-error' => $errors->has('password_confirmation')])>
                        <label for="password_confirmation">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                        @error('password_confirmation')<div class="field-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Reset password</button>
                        <a href="{{ route('admin.users') }}" class="btn-link">Back to users</a>
                    </div>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
