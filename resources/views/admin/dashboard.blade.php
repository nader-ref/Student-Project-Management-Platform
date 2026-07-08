@extends('layouts.admin')

@section('title', 'Admin Dashboard · Projects Hub')
@section('brand_title', 'Admin Dashboard')
@section('user_name')
Signed in as {{ auth()->user()->name }}
@endsection

@section('content')
    <section class="admin-hero">
        <div class="admin-hero-inner">
            <div>
                <nav class="admin-breadcrumb" aria-label="Breadcrumb">
                    <span>Admin</span>
                    <span class="sep">/</span>
                    <span>Dashboard</span>
                </nav>
                <h1>Welcome back, {{ auth()->user()->name }}</h1>
                <p>Institutional command center — monitor users, projects, and workflow activity across the platform from one place.</p>
            </div>
            <div class="admin-hero-actions">
                <a href="{{ route('admin.requests') }}" class="btn-hero-outline">
                    <i class="fas fa-inbox"></i> Review requests
                </a>
                <a href="{{ route('admin.students.create') }}" class="btn-hero-solid">
                    <i class="fas fa-user-plus"></i> Add account
                </a>
            </div>
        </div>
    </section>

    <section class="dashboard-section" aria-labelledby="overview-heading">
        <h2 id="overview-heading" class="dashboard-section-heading"><i class="fas fa-chart-pie"></i> Overview</h2>
        <div class="metric-grid" aria-label="Admin statistics">
            <a href="{{ route('admin.users') }}" class="metric-card metric-card-link">
                <span class="metric-label"><i class="fas fa-users"></i> Total users</span>
                <p class="metric-value">{{ $stats['totalUsers'] }}</p>
            </a>
            <div class="metric-card">
                <span class="metric-label"><i class="fas fa-user-graduate"></i> Total students</span>
                <p class="metric-value">{{ $stats['totalStudents'] }}</p>
            </div>
            <div class="metric-card">
                <span class="metric-label"><i class="fas fa-user-tie"></i> Total supervisors</span>
                <p class="metric-value">{{ $stats['totalSupervisors'] }}</p>
            </div>
            <a href="{{ route('admin.projects') }}" class="metric-card metric-card-link">
                <span class="metric-label"><i class="fas fa-diagram-project"></i> Total projects</span>
                <p class="metric-value">{{ $stats['totalProjects'] }}</p>
            </a>
            <a href="{{ route('admin.submissions') }}" class="metric-card metric-card-link">
                <span class="metric-label"><i class="fas fa-file-arrow-up"></i> Total submissions</span>
                <p class="metric-value">{{ $stats['totalSubmissions'] }}</p>
            </a>
            <a href="{{ route('admin.requests') }}" class="metric-card metric-card-link metric-card--highlight">
                <span class="metric-label"><i class="fas fa-inbox"></i> Pending requests</span>
                <p class="metric-value">{{ $stats['pendingRequests'] }}</p>
            </a>
            <a href="{{ route('admin.ideas') }}" class="metric-card metric-card-link metric-card--highlight">
                <span class="metric-label"><i class="fas fa-lightbulb"></i> Pending ideas</span>
                <p class="metric-value">{{ $stats['pendingIdeas'] }}</p>
            </a>
            <a href="{{ route('admin.users') }}" class="metric-card metric-card-link metric-card--highlight">
                <span class="metric-label"><i class="fas fa-envelope"></i> Pending email</span>
                <p class="metric-value">{{ $stats['pendingEmailUsers'] }}</p>
            </a>
        </div>

        <section class="data-card data-card--spaced">
            <div class="data-card-header">
                <h2>Latest registered users</h2>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>University number</th>
                            <th>Email status</th>
                            <th>Role</th>
                            <th>Account status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($latestUsers as $user)
                            <tr>
                                <td>{{ $user['name'] }}</td>
                                <td>{{ $user['university_number'] }}</td>
                                <td>
                                    <span @class([
                                        'badge',
                                        'badge-success' => $user['email_status'] === 'Complete',
                                        'badge-pending' => $user['email_status'] === 'Pending',
                                    ])>{{ $user['email_status'] }}</span>
                                </td>
                                <td><span class="badge badge-neutral">{{ $user['role'] }}</span></td>
                                <td><span class="badge badge-success">{{ $user['status'] }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>

    <section class="dashboard-section" aria-labelledby="projects-heading">
        <h2 id="projects-heading" class="dashboard-section-heading">
            <i class="fas fa-diagram-project"></i> Projects
            <a href="{{ route('admin.projects') }}">View all</a>
        </h2>
        <div class="metric-grid metric-grid--compact">
            <a href="{{ route('admin.projects') }}" class="metric-card metric-card-link">
                <span class="metric-label">Available projects</span>
                <p class="metric-value">{{ $stats['availableProjects'] }}</p>
            </a>
            <a href="{{ route('admin.projects') }}" class="metric-card metric-card-link">
                <span class="metric-label">Assigned projects</span>
                <p class="metric-value">{{ $stats['takenProjects'] }}</p>
            </a>
        </div>
    </section>

    <section class="dashboard-section" aria-labelledby="workflow-heading">
        <h2 id="workflow-heading" class="dashboard-section-heading"><i class="fas fa-diagram-next"></i> Workflow</h2>
        <section class="data-card">
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Area</th>
                            <th>Pending</th>
                            <th>Accepted</th>
                            <th>Rejected</th>
                            <th>View</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Requests</strong></td>
                            <td><span class="report-count">{{ $workflowSummary['requests']['pending'] }}</span></td>
                            <td><span class="report-count">{{ $workflowSummary['requests']['accepted'] }}</span></td>
                            <td><span class="report-count">{{ $workflowSummary['requests']['rejected'] }}</span></td>
                            <td><a href="{{ route('admin.requests') }}">Requests</a></td>
                        </tr>
                        <tr>
                            <td><strong>Ideas</strong></td>
                            <td><span class="report-count">{{ $workflowSummary['ideas']['pending'] }}</span></td>
                            <td><span class="report-count">{{ $workflowSummary['ideas']['accepted'] }}</span></td>
                            <td><span class="report-count">{{ $workflowSummary['ideas']['rejected'] }}</span></td>
                            <td><a href="{{ route('admin.ideas') }}">Ideas</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </section>

    <section class="dashboard-section" aria-labelledby="submissions-heading">
        <h2 id="submissions-heading" class="dashboard-section-heading">
            <i class="fas fa-file-arrow-up"></i> Submissions
            <a href="{{ route('admin.submissions') }}">View all</a>
        </h2>
        <section class="data-card">
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($submissionSummary as $row)
                            <tr>
                                <td>
                                    <span @class([
                                        'badge',
                                        'badge-info' => $row['label'] === 'Pending Review',
                                        'badge-success' => $row['label'] === 'Approved',
                                        'badge-warning' => $row['label'] === 'Revision Required',
                                    ])>{{ $row['label'] }}</span>
                                </td>
                                <td><span class="report-count">{{ $row['count'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </section>

    <section class="dashboard-section" aria-labelledby="supervisor-workload-heading">
        <h2 id="supervisor-workload-heading" class="dashboard-section-heading">
            <i class="fas fa-user-tie"></i> Supervisor workload
            <a href="{{ route('admin.projects') }}">View projects</a>
        </h2>
        <section class="data-card">
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Supervisor</th>
                            <th>Total projects</th>
                            <th>Assignment</th>
                            <th>Available</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($supervisorWorkload as $row)
                            <tr>
                                <td><strong>{{ $row['name'] }}</strong></td>
                                <td><span class="report-count">{{ $row['total'] }}</span></td>
                                <td><span class="report-count">{{ $row['taken'] }}</span></td>
                                <td><span class="report-count">{{ $row['available'] }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-state">No supervisors found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>
@endsection
