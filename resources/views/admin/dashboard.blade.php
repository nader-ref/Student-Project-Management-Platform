<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard · Projects Hub</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f6f9fc; color: #0a2942; }
        header, main { max-width: 1100px; margin: 0 auto; padding: 24px; }
        header { display: flex; justify-content: space-between; align-items: center; }
        a { color: #1f6f9f; font-weight: 700; text-decoration: none; }
        .header-actions { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
        .logout-button { border: 0; border-radius: 999px; background: #c62828; color: #fff; cursor: pointer; font-weight: 700; padding: 10px 16px; }
        .grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
        .card { background: #fff; border: 1px solid #dce6f3; border-radius: 18px; padding: 20px; box-shadow: 0 12px 30px rgba(10, 41, 66, 0.08); }
        .card-link { display: block; color: inherit; }
        .card-link:hover { border-color: #1f6f9f; }
        .metric { font-size: 32px; font-weight: 800; margin: 8px 0 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; background: #fff; border-radius: 16px; overflow: hidden; }
        th, td { padding: 14px; border-bottom: 1px solid #e6eef7; text-align: left; }
        th { background: #0a2942; color: #fff; }
    </style>
</head>
<body>
    <header>
        <div>
            <h1>Admin Dashboard</h1>
            <p>Signed in as {{ auth()->user()->name }}</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.users') }}">Users</a>
            <a href="{{ route('admin.projects') }}">Projects</a>
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
        <section class="grid" aria-label="Admin statistics">
            <a href="{{ route('admin.users') }}" class="card card-link">
                <strong>Total users</strong>
                <p class="metric">{{ $stats['totalUsers'] }}</p>
            </a>
            <div class="card">
                <strong>Total students</strong>
                <p class="metric">{{ $stats['totalStudents'] }}</p>
            </div>
            <div class="card">
                <strong>Total supervisors</strong>
                <p class="metric">{{ $stats['totalSupervisors'] }}</p>
            </div>
            <a href="{{ route('admin.projects') }}" class="card card-link">
                <strong>Total projects</strong>
                <p class="metric">{{ $stats['totalProjects'] }}</p>
            </a>
            <a href="{{ route('admin.submissions') }}" class="card card-link">
                <strong>Total submissions</strong>
                <p class="metric">{{ $stats['totalSubmissions'] }}</p>
            </a>
            <a href="{{ route('admin.requests') }}" class="card card-link">
                <strong>Pending requests</strong>
                <p class="metric">{{ $stats['pendingRequests'] }}</p>
            </a>
            <a href="{{ route('admin.ideas') }}" class="card card-link">
                <strong>Pending ideas</strong>
                <p class="metric">{{ $stats['pendingIdeas'] }}</p>
            </a>
        </section>

        <section class="card" style="margin-top: 24px;">
            <h2>Latest registered users</h2>
            <table>
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
                            <td>{{ $user['role'] }}</td>
                            <td>{{ $user['status'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
