@php
    $teamMembers = collect($teamMembers ?? []);
    $milestones = collect($milestones ?? []);
    $milestoneStates = collect($milestoneStates ?? []);
    $submissions = collect($submissions ?? []);
    $recentActivity = collect($recentActivity ?? []);
    $urgentStates = $milestoneStates->filter(fn ($state) => in_array($state['status_key'], ['due_soon', 'overdue'], true));
@endphp

@if ($urgentStates->isNotEmpty())
    <div class="deadline-alerts">
        @foreach ($urgentStates->take(1) as $state)
            <div class="deadline-alert">
                <i class="fas fa-calendar-exclamation"></i>
                <span>
                    <strong>{{ $state['label'] }}</strong> on {{ $state['formatted'] }}
                </span>
                <span class="days-badge">{{ $state['status_label'] }}</span>
            </div>
        @endforeach
    </div>
@endif

<div class="dash-kpi-grid">
    <div class="kpi-card total">
        <div class="kpi-card-top">
            <span class="kpi-label">My Project</span>
            <span class="kpi-icon"><i class="fas fa-graduation-cap"></i></span>
        </div>
        <div class="kpi-value" style="font-size: 1.15rem; line-height: 1.3;">{{ \Illuminate\Support\Str::limit($enrolledProject->name, 28) }}</div>
        <div class="kpi-meta">Active enrollment</div>
    </div>
    <div class="kpi-card supervisors">
        <div class="kpi-card-top">
            <span class="kpi-label">Supervisor</span>
            <span class="kpi-icon"><i class="fas fa-user-tie"></i></span>
        </div>
        <div class="kpi-value" style="font-size: 1.1rem;">{{ $enrolledProject->supervisor->name ?? '—' }}</div>
        <div class="kpi-meta">Your project guide</div>
    </div>
    <div class="kpi-card available">
        <div class="kpi-card-top">
            <span class="kpi-label">Team Size</span>
            <span class="kpi-icon"><i class="fas fa-users"></i></span>
        </div>
        <div class="kpi-value">{{ $teamMembers->count() }}</div>
        <div class="kpi-meta">Registered members</div>
    </div>
    <div class="kpi-card pending">
        <div class="kpi-card-top">
            <span class="kpi-label">Progress</span>
            <span class="kpi-icon"><i class="fas fa-chart-line"></i></span>
        </div>
        <div class="kpi-value">{{ $progress['percent'] ?? 0 }}%</div>
        <div class="kpi-meta">{{ $progress['current_phase'] ?? 'In progress' }}</div>
    </div>
</div>

<section class="overview-workspace">
    <div class="overview-workspace-main">
        <div class="overview-section-card next-steps-card">
            <div class="overview-section-header">
                <h3><i class="fas fa-bolt"></i> Your Next Steps</h3>
                <span class="overview-section-hint">What to do now</span>
            </div>

            <div class="next-steps-list">
                @foreach ($nextSteps ?? [] as $step)
                    <article class="next-step-item priority-{{ $step['priority'] }}">
                        <span class="next-step-icon"><i class="{{ $step['icon'] }}"></i></span>
                        <div class="next-step-body">
                            <strong>{{ $step['title'] }}</strong>
                            <p>{{ $step['description'] }}</p>
                        </div>
                        <button type="button" class="btn-next-step dash-tab-trigger" data-tab="{{ $step['tab'] }}">
                            {{ $step['cta'] }}
                        </button>
                    </article>
                @endforeach
            </div>
        </div>

        @if ($progress)
            <div class="overview-section-card progress-snapshot-card">
                <div class="overview-section-header">
                    <h3><i class="fas fa-tasks"></i> Project Progress</h3>
                    <button type="button" class="overview-link-btn dash-tab-trigger" data-tab="progress">
                        Full details <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                <div class="progress-snapshot-top">
                    <div>
                        <div class="progress-snapshot-percent">{{ $progress['percent'] }}%</div>
                        <p class="progress-snapshot-phase">Current phase: <strong>{{ $progress['current_phase'] }}</strong></p>
                    </div>
                    <div class="progress-ring" @style(['--bar-width: '.$progress['percent'].'%' => true])>
                        <span>{{ $progress['percent'] }}%</span>
                    </div>
                </div>

                <div class="kpi-bar progress-snapshot-bar" @style(['--bar-width: '.$progress['percent'].'%' => true])>
                    <div class="kpi-bar-fill"></div>
                </div>

                <div class="progress-snapshot-stats">
                    <span><strong>{{ $submissions->where('status', 'approved')->count() }}</strong> approved</span>
                    <span><strong>{{ $submissions->where('status', 'submitted')->count() }}</strong> pending review</span>
                    <span><strong>{{ $submissions->where('status', 'needs_revision')->count() }}</strong> need revision</span>
                </div>

                <div class="progress-snapshot-steps">
                    @foreach ($progress['steps'] as $step)
                        <span class="progress-pill {{ $step['done'] ? 'done' : 'upcoming' }}" title="{{ $step['status_label'] ?? '' }}">
                            <i class="fas fa-{{ $step['done'] ? 'check' : 'circle' }}"></i>
                            {{ $step['label'] }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <aside class="overview-workspace-side">
        <div class="overview-section-card activity-feed-card">
            <div class="overview-section-header">
                <h3><i class="fas fa-history"></i> Recent Activity</h3>
            </div>

            @if (($recentActivity ?? collect())->isEmpty())
                <div class="overview-empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No activity yet. Upload a file or message your supervisor to get started.</p>
                    <button type="button" class="btn-primary dash-tab-trigger" data-tab="submissions">
                        <i class="fas fa-upload"></i> Submit a File
                    </button>
                </div>
            @else
                <div class="activity-feed">
                    @foreach ($recentActivity as $item)
                        <div class="feed-item tone-{{ $item['tone'] }}">
                            <span class="feed-icon"><i class="{{ $item['icon'] }}"></i></span>
                            <div class="feed-body">
                                <strong>{{ $item['title'] }}</strong>
                                <span>{{ $item['meta'] }}</span>
                                <time>{{ $item['timestamp']->diffForHumans() }}</time>
                            </div>
                            @if ($item['tab'])
                                <button type="button" class="feed-link dash-tab-trigger" data-tab="{{ $item['tab'] }}" aria-label="Go to related section">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="overview-section-card project-mini-card">
            <div class="overview-section-header">
                <h3><i class="fas fa-folder-open"></i> Project Summary</h3>
                <button type="button" class="overview-link-btn dash-tab-trigger" data-tab="my-project">
                    Details <i class="fas fa-arrow-right"></i>
                </button>
            </div>
            <div class="project-mini-meta">
                <div class="meta-block team-block">
                    <label>Department</label>
                    <span>{{ $deptLabels[$enrolledProject->department] ?? $enrolledProject->department ?? '—' }}</span>
                </div>
                <div class="meta-block team-block">
                    <label>Description</label>
                    <span>{{ \Illuminate\Support\Str::limit($enrolledProject->description ?? 'No description provided.', 140) }}</span>
                </div>
            </div>
            @if ($milestoneStates->filter(fn ($state) => in_array($state['status_key'], ['due_soon', 'upcoming'], true))->isNotEmpty())
                <div class="project-mini-milestones">
                    <label>Upcoming</label>
                    @foreach ($milestoneStates->filter(fn ($state) => in_array($state['status_key'], ['due_soon', 'upcoming'], true))->take(2) as $state)
                        <span class="mini-milestone-chip">
                            <i class="fas fa-calendar"></i>
                            {{ $state['label'] }} · {{ $state['status_label'] }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </aside>
</section>
