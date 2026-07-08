@extends('layouts.admin')

@section('title', 'Projects · Admin · Projects Hub')

@section('content')
    <div class="admin-page-header">
        <span class="admin-page-header-icon"><i class="fas fa-diagram-project"></i></span>
        <div>
            <h1>Projects</h1>
            <p>Read-only project overview.</p>
        </div>
    </div>

    <section class="data-card">
        <div class="table-wrap">
            <table class="data-table">
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
                            <td><strong>{{ $project['name'] }}</strong></td>
                            <td>{{ $project['supervisor'] }}</td>
                            <td>{{ $project['department'] }}</td>
                            <td>
                                <span @class([
                                    'badge',
                                    'badge-taken' => $project['status'] === 'Assigned',
                                    'badge-available' => $project['status'] === 'Available',
                                ])>{{ $project['status'] }}</span>
                            </td>
                            <td><span class="badge badge-neutral">{{ $project['member_count'] }}</span></td>
                            <td>{{ $project['seminar_1']?->format('M d, Y') ?? '—' }}</td>
                            <td>{{ $project['seminar_2']?->format('M d, Y') ?? '—' }}</td>
                            <td>{{ $project['seminar_3']?->format('M d, Y') ?? '—' }}</td>
                            <td>{{ $project['final']?->format('M d, Y') ?? '—' }}</td>
                            <td>{{ $project['created_at']?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="empty-state">No projects found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
