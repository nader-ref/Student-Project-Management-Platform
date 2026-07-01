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
        .grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
        .card { background: #fff; border: 1px solid #dce6f3; border-radius: 18px; padding: 20px; box-shadow: 0 12px 30px rgba(10, 41, 66, 0.08); }
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
        <a href="{{ route('admin.users') }}">View users</a>
    </header>

    <main>
        <section class="grid" aria-label="Admin statistics">
            <div class="card">
                <strong>Total users</strong>
                <p class="metric">{{ $stats['totalUsers'] }}</p>
            </div>
            <div class="card">
                <strong>Total students</strong>
                <p class="metric">{{ $stats['totalStudents'] }}</p>
            </div>
            <div class="card">
                <strong>Total supervisors</strong>
                <p class="metric">{{ $stats['totalSupervisors'] }}</p>
            </div>
            <div class="card">
                <strong>Total projects</strong>
                <p class="metric">{{ $stats['totalProjects'] }}</p>
            </div>
            <div class="card">
                <strong>Total submissions</strong>
                <p class="metric">{{ $stats['totalSubmissions'] }}</p>
            </div>
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
