@extends('layouts.supervisor')

@section('title', 'Projects Hub · Supervisor Dashboard')

@section('content')
    @php
        use Carbon\Carbon;

        $supervisorName = Session::get('name');
        $supervisorEmail = Session::get('email');
        $myProjects = $projects;
        $myProjectIds = $myProjects->pluck('id');
        $supervisorRequests = $requests;
        $projectNames = $myProjects->keyBy('id');

        $totalProjects = $myProjects->count();
        $availableProjects = $myProjects->where('taken', 0)->count();
        $takenProjects = $myProjects->where('taken', 1)->count();
        $availabilityRate = $totalProjects > 0 ? round(($availableProjects / $totalProjects) * 100) : 0;
        $takenRate = $totalProjects > 0 ? round(($takenProjects / $totalProjects) * 100) : 0;

        $pendingRequests = $supervisorRequests->filter(
            fn ($r) => ! $r->accepted && ! ($r->rejected ?? false),
        );
        $processedRequests = $supervisorRequests->filter(
            fn ($r) => $r->accepted || ($r->rejected ?? false),
        );

        $pendingIdeas = $ideas->filter(
            fn ($i) => ! $i->accepted && ! $i->rejected,
        );
        $processedIdeas = $ideas->filter(
            fn ($i) => $i->accepted || $i->rejected,
        );

        $pendingReplyCount = $inboxMessages->filter(fn ($m) => empty($m->Replay))->count();
        $pendingSubmissions = $submissions->where('status', 'submitted')->count();

        $deptLabels = [
            'software' => 'Software Engineering',
            'ai' => 'Artificial Intelligence',
            'network' => 'Network & Cybersecurity',
        ];

        $upcomingDeadlines = collect();
        $milestoneFields = [
            'seminar_1' => 'Seminar 1',
            'seminar_2' => 'Seminar 2',
            'seminar_3' => 'Seminar 3',
            'final' => 'Final Presentation',
        ];
        foreach ($myProjects as $project) {
            foreach ($milestoneFields as $field => $label) {
                if (! empty($project->$field)) {
                    try {
                        $date = Carbon::parse($project->$field);
                        if ($date->isFuture() && now()->diffInDays($date, false) <= 14) {
                            $upcomingDeadlines->push([
                                'project' => $project->name,
                                'label' => $label,
                                'date' => $date,
                                'days_left' => (int) now()->diffInDays($date, false),
                            ]);
                        }
                    } catch (\Exception $e) {
                    }
                }
            }
        }
        $upcomingDeadlines = $upcomingDeadlines->sortBy('date');

        $byDepartment = $myProjects->groupBy('department')->map->count();
        $totalReqCount = $supervisorRequests->count();
        $acceptedReqCount = $supervisorRequests->where('accepted', 1)->count();
        $rejectedReqCount = $supervisorRequests->where('rejected', 1)->count();
        $pendingReqCount = $pendingRequests->count();
        $requestAcceptRate = $totalReqCount > 0 ? round(($acceptedReqCount / $totalReqCount) * 100) : 0;

        $totalIdeaCount = $ideas->count();
        $acceptedIdeaCount = $ideas->where('accepted', 1)->count();
        $rejectedIdeaCount = $ideas->where('rejected', 1)->count();
        $ideaAcceptRate = $totalIdeaCount > 0 ? round(($acceptedIdeaCount / $totalIdeaCount) * 100) : 0;

        $activeTab = Session::get('active_tab', 'dashboard');
    @endphp

    <div class="dashboard" data-active-tab="{{ $activeTab }}">
        @include('supervisor.partials.navbar')

        <div class="content-panel">
            @if (Session::has('success'))
                <div class="form-pro-alert success" style="margin-bottom: 1rem;">
                    <i class="fas fa-check-circle"></i> {{ Session::get('success') }}
                </div>
            @endif
            @if (Session::has('error'))
                <div class="form-pro-alert error" style="margin-bottom: 1rem;">
                    <i class="fas fa-exclamation-circle"></i> {{ Session::get('error') }}
                </div>
            @endif

            <section class="acceptance-hero">
                <div class="acceptance-hero-inner">
                    <div>
                        <nav class="breadcrumb" aria-label="Breadcrumb">
                            <span>Supervisor Portal</span>
                            <span class="sep">/</span>
                            <span>Dashboard</span>
                        </nav>
                        <h1>Welcome back, {{ $supervisorName }}</h1>
                        <p>Manage projects, review requests, communicate with students, and track deadlines.</p>
                    </div>
                    <div class="hero-actions">
                        <button type="button" class="btn-hero-outline dash-tab-trigger" data-tab="show_pro">
                            <i class="fas fa-folder-open"></i> My Projects
                        </button>
                        <button type="button" class="btn-hero-solid dash-tab-trigger" data-tab="Message">
                            <i class="fas fa-envelope"></i> Messages
                            @if ($pendingReplyCount > 0)
                                ({{ $pendingReplyCount }})
                            @endif
                        </button>
                    </div>
                </div>
            </section>

            <div class="tabs-container dash-nav" id="main-tabs">
                <button class="tab-btn {{ $activeTab === 'dashboard' ? 'active' : '' }}" data-tab="dashboard">
                    <i class="fas fa-th-large"></i> Overview
                </button>
                <button class="tab-btn {{ $activeTab === 'show_pro' ? 'active' : '' }}" data-tab="show_pro">
                    <i class="fas fa-folder-open"></i> Projects
                </button>
                <button class="tab-btn {{ $activeTab === 'projects' ? 'active' : '' }}" data-tab="projects">
                    <i class="fas fa-plus-circle"></i> Add
                </button>
                <button class="tab-btn {{ $activeTab === 'Request' ? 'active' : '' }}" data-tab="Request">
                    <i class="fas fa-clipboard-list"></i> Requests
                    @if ($pendingRequests->count() > 0)
                        <span class="form-badge optional-badge badge-inline">{{ $pendingRequests->count() }}</span>
                    @endif
                </button>
                <button class="tab-btn {{ $activeTab === 'Idea' ? 'active' : '' }}" data-tab="Idea">
                    <i class="far fa-lightbulb"></i> Ideas
                    @if ($pendingIdeas->count() > 0)
                        <span class="form-badge optional-badge badge-inline">{{ $pendingIdeas->count() }}</span>
                    @endif
                </button>
                <button class="tab-btn {{ $activeTab === 'Message' ? 'active' : '' }}" data-tab="Message">
                    <i class="fas fa-envelope"></i> Messages
                    @if ($pendingReplyCount > 0)
                        <span class="form-badge optional-badge badge-inline">{{ $pendingReplyCount }}</span>
                    @endif
                </button>
                <button class="tab-btn {{ $activeTab === 'Submissions' ? 'active' : '' }}" data-tab="Submissions">
                    <i class="fas fa-file-upload"></i> Submissions
                    @if ($pendingSubmissions > 0)
                        <span class="form-badge optional-badge badge-inline">{{ $pendingSubmissions }}</span>
                    @endif
                </button>
                <button class="tab-btn {{ $activeTab === 'History' ? 'active' : '' }}" data-tab="History">
                    <i class="fas fa-history"></i> History
                </button>
                <button class="tab-btn {{ $activeTab === 'Reports' ? 'active' : '' }}" data-tab="Reports">
                    <i class="fas fa-chart-bar"></i> Reports
                </button>
            </div>

            {{-- OVERVIEW --}}
            <div id="dashboard" class="tab-content {{ $activeTab === 'dashboard' ? 'active-content' : '' }}">
                <div class="dash-kpi-grid">
                    <div class="kpi-card total">
                        <div class="kpi-card-top">
                            <span class="kpi-label">My Projects</span>
                            <span class="kpi-icon"><i class="fas fa-layer-group"></i></span>
                        </div>
                        <div class="kpi-value">{{ $totalProjects }}</div>
                        <div class="kpi-meta">Under your supervision</div>
                        <div class="kpi-bar" @style(['--bar-width: 100%' => true])><div class="kpi-bar-fill"></div></div>
                    </div>
                    <div class="kpi-card available">
                        <div class="kpi-card-top">
                            <span class="kpi-label">Available</span>
                            <span class="kpi-icon"><i class="fas fa-check-circle"></i></span>
                        </div>
                        <div class="kpi-value">{{ $availableProjects }}</div>
                        <div class="kpi-meta">{{ $availabilityRate }}% open</div>
                        <div class="kpi-bar" @style(['--bar-width: '.$availabilityRate.'%' => true])><div class="kpi-bar-fill"></div></div>
                    </div>
                    <div class="kpi-card taken">
                        <div class="kpi-card-top">
                            <span class="kpi-label">Taken</span>
                            <span class="kpi-icon"><i class="fas fa-users"></i></span>
                        </div>
                        <div class="kpi-value">{{ $takenProjects }}</div>
                        <div class="kpi-meta">{{ $takenRate }}% assigned</div>
                        <div class="kpi-bar" @style(['--bar-width: '.$takenRate.'%' => true])><div class="kpi-bar-fill"></div></div>
                    </div>
                    <div class="kpi-card pending">
                        <div class="kpi-card-top">
                            <span class="kpi-label">Pending Actions</span>
                            <span class="kpi-icon"><i class="fas fa-clock"></i></span>
                        </div>
                        <div class="kpi-value">{{ $pendingRequests->count() + $pendingIdeas->count() + $pendingReplyCount }}</div>
                        <div class="kpi-meta">Requests, ideas & messages</div>
                        <div class="kpi-bar" @style(['--bar-width: '.min(100, ($pendingRequests->count() + $pendingIdeas->count() + $pendingReplyCount) * 15).'%' => true])><div class="kpi-bar-fill"></div></div>
                    </div>
                </div>

                <div class="quick-actions-grid">
                    <button type="button" class="quick-action-card dash-tab-trigger" data-tab="Request">
                        <span class="quick-action-icon"><i class="fas fa-clipboard-check"></i></span>
                        <div>
                            <strong>Review Requests</strong>
                            <span>{{ $pendingRequests->count() }} pending</span>
                        </div>
                    </button>
                    <button type="button" class="quick-action-card dash-tab-trigger" data-tab="Message">
                        <span class="quick-action-icon"><i class="fas fa-envelope"></i></span>
                        <div>
                            <strong>Reply to Students</strong>
                            <span>{{ $pendingReplyCount }} awaiting reply</span>
                        </div>
                    </button>
                    <button type="button" class="quick-action-card dash-tab-trigger" data-tab="Reports">
                        <span class="quick-action-icon"><i class="fas fa-chart-bar"></i></span>
                        <div>
                            <strong>View Reports</strong>
                            <span>{{ $requestAcceptRate }}% request acceptance</span>
                        </div>
                    </button>
                    <button type="button" class="quick-action-card dash-tab-trigger" data-tab="projects">
                        <span class="quick-action-icon"><i class="fas fa-plus"></i></span>
                        <div>
                            <strong>Add Project</strong>
                            <span>Register a new project</span>
                        </div>
                    </button>
                </div>

                <div class="overview-split">
                    <div class="insight-card">
                        <h3><i class="fas fa-chart-bar"></i> Project Distribution</h3>
                        <div class="distribution-row">
                            <span>Available</span>
                            <div class="distribution-track" @style(['--bar-width: '.$availabilityRate.'%' => true])>
                                <div class="distribution-fill available"></div>
                            </div>
                            <span>{{ $availableProjects }}</span>
                        </div>
                        <div class="distribution-row">
                            <span>Taken</span>
                            <div class="distribution-track" @style(['--bar-width: '.$takenRate.'%' => true])>
                                <div class="distribution-fill taken"></div>
                            </div>
                            <span>{{ $takenProjects }}</span>
                        </div>
                    </div>
                    <div class="activity-card">
                        <h3><i class="fas fa-bell"></i> Upcoming Deadlines</h3>
                        @if ($upcomingDeadlines->isEmpty())
                            <div class="activity-list">
                                <div class="activity-item">
                                    <i class="fas fa-check"></i>
                                    <span>No milestones within the next 14 days.</span>
                                </div>
                            </div>
                        @else
                            <div class="activity-list">
                                @foreach ($upcomingDeadlines->take(4) as $alert)
                                    <div class="activity-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>
                                            <strong>{{ $alert['project'] }}</strong> — {{ $alert['label'] }}
                                            ({{ $alert['days_left'] }}d)
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- PROJECTS LIST --}}
            <div id="show_pro" class="tab-content {{ $activeTab === 'show_pro' ? 'active-content' : '' }}">
                <div class="tab-panel-header">
                    <h2><i class="fas fa-folder-open"></i> My Projects</h2>
                    <p>Search, filter, and edit your supervised projects.</p>
                </div>

                @if ($myProjects->isNotEmpty())
                    <div class="project-toolbar">
                        <input type="search" id="project-search" placeholder="Search by name..." aria-label="Search projects">
                        <select id="project-filter-dept" aria-label="Filter by department">
                            <option value="all">All Departments</option>
                            @foreach ($deptLabels as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <select id="project-filter-status" aria-label="Filter by status">
                            <option value="all">All Status</option>
                            <option value="available">Available</option>
                            <option value="taken">Taken</option>
                        </select>
                    </div>
                @endif

                @if ($myProjects->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-folder-open"></i></div>
                        <h3>No projects yet</h3>
                        <p>Use the Add tab to register your first project.</p>
                        <div class="empty-state-actions">
                            <button type="button" class="btn-primary dash-tab-trigger" data-tab="projects">
                                <i class="fas fa-plus"></i> Add Project
                            </button>
                        </div>
                    </div>
                @else
                    <div class="request-list" id="project-list">
                        @foreach ($myProjects as $project)
                            <article
                                class="project-item {{ $project->taken == 0 ? 'is-available' : 'is-taken' }}"
                                data-name="{{ strtolower($project->name) }}"
                                data-department="{{ $project->department }}"
                                data-status="{{ $project->taken == 0 ? 'available' : 'taken' }}"
                            >
                                <div class="project-item-body">
                                    <div class="request-item-top">
                                        <div>
                                            <div class="request-ref">
                                                PRJ-{{ str_pad($project->id, 4, '0', STR_PAD_LEFT) }}
                                            </div>
                                            <h3>{{ $project->name }}</h3>
                                        </div>
                                        <span class="status-pill {{ $project->taken == 0 ? 'accepted' : 'pending' }}">
                                            {{ $project->taken == 0 ? 'Available' : 'Taken' }}
                                        </span>
                                    </div>
                                    <div class="request-meta-grid">
                                        <div class="meta-block">
                                            <label>Department</label>
                                            <span>{{ $deptLabels[$project->department] ?? $project->department ?? '—' }}</span>
                                        </div>
                                        <div class="meta-block">
                                            <label>Seminar 1</label>
                                            <span>{{ $project->seminar_1 ? Carbon::parse($project->seminar_1)->format('M d, Y') : '—' }}</span>
                                        </div>
                                        <div class="meta-block">
                                            <label>Final</label>
                                            <span>{{ $project->final ? Carbon::parse($project->final)->format('M d, Y') : '—' }}</span>
                                        </div>
                                    </div>
                                    @if ($project->members->isNotEmpty())
                                        <div class="meta-block team-block">
                                            <label>Team</label>
                                            <span>{{ $project->members->sortBy('position')->pluck('user.name')->implode(', ') }}</span>
                                        </div>
                                    @endif
                                    <div class="meta-block team-block">
                                        <label>Description</label>
                                        <span>{{ $project->description ?? '—' }}</span>
                                    </div>
                                    @include('supervisor.partials.project-edit-form', ['project' => $project])
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ADD PROJECT --}}
            <div id="projects" class="tab-content {{ $activeTab === 'projects' ? 'active-content' : '' }}">
                <div class="tab-panel-header">
                    <h2><i class="fas fa-plus-circle"></i> Register New Project</h2>
                    <p>Add a project to the catalog with seminar schedule.</p>
                </div>

                <form method="POST" action="{{ url('/addproject') }}" class="request-form-pro">
                    @csrf
                    @if ($errors->any() && $activeTab !== 'show_pro')
                        <div class="form-pro-alert error">
                            <i class="fas fa-exclamation-circle"></i> Please fix the errors below.
                        </div>
                    @endif

                    <div class="form-pro-card">
                        <div class="form-pro-card-header">
                            <span class="form-step-badge">01</span>
                            <div>
                                <h3>Project Information</h3>
                                <p>Name and description</p>
                            </div>
                        </div>
                        <div class="form-pro-card-body">
                            <div class="form-field form-field-pro">
                                <label>Project Name</label>
                                <input type="text" name="project_name" required value="{{ old('project_name') }}">
                                @error('project_name')<span class="error-text">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-field form-field-pro" style="margin-top: 1rem;">
                                <label>Description</label>
                                <textarea name="description" rows="4" required>{{ old('description') }}</textarea>
                                @error('description')<span class="error-text">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-pro-card" style="margin-top: 1rem;">
                        <div class="form-pro-card-body">
                            <div class="form-grid">
                                <div class="form-field form-field-pro">
                                    <label>Department</label>
                                    <select name="department" required>
                                        <option value="" disabled {{ old('department') ? '' : 'selected' }}>Select</option>
                                        <option value="software" {{ old('department') == 'software' ? 'selected' : '' }}>Software Engineering</option>
                                        <option value="ai" {{ old('department') == 'ai' ? 'selected' : '' }}>Artificial Intelligence</option>
                                        <option value="network" {{ old('department') == 'network' ? 'selected' : '' }}>Network & Cybersecurity</option>
                                    </select>
                                </div>
                                <div class="form-field form-field-pro">
                                    <label>Already Taken?</label>
                                    <select name="taken" required>
                                        <option value="No" {{ old('taken', 'No') == 'No' ? 'selected' : '' }}>No</option>
                                        <option value="Yes" {{ old('taken') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-grid" style="margin-top: 1rem;">
                                <div class="form-field form-field-pro">
                                    <label>Seminar 1</label>
                                    <input type="date" name="seminar1_date" required value="{{ old('seminar1_date') }}">
                                </div>
                                <div class="form-field form-field-pro">
                                    <label>Seminar 2</label>
                                    <input type="date" name="seminar2_date" required value="{{ old('seminar2_date') }}">
                                </div>
                                <div class="form-field form-field-pro">
                                    <label>Seminar 3</label>
                                    <input type="date" name="seminar3_date" required value="{{ old('seminar3_date') }}">
                                </div>
                                <div class="form-field form-field-pro">
                                    <label>Final</label>
                                    <input type="date" name="final_date" required value="{{ old('final_date') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-pro-actions">
                        <button type="submit" class="btn-primary"><i class="fas fa-paper-plane"></i> Register</button>
                    </div>
                </form>
            </div>

            {{-- REQUESTS --}}
            <div id="Request" class="tab-content {{ $activeTab === 'Request' ? 'active-content' : '' }}">
                <div class="tab-panel-header">
                    <h2><i class="fas fa-clipboard-list"></i> Project Requests</h2>
                    <p>Applications for <strong>your projects only</strong>. Accept or reject with a reason.</p>
                </div>

                @if ($pendingRequests->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
                        <h3>No pending requests</h3>
                        <p>All applications for your projects have been processed.</p>
                    </div>
                @else
                    <div class="request-list">
                        @foreach ($pendingRequests as $req)
                            @php
                                $proj = $req->project;
                                $requestMembers = $req->members->sortBy('position');
                            @endphp
                            <article class="request-item is-pending">
                                <div class="request-item-body">
                                    <div class="request-item-top">
                                        <div>
                                            <div class="request-ref">REQ-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</div>
                                            <h3>{{ $proj->name ?? 'Project request' }}</h3>
                                        </div>
                                        <span class="status-pill pending"><i class="fas fa-hourglass-half"></i> Pending</span>
                                    </div>
                                    <div class="request-meta-grid">
                                        <div class="meta-block">
                                            <label>Team Size</label>
                                            <span>{{ $req->count }} member(s)</span>
                                        </div>
                                        <div class="meta-block">
                                            <label>Submitted</label>
                                            <span>{{ $req->created_at?->format('M d, Y') ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="meta-block team-block">
                                        <label>Team Members</label>
                                        <div class="avatar-stack">
                                            @foreach ($requestMembers as $member)
                                                <span class="avatar-chip">
                                                    <span class="avatar-initial">{{ strtoupper(substr($member->user->name, 0, 1)) }}</span>
                                                    {{ $member->user->name }} ({{ $member->user->university_number }})
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="form-pro-actions" style="margin-top: 1rem; padding: 1rem; flex-wrap: wrap; gap: 0.75rem;">
                                        <form action="{{ url('/acceptrequest') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="request" value="{{ $req->id }}">
                                            <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Accept</button>
                                        </form>
                                        <form action="{{ url('/rejectrequest') }}" method="POST" class="form-field-pro" style="display: flex; gap: 0.5rem; align-items: flex-end; flex: 1; min-width: 240px;">
                                            @csrf
                                            <input type="hidden" name="request" value="{{ $req->id }}">
                                            <div style="flex: 1;">
                                                <label>Rejection reason</label>
                                                <input type="text" name="reason" required placeholder="Why is this request declined?">
                                            </div>
                                            <button type="submit" class="btn-secondary"><i class="fas fa-times"></i> Reject</button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- IDEAS --}}
            <div id="Idea" class="tab-content {{ $activeTab === 'Idea' ? 'active-content' : '' }}">
                <div class="tab-panel-header">
                    <h2><i class="far fa-lightbulb"></i> Idea Proposals</h2>
                    <p>Ideas submitted directly to you by student teams.</p>
                </div>

                @if ($pendingIdeas->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-lightbulb"></i></div>
                        <h3>No pending ideas</h3>
                        <p>New idea proposals will appear here.</p>
                    </div>
                @else
                    <div class="request-list">
                        @foreach ($pendingIdeas as $idea)
                            @php $ideaMembers = $idea->members->sortBy('position'); @endphp
                            <article class="request-item is-pending">
                                <div class="request-item-body">
                                    <div class="request-item-top">
                                        <div>
                                            <div class="request-ref">IDEA-{{ str_pad($idea->id, 4, '0', STR_PAD_LEFT) }}</div>
                                            <h3>{{ $idea->projectname }}</h3>
                                        </div>
                                        <span class="status-pill pending">Under Review</span>
                                    </div>
                                    <div class="meta-block team-block">
                                        <label>Team</label>
                                        <span>{{ $ideaMembers->pluck('user.name')->implode(', ') }}</span>
                                    </div>
                                    <div class="form-pro-actions" style="margin-top: 1rem; padding: 1rem; flex-wrap: wrap; gap: 0.75rem;">
                                        <form action="{{ url('/acceptidea') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="idea" value="{{ $idea->id }}">
                                            <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Accept</button>
                                        </form>
                                        <form action="{{ url('/rejectidea') }}" method="POST" class="form-field-pro" style="display: flex; gap: 0.5rem; align-items: flex-end; flex: 1; min-width: 240px;">
                                            @csrf
                                            <input type="hidden" name="idea" value="{{ $idea->id }}">
                                            <div style="flex: 1;">
                                                <label>Rejection reason</label>
                                                <input type="text" name="reason" required placeholder="Reason for rejection">
                                            </div>
                                            <button type="submit" class="btn-secondary"><i class="fas fa-times"></i> Reject</button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- MESSAGES --}}
            <div id="Message" class="tab-content {{ $activeTab === 'Message' ? 'active-content' : '' }}">
                @include('supervisor.partials.section-messages', [
                    'projects' => $myProjects,
                    'inboxMessages' => $inboxMessages,
                    'announcements' => $announcements,
                    'pendingReplyCount' => $pendingReplyCount,
                ])
            </div>

            {{-- SUBMISSIONS --}}
            <div id="Submissions" class="tab-content {{ $activeTab === 'Submissions' ? 'active-content' : '' }}">
                @include('supervisor.partials.section-submissions')
            </div>

            {{-- HISTORY --}}
            <div id="History" class="tab-content {{ $activeTab === 'History' ? 'active-content' : '' }}">
                @include('supervisor.partials.section-history')
            </div>

            {{-- REPORTS --}}
            <div id="Reports" class="tab-content {{ $activeTab === 'Reports' ? 'active-content' : '' }}">
                @include('supervisor.partials.section-reports')
            </div>

            {{-- SETTINGS --}}
            <div id="settings" class="tab-content {{ $activeTab === 'settings' ? 'active-content' : '' }}">
                <div class="tab-panel-header">
                    <h2><i class="fas fa-cog"></i> Settings & Profile</h2>
                    <p>Manage your account and workspace preferences.</p>
                </div>

                <div class="profile-card">
                    <h3><i class="fas fa-user-tie"></i> Profile</h3>
                    <div class="profile-row">
                        <label>Name</label>
                        <span>{{ $supervisorName }}</span>
                    </div>
                    <div class="profile-row">
                        <label>Email</label>
                        <span>{{ $supervisorEmail }}</span>
                    </div>
                    <div class="profile-row">
                        <label>Role</label>
                        <span class="color-badge">Supervisor</span>
                    </div>
                    @if ($supervisor)
                        <div class="profile-row">
                            <label>Supervisor ID</label>
                            <span>#{{ str_pad($supervisor->id, 4, '0', STR_PAD_LEFT) }}</span>
                        </div>
                    @endif
                </div>

                <div class="profile-card">
                    <h3><i class="fas fa-key"></i> Change Password</h3>
                    <form method="POST" action="{{ url('/supervisor/changepassword') }}">
                        @csrf
                        <div class="form-field form-field-pro">
                            <label>Current Password</label>
                            <input type="password" name="old_password" required>
                            @error('old_password')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-grid" style="margin-top: 0.75rem;">
                            <div class="form-field form-field-pro">
                                <label>New Password</label>
                                <input type="password" name="new_password" required minlength="8">
                            </div>
                            <div class="form-field form-field-pro">
                                <label>Confirm Password</label>
                                <input type="password" name="new_password_confirmation" required minlength="8">
                            </div>
                        </div>
                        <div class="form-pro-actions" style="padding: 0; margin-top: 1rem;">
                            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Update Password</button>
                        </div>
                    </form>
                </div>

                <div class="settings-grid">
                    <div class="setting-card">
                        <div class="setting-info">
                            <i class="fas fa-moon"></i>
                            <span>Dark Mode</span>
                        </div>
                        <div class="toggle-switch off" id="dark-toggle"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-footer-accent"></div>
    </div>
@endsection

@push('scripts')
<script>
(function() {
    const tabButtons = document.querySelectorAll('#main-tabs .tab-btn');
    const tabTriggers = document.querySelectorAll('.dash-tab-trigger');
    const contents = ['dashboard','show_pro','projects','Request','Idea','Message','Submissions','History','Reports','settings'];

    function activateTab(tabId) {
        tabButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.tab === tabId));
        contents.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.toggle('active-content', id === tabId);
        });
    }

    tabButtons.forEach(btn => btn.addEventListener('click', () => activateTab(btn.dataset.tab)));
    tabTriggers.forEach(t => t.addEventListener('click', () => activateTab(t.dataset.tab)));

    const initial = (function() {
        const urlTab = new URLSearchParams(window.location.search).get('tab');
        const sessionTab = document.querySelector('.dashboard')?.dataset.activeTab || '';
        return [urlTab, sessionTab].find(t => t && document.getElementById(t)) || 'dashboard';
    })();
    activateTab(initial);
})();

