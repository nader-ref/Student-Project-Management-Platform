@extends('layouts.student')

@section('title', 'Projects Hub · Dashboard')

@section('content')
@php
$isEnrolled = $enrollmentMode === 'enrolled';
$isPending = $enrollmentMode === 'pending';
$isDiscovery = $enrollmentMode === 'discovery';
$deptLabels = [
'software' => 'Software Engineering',
'ai' => 'Artificial Intelligence',
'network' => 'Network & Cybersecurity',
];
$totalProjects = $stats['totalProjects'];
$availableProjects = $stats['availableProjects'];
$takenProjects = $stats['takenProjects'];
$supervisorCount = $stats['supervisors'];
$availabilityRate = $totalProjects > 0 ? round(($availableProjects / $totalProjects) * 100) : 0;
$takenRate = $totalProjects > 0 ? round(($takenProjects / $totalProjects) * 100) : 0;
$enrolledSupervisorName = $enrolledProject?->supervisor?->name;
$enrolledSupervisorId = $enrolledProject?->supervisor?->id;
@endphp

<div class="dashboard" data-enrollment-mode="{{ $enrollmentMode }}" data-active-tab="{{ Session::get('active_tab', '') }}">
    @include('student.partials.navbar')

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

        @include('student.partials.dashboard-hero')

        @include('student.partials.dashboard-nav')

        {{-- OVERVIEW --}}
        <div id="dashboard" class="tab-content active-content">
            @if ($isEnrolled)
            @include('student.partials.overview-enrolled', ['deptLabels' => $deptLabels])
            @elseif ($isPending)
            @include('student.partials.overview-pending')
            @else
            <div class="dash-kpi-grid">
                <div class="kpi-card total">
                    <div class="kpi-card-top">
                        <span class="kpi-label">Total Projects</span>
                        <span class="kpi-icon"><i class="fas fa-layer-group"></i></span>
                    </div>
                    <div class="kpi-value">{{ $totalProjects }}</div>
                    <div class="kpi-meta">Registered in the system</div>
                    <div class="kpi-bar" @style(['--bar-width: 100%'=> true])><div class="kpi-bar-fill"></div>
                    </div>
                </div>
                <div class="kpi-card available">
                    <div class="kpi-card-top">
                        <span class="kpi-label">Available</span>
                        <span class="kpi-icon"><i class="fas fa-check-circle"></i></span>
                    </div>
                    <div class="kpi-value">{{ $availableProjects }}</div>
                    <div class="kpi-meta">{{ $availabilityRate }}% open for teams</div>
                    <div class="kpi-bar" @style(['--bar-width: '.$availabilityRate.' %'=> true])><div class="kpi-bar-fill"></div>
                    </div>
                </div>
                <div class="kpi-card taken">
                    <div class="kpi-card-top">
                        <span class="kpi-label">Taken</span>
                        <span class="kpi-icon"><i class="fas fa-users"></i></span>
                    </div>
                    <div class="kpi-value">{{ $takenProjects }}</div>
                    <div class="kpi-meta">{{ $takenRate }}% already assigned</div>
                    <div class="kpi-bar" @style(['--bar-width: '.$takenRate.' %'=> true])><div class="kpi-bar-fill"></div>
                    </div>
                </div>
                <div class="kpi-card supervisors">
                    <div class="kpi-card-top">
                        <span class="kpi-label">Supervisors</span>
                        <span class="kpi-icon"><i class="fas fa-user-tie"></i></span>
                    </div>
                    <div class="kpi-value">{{ $supervisorCount }}</div>
                    <div class="kpi-meta">Available to guide you</div>
                    <div class="kpi-bar" @style(['--bar-width: 100%'=> true])><div class="kpi-bar-fill"></div>
                    </div>
                </div>
            </div>

            <div class="quick-actions-grid">
                <button type="button" class="quick-action-card dash-tab-trigger" data-tab="projects">
                    <span class="quick-action-icon"><i class="fas fa-search"></i></span>
                    <div>
                        <strong>Explore Projects</strong>
                        <span>Browse all available topics</span>
                    </div>
                </button>
                <button type="button" class="quick-action-card dash-tab-trigger" data-tab="request">
                    <span class="quick-action-icon"><i class="fas fa-paper-plane"></i></span>
                    <div>
                        <strong>Submit Request</strong>
                        <span>Apply for a project with your team</span>
                    </div>
                </button>
                <a href="{{ url('/StudentDashboard/acceptance') }}" class="quick-action-card">
                    <span class="quick-action-icon"><i class="fas fa-clipboard-check"></i></span>
                    <div>
                        <strong>Track Requests</strong>
                        <span>View approval status</span>
                    </div>
                </a>
                <button type="button" class="quick-action-card dash-tab-trigger" data-tab="message">
                    <span class="quick-action-icon"><i class="fas fa-envelope"></i></span>
                    <div>
                        <strong>Contact Supervisor</strong>
                        <span>Send a message or question</span>
                    </div>
                </button>
            </div>

            <div class="overview-split">
                <div class="insight-card">
                    <h3><i class="fas fa-chart-bar"></i> Project Distribution</h3>
                    <div class="distribution-row">
                        <span>Available</span>
                        <div class="distribution-track" @style(['--bar-width: '.$availabilityRate.' %'=> true])>
                            <div class="distribution-fill available"></div>
                        </div>
                        <span>{{ $availableProjects }}</span>
                    </div>
                    <div class="distribution-row">
                        <span>Taken</span>
                        <div class="distribution-track" @style(['--bar-width: '.$takenRate.' %'=> true])>
                            <div class="distribution-fill taken"></div>
                        </div>
                        <span>{{ $takenProjects }}</span>
                    </div>
                </div>
                <div class="activity-card">
                    <h3><i class="fas fa-bolt"></i> Quick Tips</h3>
                    <div class="activity-list">
                        <div class="activity-item">
                            <i class="fas fa-check"></i>
                            <span>Choose an available project before submitting your team request.</span>
                        </div>
                        <div class="activity-item">
                            <i class="fas fa-users"></i>
                            <span>Teams can include up to 3 members per application.</span>
                        </div>
                        <div class="activity-item">
                            <i class="fas fa-lightbulb"></i>
                            <span>Have your own idea? Submit it under the New Idea tab.</span>
                        </div>
                        <div class="activity-item">
                            <i class="fas fa-bell"></i>
                            <span>Check request status anytime from Track Requests.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-note">
                <i class="fas fa-database"></i>
                Statistics are loaded live from your project database.
            </div>
            @endif
        </div>

        @if ($isDiscovery)
        {{-- PROJECTS --}}
        <div id="projects" class="tab-content">
            <div class="tab-panel-header">
                <h2><i class="fas fa-folder-open"></i> Projects Catalog</h2>
                <p>Browse all registered projects, filter by availability, and find the right topic for your team.</p>
            </div>

            <div class="acceptance-toolbar">
                <div class="toolbar-title">
                    All Projects
                    <span>· {{ $totalProjects }} listed</span>
                </div>
                <div class="filter-group" role="group" aria-label="Filter projects">
                    <button type="button" class="filter-btn active" data-project-filter="all">All</button>
                    <button type="button" class="filter-btn" data-project-filter="available">Available</button>
                    <button type="button" class="filter-btn" data-project-filter="assigned">Assigned</button>
                </div>
            </div>

            @if ($projects->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-folder-open"></i></div>
                <h3>No projects yet</h3>
                <p>There are no projects in the system. Check back later or contact your supervisor.</p>
            </div>
            @else
            <div class="request-list" id="project-list">
                @foreach ($projects as $project)
                @php $isAvailable = $project->isAvailable(); @endphp
                <article
                    class="project-item {{ $isAvailable ? 'is-available' : 'is-taken' }}"
                    data-status="{{ $isAvailable ? 'available' : 'assigned' }}">
                    <div class="project-item-body">
                        <div class="request-item-top">
                            <div>
                                <div class="request-ref">
                                    <i class="fas fa-hashtag"></i> PRJ-{{ str_pad($project->id, 4, '0', STR_PAD_LEFT) }}
                                </div>
                                <h3>{{ $project->name }}</h3>
                            </div>
                            <span class="status-pill {{ $isAvailable ? 'accepted' : 'pending' }}">
                                <i class="fas fa-{{ $isAvailable ? 'check-circle' : 'lock' }}"></i>
                                {{ $project->lifecycleLabel() }}
                            </span>
                        </div>

                        <div class="request-meta-grid">
                            <div class="meta-block">
                                <label>Supervisor</label>
                                <span>{{ $project->supervisor->name ?? 'N/A' }}</span>
                            </div>
                            <div class="meta-block">
                                <label>Department</label>
                                <span>{{ $project->department ?? '—' }}</span>
                            </div>
                            <div class="meta-block">
                                <label>Seminars</label>
                                <span>{{ $project->seminar_1 ?? '—' }} / {{ $project->seminar_2 ?? '—' }} / {{ $project->seminar_3 ?? '—' }}</span>
                            </div>
                        </div>

                        <div class="meta-block team-block">
                            <label>Description</label>
                            <span>{{ $project->description ?? 'No description provided.' }}</span>
                        </div>

                        <div class="project-tags">
                            <span class="project-tag"><i class="fas fa-flag-checkered"></i> Final: {{ $project->final ?? '—' }}</span>
                            <span class="project-tag"><i class="fas fa-id-badge"></i> ID: {{ $project->id }}</span>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
            @endif
        </div>

        {{-- REQUEST --}}
        <div id="request" class="tab-content">
            <div class="tab-panel-header">
                <h2><i class="fas fa-file-signature"></i> Project Request</h2>
                <p>Submit a formal application to join an existing project with your team members.</p>
            </div>

            <div class="form-pro-layout">
                <div class="form-pro-main">
                    <div class="form-pro-steps" aria-label="Form steps">
                        <div class="form-pro-step active">
                            <span class="form-pro-step-num">1</span>
                            Project Details
                        </div>
                        <div class="form-pro-step">
                            <span class="form-pro-step-num">2</span>
                            Team Leader
                        </div>
                        <div class="form-pro-step">
                            <span class="form-pro-step-num">3</span>
                            Team Members
                        </div>
                        <div class="form-pro-step">
                            <span class="form-pro-step-num">4</span>
                            Submit
                        </div>
                    </div>

                    <form method="POST" action="{{ url('/RequstAdd') }}" class="request-form-pro">
                        @csrf

                        @if (!empty(Session::get('faild')))
                        <div class="form-pro-alert error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ Session::get('faild') }}
                        </div>
                        @endif

                        @if (!empty(Session::get('success')))
                        <div class="form-pro-alert success">
                            <i class="fas fa-check-circle"></i>
                            {{ Session::get('success') }}
                        </div>
                        @endif

                        <div class="form-pro-card">
                            <div class="form-pro-card-header">
                                <span class="form-step-badge">01</span>
                                <div>
                                    <h3>Project Details</h3>
                                    <p>Enter the project ID you want to apply for and your team size</p>
                                </div>
                                <span class="form-badge required-badge">Required</span>
                            </div>
                            <div class="form-pro-card-body">
                                <div class="form-grid">
                                    <div class="form-field form-field-pro">
                                        <label><i class="fas fa-hashtag"></i> Project</label>
                                        <select name="project_id" required>
                                            <option value="" disabled {{ old('project_id') ? '' : 'selected' }}>Select an available project</option>
                                            @foreach ($projects->where('taken', 0) as $projectOption)
                                                <option value="{{ $projectOption->id }}" {{ (string) old('project_id') === (string) $projectOption->id ? 'selected' : '' }}>
                                                    #{{ $projectOption->id }} — {{ $projectOption->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-field form-field-pro">
                                        <label><i class="fas fa-users"></i> Team Size</label>
                                        <input type="number" name="count" required min="1" max="3" placeholder="1 – 3 members" value="{{ old('count') }}">
                                    </div>
                                </div>
                                @error('project_id')
                                <span class="error-text">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        @include('student.partials.student-members-form-pro')

                        <div class="form-pro-actions">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Application
                            </button>
                            <button type="reset" class="btn-secondary">
                                <i class="fas fa-undo"></i> Clear Form
                            </button>
                        </div>
                    </form>
                </div>

                <aside class="form-pro-sidebar">
                    <div class="sidebar-card">
                        <h4><i class="fas fa-chart-pie"></i> Project Stats</h4>
                        <div class="sidebar-stat">
                            <span>Available projects</span>
                            <strong>{{ $availableProjects }}</strong>
                        </div>
                        <div class="sidebar-stat">
                            <span>Total projects</span>
                            <strong>{{ $totalProjects }}</strong>
                        </div>
                        <div class="sidebar-stat">
                            <span>Max team size</span>
                            <strong>3 members</strong>
                        </div>
                    </div>

                    <div class="sidebar-card">
                        <h4><i class="fas fa-list-check"></i> Before You Submit</h4>
                        <ul class="sidebar-checklist">
                            <li><i class="fas fa-check"></i> Confirm the project ID from the Projects tab</li>
                            <li><i class="fas fa-check"></i> Ensure the project is still available</li>
                            <li><i class="fas fa-check"></i> Use correct student IDs for all members</li>
                            <li><i class="fas fa-check"></i> Team size must match member count</li>
                        </ul>
                    </div>

                    <a href="{{ url('/StudentDashboard/acceptance') }}" class="sidebar-link">
                        <i class="fas fa-clipboard-check"></i> Track My Requests
                    </a>
                    <button type="button" class="sidebar-link outline dash-tab-trigger" data-tab="projects">
                        <i class="fas fa-folder-open"></i> Browse Projects
                    </button>
                </aside>
            </div>
        </div>

        {{-- IDEA --}}
        <div id="idea" class="tab-content">
            <div class="tab-panel-header">
                <h2><i class="far fa-lightbulb"></i> New Project Idea</h2>
                <p>Propose your own project concept and assign a preferred supervisor.</p>
            </div>

            <div class="form-pro-layout">
                <div class="form-pro-main">
                    <div class="form-pro-steps" aria-label="Form steps">
                        <div class="form-pro-step active">
                            <span class="form-pro-step-num">1</span>
                            Idea Details
                        </div>
                        <div class="form-pro-step">
                            <span class="form-pro-step-num">2</span>
                            Team Leader
                        </div>
                        <div class="form-pro-step">
                            <span class="form-pro-step-num">3</span>
                            Team Members
                        </div>
                        <div class="form-pro-step">
                            <span class="form-pro-step-num">4</span>
                            Submit
                        </div>
                    </div>

                    <form method="POST" action="{{ url('/RequstIdea') }}" class="request-form-pro">
                        @csrf

                        @if (!empty(Session::get('faild2')))
                        <div class="form-pro-alert error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ Session::get('faild2') }}
                        </div>
                        @endif

                        <div class="form-pro-card">
                            <div class="form-pro-card-header">
                                <span class="form-step-badge">01</span>
                                <div>
                                    <h3>Idea & Supervisor</h3>
                                    <p>Describe your project concept and choose a preferred supervisor</p>
                                </div>
                                <span class="form-badge required-badge">Required</span>
                            </div>
                            <div class="form-pro-card-body">
                                <div class="form-grid">
                                    <div class="form-field form-field-pro">
                                        <label><i class="fas fa-lightbulb"></i> Project Name</label>
                                        <input type="text" name="projectname" required placeholder="Hotel Reservation System" value="{{ old('projectname') }}">
                                    </div>
                                    <div class="form-field form-field-pro">
                                        <label><i class="fas fa-users"></i> Team Size</label>
                                        <input type="number" name="count" required min="1" max="3" placeholder="1 – 3 members" value="{{ old('count') }}">
                                    </div>
                                    <div class="form-field form-field-pro">
                                        <label><i class="fas fa-user-tie"></i> Preferred Supervisor</label>
                                        <select name="supervisor_id" required>
                                            @include('student.partials.supervisor-options')
                                        </select>
                                    </div>
                                </div>
                                @error('supervisor_id')
                                <span class="error-text">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        @include('student.partials.student-members-form-pro', [
                        'leaderStep' => '02',
                        'member1Step' => '03',
                        'member2Step' => '04',
                        'leaderNote' => 'Primary proposer — required for every idea submission',
                        ])

                        <div class="form-pro-actions">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-lightbulb"></i> Submit Idea
                            </button>
                            <button type="reset" class="btn-secondary">
                                <i class="fas fa-undo"></i> Clear Form
                            </button>
                        </div>
                    </form>
                </div>

                <aside class="form-pro-sidebar">
                    <div class="sidebar-card">
                        <h4><i class="fas fa-user-tie"></i> Supervisors</h4>
                        <div class="sidebar-stat">
                            <span>Available supervisors</span>
                            <strong>{{ $supervisorCount }}</strong>
                        </div>
                        <div class="sidebar-stat">
                            <span>Max team size</span>
                            <strong>3 members</strong>
                        </div>
                        <div class="sidebar-stat">
                            <span>Review time</span>
                            <strong>3–5 days</strong>
                        </div>
                    </div>

                    <div class="sidebar-card">
                        <h4><i class="fas fa-list-check"></i> Idea Guidelines</h4>
                        <ul class="sidebar-checklist">
                            <li><i class="fas fa-check"></i> Choose a clear, descriptive project name</li>
                            <li><i class="fas fa-check"></i> Pick a supervisor from your department if possible</li>
                            <li><i class="fas fa-check"></i> Include all team members with valid student IDs</li>
                            <li><i class="fas fa-check"></i> Wait for supervisor approval before starting work</li>
                        </ul>
                    </div>

                    <a href="{{ url('/StudentDashboard/acceptanceidea') }}" class="sidebar-link">
                        <i class="fas fa-lightbulb"></i> View Submitted Ideas
                    </a>
                    <button type="button" class="sidebar-link outline dash-tab-trigger" data-tab="request">
                        <i class="fas fa-file-signature"></i> Apply for Existing Project
                    </button>
                </aside>
            </div>
        </div>
        @endif

        @if ($isPending)
        <div id="pending-status" class="tab-content">
            @include('student.partials.section-pending-status')
        </div>
        @endif

        @if ($isEnrolled)
        <div id="my-project" class="tab-content">
            @include('student.partials.section-my-project', ['deptLabels' => $deptLabels])
        </div>
        <div id="team" class="tab-content">
            @include('student.partials.section-team')
        </div>
        <div id="timeline" class="tab-content">
            @include('student.partials.section-timeline')
        </div>
        <div id="progress" class="tab-content">
            @include('student.partials.section-progress', [
            'progress' => $progress,
            'submissions' => $submissions,
            ])
        </div>
        <div id="submissions" class="tab-content">
            @include('student.partials.section-submissions', [
            'submissions' => $submissions,
            'milestoneLabels' => $milestoneLabels,
            ])
        </div>
        @endif

        {{-- MESSAGE --}}
        <div id="message" class="tab-content">
            <div class="tab-panel-header">
                <h2><i class="far fa-envelope"></i> Contact Supervisor</h2>
                <p>Send a direct message to your supervisor about requirements, deadlines, or project details.</p>
            </div>

            <div class="form-pro-layout">
                <div class="form-pro-main">
                    <div class="form-pro-steps" aria-label="Form steps">
                        <div class="form-pro-step active">
                            <span class="form-pro-step-num">1</span>
                            Recipient
                        </div>
                        <div class="form-pro-step">
                            <span class="form-pro-step-num">2</span>
                            Subject
                        </div>
                        <div class="form-pro-step">
                            <span class="form-pro-step-num">3</span>
                            Message
                        </div>
                        <div class="form-pro-step">
                            <span class="form-pro-step-num">4</span>
                            Send
                        </div>
                    </div>

                    <form method="POST" action="{{ url('/Message') }}" class="request-form-pro">
                        @csrf

                        @if (!empty(Session::get('success')))
                        <div class="form-pro-alert success">
                            <i class="fas fa-check-circle"></i>
                            {{ Session::get('success') }}
                        </div>
                        @endif

                        <div class="form-pro-card">
                            <div class="form-pro-card-header">
                                <span class="form-step-badge">01</span>
                                <div>
                                    <h3>Select Supervisor</h3>
                                    <p>Choose who should receive your message</p>
                                </div>
                                <span class="form-badge required-badge">Required</span>
                            </div>
                            <div class="form-pro-card-body">
                                <div class="form-field form-field-pro">
                                    <label><i class="fas fa-user-tie"></i> Supervisor Name</label>
                                    <select name="supervisor_id" required>
                                        @if ($isEnrolled && $enrolledSupervisorId)
                                        <option value="{{ $enrolledSupervisorId }}" selected>{{ $enrolledSupervisorName }}</option>
                                        @else
                                            <option value="" disabled {{ old('supervisor_id') ? '' : 'selected' }}>Select a supervisor</option>
                                            @foreach($supervisors as $supervisor)
                                                <option value="{{ $supervisor->id }}" {{ (string) old('supervisor_id') === (string) $supervisor->id ? 'selected' : '' }}>{{ $supervisor->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @error('supervisor_id')
                                <span class="error-text">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-pro-card">
                            <div class="form-pro-card-header">
                                <span class="form-step-badge">02</span>
                                <div>
                                    <h3>Message Subject</h3>
                                    <p>Write a short, clear subject line</p>
                                </div>
                                <span class="form-badge required-badge">Required</span>
                            </div>
                            <div class="form-pro-card-body">
                                <div class="form-field form-field-pro">
                                    <label><i class="fas fa-tag"></i> Subject</label>
                                    <input type="text" name="subject" required placeholder="Seminar one requirements" value="{{ old('subject') }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-pro-card">
                            <div class="form-pro-card-header">
                                <span class="form-step-badge">03</span>
                                <div>
                                    <h3>Your Message</h3>
                                    <p>Explain your question or request in detail</p>
                                </div>
                                <span class="form-badge optional-badge">Optional</span>
                            </div>
                            <div class="form-pro-card-body">
                                <div class="form-field form-field-pro">
                                    <label><i class="fas fa-comment-alt"></i> Message Body</label>
                                    <textarea name="Message" rows="6" placeholder="Write your message here...">{{ old('Message') }}</textarea>
                                </div>
                                @error('Message')
                                <span class="error-text">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-pro-notice">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <strong>Professional communication</strong>
                                <span>Be specific about your project, seminar phase, and what you need from your supervisor. Messages are stored and can be viewed in your replies.</span>
                            </div>
                        </div>

                        <div class="form-pro-actions">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-paper-plane"></i> Send Message
                            </button>
                            <button type="reset" class="btn-secondary">
                                <i class="fas fa-undo"></i> Clear Form
                            </button>
                        </div>
                    </form>
                </div>

                <aside class="form-pro-sidebar">
                    <div class="sidebar-card">
                        <h4><i class="fas fa-envelope-open-text"></i> Messaging</h4>
                        <div class="sidebar-stat">
                            <span>Supervisors available</span>
                            <strong>{{ $supervisorCount }}</strong>
                        </div>
                        <div class="sidebar-stat">
                            <span>Response time</span>
                            <strong>1–3 days</strong>
                        </div>
                        <div class="sidebar-stat">
                            <span>Logged in as</span>
                            <strong>{{ auth()->user()?->name ?? 'Student' }}</strong>
                        </div>
                    </div>

                    <div class="sidebar-card">
                        <h4><i class="fas fa-list-check"></i> Writing Tips</h4>
                        <ul class="sidebar-checklist">
                            <li><i class="fas fa-check"></i> Mention your project name or ID if relevant</li>
                            <li><i class="fas fa-check"></i> State which seminar phase you are asking about</li>
                            <li><i class="fas fa-check"></i> Be polite and concise — one topic per message</li>
                            <li><i class="fas fa-check"></i> Check replies regularly in the Replies section</li>
                        </ul>
                    </div>

                    <a href="{{ url('/StudentDashboard/replay') }}" class="sidebar-link">
                        <i class="fas fa-reply"></i> View Supervisor Replies
                    </a>
                    <button type="button" class="sidebar-link outline dash-tab-trigger" data-tab="projects">
                        <i class="fas fa-folder-open"></i> Browse Projects
                    </button>
                </aside>
            </div>
        </div>

        {{-- SETTINGS --}}
        <div id="settings" class="tab-content">
            <div class="tab-panel-header">
                <h2><i class="fas fa-cog"></i> Workspace Settings</h2>
                <p>Customize your dashboard experience and appearance preferences.</p>
            </div>

            <div class="settings-grid">
                <div class="setting-card">
                    <div class="setting-info">
                        <i class="fas fa-moon"></i>
                        <span>Dark Mode</span>
                    </div>
                    <div class="toggle-switch off" id="dark-toggle"></div>
                </div>
                <div class="setting-card">
                    <div class="setting-info">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                    </div>
                    <div class="toggle-switch off"></div>
                </div>
                <div class="setting-card">
                    <div class="setting-info">
                        <i class="fas fa-lock"></i>
                        <span>Privacy Mode</span>
                    </div>
                    <div class="toggle-switch"></div>
                </div>
                <div class="setting-card">
                    <div class="setting-info">
                        <i class="fas fa-palette"></i>
                        <span>Accent Color</span>
                    </div>
                    <div class="color-badge">Dark Blue</div>
                </div>
            </div>

            <div class="settings-note">
                <i class="fas fa-info-circle"></i>
                Dark mode is saved in your browser. Other settings are visual only.
            </div>
        </div>
    </div>

    <div class="dashboard-footer-accent"></div>
</div>
@endsection

@push('scripts')
<script>
    (function() {
        const dashboard = document.querySelector('.dashboard');
        const mode = dashboard?.dataset.enrollmentMode || 'discovery';
        const tabButtons = document.querySelectorAll('#main-tabs .tab-btn');
        const tabTriggers = document.querySelectorAll('.dash-tab-trigger');

        const discoveryTabs = ['dashboard', 'projects', 'request', 'idea', 'message', 'settings'];
        const pendingTabs = ['dashboard', 'pending-status', 'message', 'settings'];
        const enrolledTabs = ['dashboard', 'my-project', 'team', 'timeline', 'progress', 'submissions', 'message', 'settings'];

        const tabIds = mode === 'enrolled' ? enrolledTabs : (mode === 'pending' ? pendingTabs : discoveryTabs);

        function activateTab(tabId) {
            if (!tabIds.includes(tabId)) return;
            tabButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.tab === tabId));
            tabIds.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.toggle('active-content', id === tabId);
            });
        }

        tabButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                if (this.dataset.tab) activateTab(this.dataset.tab);
            });
        });

        tabTriggers.forEach(trigger => {
            trigger.addEventListener('click', function() {
                if (this.dataset.tab) activateTab(this.dataset.tab);
            });
        });

        const urlTab = new URLSearchParams(window.location.search).get('tab');
        const sessionTab = dashboard?.dataset.activeTab || '';
        const initial = [urlTab, sessionTab].find(t => t && tabIds.includes(t)) || 'dashboard';
        activateTab(initial);

        const projectFilters = document.querySelectorAll('[data-project-filter]');
        const projectItems = document.querySelectorAll('.project-item');

        projectFilters.forEach(function(button) {
            button.addEventListener('click', function() {
                const filter = button.dataset.projectFilter;
                projectFilters.forEach(btn => btn.classList.toggle('active', btn === button));
                projectItems.forEach(function(item) {
                    const status = item.dataset.status;
                    item.classList.toggle('hidden', filter !== 'all' && status !== filter);
                });
            });
        });
    })();
</script>

<script>
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