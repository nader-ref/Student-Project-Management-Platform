<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard · Projects Hub</title>
    <style>
        :root {
            --admin-navy: #0f172a;
            --admin-slate: #1e293b;
            --admin-accent: #6366f1;
            --admin-accent-soft: #eef2ff;
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
            --admin-info: #2563eb;
            --admin-info-bg: #eff6ff;
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
        .metric-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        }
        .metric-card {
            background: var(--admin-card);
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius);
            padding: 18px 20px;
            box-shadow: var(--admin-shadow);
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        }
        .metric-card-link { display: block; color: inherit; }
        .metric-card-link:hover {
            transform: translateY(-2px);
            border-color: #c7d2fe;
            box-shadow: 0 10px 28px rgba(99, 102, 241, 0.12);
        }
        .metric-card--highlight {
            border-color: #fcd34d;
            background: linear-gradient(180deg, #fffbeb 0%, #ffffff 100%);
        }
        .metric-card--highlight .metric-label { color: #b45309; }
        .metric-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--admin-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .metric-value {
            margin: 10px 0 0;
            font-size: 34px;
            font-weight: 800;
            line-height: 1;
            color: var(--admin-navy);
        }
        .metric-card--highlight .metric-value { color: #b45309; }
        .data-card {
            background: var(--admin-card);
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius);
            box-shadow: var(--admin-shadow);
            overflow: hidden;
            margin-top: 24px;
        }
        .data-card-header {
            padding: 20px 22px 0;
        }
        .data-card-header h2 {
            margin: 0 0 16px;
            font-size: 18px;
            font-weight: 800;
        }
        .table-wrap { overflow-x: auto; }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .data-table th,
        .data-table td {
            padding: 14px 18px;
            text-align: left;
            border-bottom: 1px solid var(--admin-border);
            vertical-align: middle;
        }
        .data-table th {
            background: var(--admin-navy);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .data-table tbody tr:hover { background: #f8fafc; }
        .data-table tbody tr:last-child td { border-bottom: 0; }
        .empty-state {
            padding: 36px 18px;
            text-align: center;
            color: var(--admin-muted);
            font-weight: 600;
        }
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
        .badge-neutral { background: #f1f5f9; color: #475569; }
        @media (max-width: 720px) {
            .admin-main { padding: 20px 16px 32px; }
            .admin-topbar, .admin-nav-wrap { padding-left: 16px; padding-right: 16px; }
            .admin-nav { padding: 0 16px; }
        }
    </style>
</head>
<body>
    <div class="admin-shell">
        <header class="admin-topbar">
            <div class="admin-topbar-inner">
                <div class="admin-brand">
                    <span class="admin-brand-kicker">Projects Hub</span>
                    <span class="admin-brand-title">Admin Dashboard</span>
                </div>
                <div class="admin-userbar">
                    <span class="admin-user-name">Signed in as {{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="admin-logout">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        <nav class="admin-nav-wrap" aria-label="Admin navigation">
            <div class="admin-nav">
                <a href="{{ route('admin.dashboard') }}" class="admin-nav-link is-active">Dashboard</a>
                <a href="{{ route('admin.users') }}" class="admin-nav-link">Users</a>
                <a href="{{ route('admin.projects') }}" class="admin-nav-link">Projects</a>
                <a href="{{ route('admin.requests') }}" class="admin-nav-link">Requests</a>
                <a href="{{ route('admin.ideas') }}" class="admin-nav-link">Ideas</a>
                <a href="{{ route('admin.submissions') }}" class="admin-nav-link">Submissions</a>
            </div>
        </nav>

        <main class="admin-main">
            <div class="admin-page-header">
                <h1>Admin Dashboard</h1>
                <p>Institutional overview of users, projects, and workflow activity.</p>
            </div>

            <section class="metric-grid" aria-label="Admin statistics">
                <a href="{{ route('admin.users') }}" class="metric-card metric-card-link">
                    <span class="metric-label">Total users</span>
                    <p class="metric-value">{{ $stats['totalUsers'] }}</p>
                </a>
                <div class="metric-card">
                    <span class="metric-label">Total students</span>
                    <p class="metric-value">{{ $stats['totalStudents'] }}</p>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Total supervisors</span>
                    <p class="metric-value">{{ $stats['totalSupervisors'] }}</p>
                </div>
                <a href="{{ route('admin.projects') }}" class="metric-card metric-card-link">
                    <span class="metric-label">Total projects</span>
                    <p class="metric-value">{{ $stats['totalProjects'] }}</p>
                </a>
                <a href="{{ route('admin.submissions') }}" class="metric-card metric-card-link">
                    <span class="metric-label">Total submissions</span>
                    <p class="metric-value">{{ $stats['totalSubmissions'] }}</p>
                </a>
                <a href="{{ route('admin.requests') }}" class="metric-card metric-card-link metric-card--highlight">
                    <span class="metric-label">Pending requests</span>
                    <p class="metric-value">{{ $stats['pendingRequests'] }}</p>
                </a>
                <a href="{{ route('admin.ideas') }}" class="metric-card metric-card-link metric-card--highlight">
                    <span class="metric-label">Pending ideas</span>
                    <p class="metric-value">{{ $stats['pendingIdeas'] }}</p>
                </a>
            </section>

            <section class="data-card">
                <div class="data-card-header">
                    <h2>Latest registered users</h2>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>University number</th>
                                <th>Role</th>
                                <th>Account status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($latestUsers as $user)
                                <tr>
                                    <td>{{ $user['name'] }}</td>
                                    <td>{{ $user['university_number'] }}</td>
                                    <td><span class="badge badge-neutral">{{ $user['role'] }}</span></td>
                                    <td><span class="badge badge-success">{{ $user['status'] }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty-state">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
