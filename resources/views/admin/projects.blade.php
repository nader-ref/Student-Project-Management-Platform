<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects · Admin · Projects Hub</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f6f9fc; color: #0a2942; }
        header, main { max-width: 1200px; margin: 0 auto; padding: 24px; }
        header { display: flex; justify-content: space-between; align-items: center; }
        a { color: #1f6f9f; font-weight: 700; text-decoration: none; }
        .header-actions { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
        .logout-button { border: 0; border-radius: 999px; background: #c62828; color: #fff; cursor: pointer; font-weight: 700; padding: 10px 16px; }
        .card { background: #fff; border: 1px solid #dce6f3; border-radius: 18px; padding: 20px; box-shadow: 0 12px 30px rgba(10, 41, 66, 0.08); }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 16px; overflow: hidden; }
        th, td { padding: 12px; border-bottom: 1px solid #e6eef7; text-align: left; font-size: 14px; }
        th { background: #0a2942; color: #fff; }
        .table-wrap { overflow-x: auto; }
    </style>
</head>
<body>
    <header>
        <div>
            <h1>Projects</h1>
            <p>Read-only project overview.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.users') }}">Users</a>
            <a href="{{ route('admin.requests') }}">Requests</a>
            <a href="{{ route('admin.ideas') }}">Ideas</a>
            <a href="{{ route('admin.submissions') }}">Submissions</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-button">Logout</button>
            </form>
        </div>
    </header>

    <main>
        <section class="card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Supervisor</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Members</th>
                            <th>Seminar 1</th>
                            <th>Seminar 2</th>
                            <th>Seminar 3</th>
                            <th>Final</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($projects as $project)
                            <tr>
                                <td>{{ $project['name'] }}</td>
                                <td>{{ $project['supervisor'] }}</td>
                                <td>{{ $project['department'] }}</td>
                                <td>{{ $project['status'] }}</td>
                                <td>{{ $project['member_count'] }}</td>
                                <td>{{ $project['seminar_1']?->format('M d, Y') ?? '—' }}</td>
                                <td>{{ $project['seminar_2']?->format('M d, Y') ?? '—' }}</td>
                                <td>{{ $project['seminar_3']?->format('M d, Y') ?? '—' }}</td>
                                <td>{{ $project['final']?->format('M d, Y') ?? '—' }}</td>
                                <td>{{ $project['created_at']?->format('M d, Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10">No projects found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
