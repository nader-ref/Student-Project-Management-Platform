@extends('layouts.admin')

@section('title', 'Admin Dashboard · Projects Hub')

@section('content')
    <section class="acceptance-hero admin-page-hero admin-dashboard-hero">
        <div class="acceptance-hero-inner">
            <div>
                <nav class="breadcrumb" aria-label="Breadcrumb">
                    <span>Admin</span>
                    <span class="sep">/</span>
                    <span>Dashboard</span>
                </nav>
                <h1>Welcome back, {{ auth()->user()->name }}</h1>
                <p>Institutional command center — monitor users, projects, and workflow activity across the platform from one place.</p>
            </div>
            <div class="hero-actions">
                <a href="{{ route('admin.requests') }}" class="btn-hero-outline">
                    <i class="fas fa-inbox"></i> Review requests
                </a>
                <a href="{{ route('admin.students.create') }}" class="btn-hero-solid">
                    <i class="fas fa-user-plus"></i> Add account
                </a>
            </div>
        </div>
    </section>

    @include('admin.partials.tabs')

    <section class="dashboard-section" aria-labelledby="overview-heading">
        <h2 id="overview-heading" class="dashboard-section-heading"><i class="fas fa-chart-pie"></i> Overview</h2>
        <div class="dash-kpi-grid dash-kpi-grid--admin-8" aria-label="Admin statistics">
            <a href="{{ route('admin.users') }}" class="kpi-card total kpi-card-link">
                <div class="kpi-card-top">
                    <span class="kpi-label">Total users</span>
                    <span class="kpi-icon"><i class="fas fa-users"></i></span>
                </div>
                <div class="kpi-value">{{ $stats['totalUsers'] }}</div>
                <div class="kpi-meta">Registered accounts</div>
                <div class="kpi-bar" style="--bar-width: 100%"><div class="kpi-bar-fill"></div></div>
            </a>
            <div class="kpi-card available">
                <div class="kpi-card-top">
                    <span class="kpi-label">Total students</span>
                    <span class="kpi-icon"><i class="fas fa-user-graduate"></i></span>
                </div>
                <div class="kpi-value">{{ $stats['totalStudents'] }}</div>
                <div class="kpi-meta">Student accounts</div>
                <div class="kpi-bar" style="--bar-width: 100%"><div class="kpi-bar-fill"></div></div>
            </div>
            <div class="kpi-card taken">
                <div class="kpi-card-top">
                    <span class="kpi-label">Total supervisors</span>
                    <span class="kpi-icon"><i class="fas fa-user-tie"></i></span>
                </div>
                <div class="kpi-value">{{ $stats['totalSupervisors'] }}</div>
                <div class="kpi-meta">Supervisor accounts</div>
                <div class="kpi-bar" style="--bar-width: 100%"><div class="kpi-bar-fill"></div></div>
            </div>
            <a href="{{ route('admin.projects') }}" class="kpi-card total kpi-card-link">
                <div class="kpi-card-top">
                    <span class="kpi-label">Total projects</span>
                    <span class="kpi-icon"><i class="fas fa-diagram-project"></i></span>
                </div>
                <div class="kpi-value">{{ $stats['totalProjects'] }}</div>
                <div class="kpi-meta">In the system</div>
                <div class="kpi-bar" style="--bar-width: 100%"><div class="kpi-bar-fill"></div></div>
            </a>
            <a href="{{ route('admin.submissions') }}" class="kpi-card taken kpi-card-link">
                <div class="kpi-card-top">
                    <span class="kpi-label">Total submissions</span>
                    <span class="kpi-icon"><i class="fas fa-file-arrow-up"></i></span>
                </div>
                <div class="kpi-value">{{ $stats['totalSubmissions'] }}</div>
                <div class="kpi-meta">Uploaded files</div>
                <div class="kpi-bar" style="--bar-width: 100%"><div class="kpi-bar-fill"></div></div>
            </a>
            <a href="{{ route('admin.requests') }}" class="kpi-card highlight kpi-card-link">
                <div class="kpi-card-top">
                    <span class="kpi-label">Pending requests</span>
                    <span class="kpi-icon"><i class="fas fa-inbox"></i></span>
                </div>
                <div class="kpi-value">{{ $stats['pendingRequests'] }}</div>
                <div class="kpi-meta">Awaiting review</div>
                <div class="kpi-bar" style="--bar-width: {{ min(100, $stats['pendingRequests'] * 10) }}%"><div class="kpi-bar-fill"></div></div>
            </a>
            <a href="{{ route('admin.ideas') }}" class="kpi-card highlight kpi-card-link">
                <div class="kpi-card-top">
                    <span class="kpi-label">Pending ideas</span>
                    <span class="kpi-icon"><i class="fas fa-lightbulb"></i></span>
                </div>
                <div class="kpi-value">{{ $stats['pendingIdeas'] }}</div>
                <div class="kpi-meta">Awaiting review</div>
                <div class="kpi-bar" style="--bar-width: {{ min(100, $stats['pendingIdeas'] * 10) }}%"><div class="kpi-bar-fill"></div></div>
            </a>
            <a href="{{ route('admin.users') }}" class="kpi-card highlight kpi-card-link">
                <div class="kpi-card-top">
                    <span class="kpi-label">Pending email</span>
                    <span class="kpi-icon"><i class="fas fa-envelope"></i></span>
                </div>
                <div class="kpi-value">{{ $stats['pendingEmailUsers'] }}</div>
                <div class="kpi-meta">Incomplete profiles</div>
                <div class="kpi-bar" style="--bar-width: {{ min(100, $stats['pendingEmailUsers'] * 10) }}%"><div class="kpi-bar-fill"></div></div>
            </a>
        </div>

        <section class="data-card data-card--spaced">
            <div class="data-card-header">
                <h2><i class="fas fa-user-plus"></i> Latest registered users</h2>
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
        <div class="dash-kpi-grid dash-kpi-grid--compact">
            <a href="{{ route('admin.projects') }}" class="kpi-card available kpi-card-link">
                <div class="kpi-card-top">
                    <span class="kpi-label">Available projects</span>
                    <span class="kpi-icon"><i class="fas fa-check-circle"></i></span>
                </div>
                <div class="kpi-value">{{ $stats['availableProjects'] }}</div>
                <div class="kpi-meta">Open for assignment</div>
                <div class="kpi-bar" style="--bar-width: 100%"><div class="kpi-bar-fill"></div></div>
            </a>
            <a href="{{ route('admin.projects') }}" class="kpi-card taken kpi-card-link">
                <div class="kpi-card-top">
                    <span class="kpi-label">Assigned projects</span>
                    <span class="kpi-icon"><i class="fas fa-users"></i></span>
                </div>
                <div class="kpi-value">{{ $stats['takenProjects'] }}</div>
                <div class="kpi-meta">Already taken</div>
                <div class="kpi-bar" style="--bar-width: 100%"><div class="kpi-bar-fill"></div></div>
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