(function() {
    const search = document.getElementById('project-search');
    const dept = document.getElementById('project-filter-dept');
    const status = document.getElementById('project-filter-status');
    const items = document.querySelectorAll('#project-list .project-item');

    function filterProjects() {
        const q = (search?.value || '').toLowerCase().trim();
        const d = dept?.value || 'all';
        const s = status?.value || 'all';
        items.forEach(item => {
            const matchName = !q || item.dataset.name.includes(q);
            const matchDept = d === 'all' || item.dataset.department === d;
            const matchStatus = s === 'all' || item.dataset.status === s;
            item.classList.toggle('hidden', !(matchName && matchDept && matchStatus));
        });
    }
    search?.addEventListener('input', filterProjects);
    dept?.addEventListener('change', filterProjects);
    status?.addEventListener('change', filterProjects);
})();

(function() {
    document.querySelectorAll('.message-subtab').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.message-subtab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const panel = this.dataset.messagePanel;
            document.querySelectorAll('.message-panel').forEach(p => p.classList.remove('active-content'));
            const map = { inbox: 'message-inbox', announce: 'message-announce', sent: 'message-sent' };
            document.getElementById(map[panel])?.classList.add('active-content');
        });
    });
})();

(function() {
    document.querySelectorAll('.history-subtab').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.history-subtab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.history-panel').forEach(p => p.classList.remove('active-content'));
            document.getElementById(this.dataset.historyPanel)?.classList.add('active-content');
        });
    });
})();

document.addEventListener('DOMContentLoaded', function() {
    const body = document.getElementById('main-body');
    const toggle = document.getElementById('dark-toggle');
    if (!toggle || !body) return;
    toggle.addEventListener('click', function() {
        const willBeDark = !body.classList.contains('dark-mode');
        if (typeof window.applyStudentDarkMode === 'function') {
            window.applyStudentDarkMode(willBeDark);
        } else {
            body.classList.toggle('dark-mode', willBeDark);
            toggle.classList.toggle('off', !willBeDark);
        }
        localStorage.setItem('theme', willBeDark ? 'dark' : 'light');
    });
});
</script>
@endpush
