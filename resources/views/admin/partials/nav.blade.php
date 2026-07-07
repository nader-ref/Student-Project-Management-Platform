<nav class="admin-nav-wrap" aria-label="Admin navigation">
    <div class="admin-nav">
        <a href="{{ route('admin.dashboard') }}" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.dashboard')])>Dashboard</a>
        <a href="{{ route('admin.users') }}" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.users', 'admin.users.reset-password')])>Users</a>
        <a href="{{ route('admin.projects') }}" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.projects')])>Projects</a>
        <a href="{{ route('admin.requests') }}" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.requests')])>Requests</a>
        <a href="{{ route('admin.ideas') }}" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.ideas')])>Ideas</a>
        <a href="{{ route('admin.submissions') }}" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.submissions')])>Submissions</a>
        <a href="{{ route('admin.supervisors.create') }}" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.supervisors.create')])>Create Supervisor</a>
        <a href="{{ route('admin.students.create') }}" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.students.create')])>Create Student</a>
    </div>
</nav>
