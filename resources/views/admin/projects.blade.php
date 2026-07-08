@extends('layouts.admin')

@section('title', 'Projects · Admin · Projects Hub')

@section('content')
    @include('admin.partials.page-hero', [
        'title' => 'Projects',
        'description' => 'Read-only project overview.',
        'breadcrumb' => '<span>Admin</span><span class="sep">/</span><span>Projects</span>',
    ])

    <section class="data-card">
        <div class="data-card-header-bar">
            <h2><i class="fas fa-diagram-project"></i> Project registry</h2>
            <span>{{ count($projects) }} listed</span>
        </div>
        <div class="table-wrap">
            <table class="data-table data-table--compact">
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
