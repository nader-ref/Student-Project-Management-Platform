<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submissions · Admin · Projects Hub</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f6f9fc; color: #0a2942; }
        header, main { max-width: 1100px; margin: 0 auto; padding: 24px; }
        header { display: flex; justify-content: space-between; align-items: center; }
        a { color: #1f6f9f; font-weight: 700; text-decoration: none; }
        .header-actions { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
        .logout-button { border: 0; border-radius: 999px; background: #c62828; color: #fff; cursor: pointer; font-weight: 700; padding: 10px 16px; }
        .card { background: #fff; border: 1px solid #dce6f3; border-radius: 18px; padding: 20px; box-shadow: 0 12px 30px rgba(10, 41, 66, 0.08); }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 16px; overflow: hidden; }
        th, td { padding: 14px; border-bottom: 1px solid #e6eef7; text-align: left; }
        th { background: #0a2942; color: #fff; }
    </style>
</head>
<body>
    <header>
        <div>
            <h1>Submissions</h1>
            <p>Read-only submission overview.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.users') }}">Users</a>
            <a href="{{ route('admin.projects') }}">Projects</a>
            <a href="{{ route('admin.requests') }}">Requests</a>
            <a href="{{ route('admin.ideas') }}">Ideas</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-button">Logout</button>
            </form>
        </div>
    </header>

    <main>
        <section class="card">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Project</th>
                        <th>Submitter</th>
                        <th>Milestone</th>
                        <th>Status</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($submissions as $submission)
                        <tr>
                            <td>{{ $submission['title'] }}</td>
                            <td>{{ $submission['project'] }}</td>
                            <td>{{ $submission['submitter'] }}</td>
                            <td>{{ $submission['milestone'] }}</td>
                            <td>{{ $submission['status'] }}</td>
                            <td>{{ $submission['submitted_at']?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No submissions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
