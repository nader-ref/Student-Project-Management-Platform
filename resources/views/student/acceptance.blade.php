@extends('layouts.student')

@section('title', 'Projects Hub · Your Requests')

@section('content')
    @php
        $currentUserId = auth()->id();
        $myRequests = $requests;
        $acceptedCount = $myRequests->where('accepted', 1)->count();
        $pendingCount = $myRequests->filter(
            fn ($request) => ! $request->accepted && ! ($request->rejected ?? false),
        )->count();
        $totalCount = $myRequests->count();
        $acceptanceRate = $totalCount > 0 ? round(($acceptedCount / $totalCount) * 100) : 0;
        $pendingRate = $totalCount > 0 ? round(($pendingCount / $totalCount) * 100) : 0;
    @endphp

    <div class="dashboard">
        @include('student.partials.navbar')

        <div class="content-panel">
            <section class="acceptance-hero">
                <div class="acceptance-hero-inner">
                    <div>
                        <nav class="breadcrumb" aria-label="Breadcrumb">
                            <a href="{{ url('/StudentDashboard') }}">Dashboard</a>
                            <span class="sep">/</span>
                            <span>Project Requests</span>
                        </nav>
                        <h1>Request Status Center</h1>
                        <p>Monitor every project application submitted by your team and follow approval progress in one place.</p>
                    </div>
                    <div class="hero-actions">
                        <a href="{{ url('/StudentDashboard') }}" class="btn-hero-outline">
                            <i class="fas fa-arrow-left"></i> Dashboard
                        </a>
                        <a href="{{ url('/StudentDashboard?tab=request') }}" class="btn-hero-solid">
                            <i class="fas fa-plus"></i> New Request
                        </a>
                    </div>
                </div>
            </section>

            <div class="acceptance-kpi-grid">
                <div class="kpi-card total">
                    <div class="kpi-card-top">
                        <span class="kpi-label">Total Submissions</span>
                        <span class="kpi-icon"><i class="fas fa-layer-group"></i></span>
                    </div>
                    <div class="kpi-value">{{ $totalCount }}</div>
                    <div class="kpi-meta">All requests linked to your account</div>
                    <div class="kpi-bar" @style(['--bar-width: 100%' => true])><div class="kpi-bar-fill"></div></div>
                </div>
                <div class="kpi-card accepted">
                    <div class="kpi-card-top">
                        <span class="kpi-label">Approved</span>
                        <span class="kpi-icon"><i class="fas fa-check-circle"></i></span>
                    </div>
                    <div class="kpi-value">{{ $acceptedCount }}</div>
                    <div class="kpi-meta">{{ $acceptanceRate }}% approval rate</div>
                    <div class="kpi-bar" @style(['--bar-width: '.$acceptanceRate.'%' => true])><div class="kpi-bar-fill"></div></div>
                </div>
                <div class="kpi-card pending">
                    <div class="kpi-card-top">
                        <span class="kpi-label">Under Review</span>
                        <span class="kpi-icon"><i class="fas fa-clock"></i></span>
                    </div>
                    <div class="kpi-value">{{ $pendingCount }}</div>
                    <div class="kpi-meta">Awaiting supervisor decision</div>
                    <div class="kpi-bar" @style(['--bar-width: '.$pendingRate.'%' => true])><div class="kpi-bar-fill"></div></div>
                </div>
            </div>

            @if ($myRequests->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3>No project requests found</h3>
                    <p>You have not submitted any applications yet. Start by choosing a project and building your team from the dashboard.</p>
                    <div class="empty-state-actions">
                        <a href="{{ url('/StudentDashboard?tab=request') }}" class="btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Request
                        </a>
                        <a href="{{ url('/StudentDashboard?tab=projects') }}" class="btn-secondary">
                            <i class="fas fa-th-large"></i> Browse Projects
                        </a>
                    </div>
                </div>
            @else
                <div class="acceptance-toolbar">
                    <div class="toolbar-title">
                        Application History
                        <span>· {{ $totalCount }} record(s)</span>
                    </div>
                    <div class="filter-group" role="group" aria-label="Filter requests">
                        <button type="button" class="filter-btn active" data-filter="all">All</button>
                        <button type="button" class="filter-btn" data-filter="accepted">Approved</button>
                        <button type="button" class="filter-btn" data-filter="pending">Under Review</button>
                    </div>
                </div>

                <div class="request-list" id="request-list">
                    @foreach ($myRequests as $request)
                        @php
                            $isAccepted = $request->accepted == 1;
                            $teamMembers = $request->members->sortBy('position');
                        @endphp
                        <article
                            class="request-item {{ $isAccepted ? 'is-accepted' : 'is-pending' }}"
                            data-status="{{ $isAccepted ? 'accepted' : 'pending' }}">
                            <div class="request-item-body">
                                <div class="request-item-top">
                                    <div>
                                        <div class="request-ref">
                                            <i class="fas fa-hashtag"></i> REQ-{{ str_pad($request->id, 4, '0', STR_PAD_LEFT) }}
                                        </div>
                                        <h3>{{ $request->project->name ?? 'Project request' }}</h3>
                                    </div>
                                    <span class="status-pill {{ $isAccepted ? 'accepted' : 'pending' }}">
                                        <i class="fas fa-{{ $isAccepted ? 'check-circle' : 'hourglass-half' }}"></i>
                                        {{ $isAccepted ? 'Approved' : 'Under Review' }}
                                    </span>
                                </div>

                                <div class="request-meta-grid">
                                    <div class="meta-block">
                                        <label>Team Size</label>
                                        <span>{{ $request->count }} member(s)</span>
                                    </div>
                                    <div class="meta-block">
                                        <label>Submitted On</label>
                                        <span>{{ $request->created_at?->format('M d, Y') ?? '—' }}</span>
                                    </div>
                                    <div class="meta-block">
                                        <label>Last Updated</label>
                                        <span>{{ $request->updated_at?->format('M d, Y') ?? '—' }}</span>
                                    </div>
                                </div>

                                <div class="meta-block team-block">
                                    <label>Team Members</label>
                                    <div class="avatar-stack">
                                        @foreach ($teamMembers as $member)
                                            <span class="avatar-chip {{ $member->user_id === $currentUserId ? 'is-you' : '' }}">
                                                <span class="avatar-initial">{{ strtoupper(substr($member->user->name, 0, 1)) }}</span>
                                                {{ $member->user->name }}{{ $member->user_id === $currentUserId ? ' (You)' : '' }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="request-progress" aria-label="Request progress">
                                    <div class="progress-step done">
                                        <div class="progress-dot"><i class="fas fa-check"></i></div>
                                        <div class="progress-label">Submitted</div>
                                    </div>
                                    <div class="progress-step {{ $isAccepted ? 'done' : 'active' }}">
                                        <div class="progress-dot"></div>
                                        <div class="progress-label">Review</div>
                                    </div>
                                    <div class="progress-step {{ $isAccepted ? 'done active is-final' : '' }}">
                                        <div class="progress-dot">
                                            @if ($isAccepted)
                                                <i class="fas fa-check"></i>
                                            @endif
                                        </div>
                                        <div class="progress-label">Approved</div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            <div class="settings-note acceptance-note">
                <i class="fas fa-info-circle"></i>
                Supervisors review requests in order of submission. Contact them via the dashboard if you need an update.
            </div>
        </div>

        <div class="dashboard-footer-accent"></div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const requestItems = document.querySelectorAll('.request-item');

            if (!filterButtons.length) return;

            filterButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const filter = button.dataset.filter;

                    filterButtons.forEach(function(btn) {
                        btn.classList.toggle('active', btn === button);
                    });

                    requestItems.forEach(function(item) {
                        const status = item.dataset.status;
                        const show = filter === 'all' || status === filter;
                        item.classList.toggle('hidden', !show);
                    });
                });
            });
        });
    </script>
@endpush
