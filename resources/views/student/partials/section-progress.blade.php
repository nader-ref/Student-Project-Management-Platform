<div class="tab-panel-header">
    <h2><i class="fas fa-chart-line"></i> Project Progress</h2>
    <p>Track your graduation project phase and milestone completion.</p>
</div>

@if ($progress)
    <div class="progress-overview-card">
        <div class="progress-overview-top">
            <div>
                <span class="kpi-label">Overall Progress</span>
                <div class="progress-percent">{{ $progress['percent'] }}%</div>
                <p class="progress-phase">Current phase: <strong>{{ $progress['current_phase'] }}</strong></p>
            </div>
            <div class="progress-ring" @style(['--bar-width: '.$progress['percent'].'%' => true])>
                <span>{{ $progress['percent'] }}%</span>
            </div>
        </div>
        <div class="kpi-bar" @style(['--bar-width: '.$progress['percent'].'%' => true])>
            <div class="kpi-bar-fill"></div>
        </div>
    </div>

    <div class="progress-steps-grid">
        @foreach ($progress['steps'] as $step)
            @php
                $pillClass = match ($step['status_key'] ?? '') {
                    'approved' => 'accepted',
                    'revision_required', 'overdue' => 'rejected',
                    'pending_review', 'due_soon', 'upcoming' => 'pending',
                    default => $step['done'] ? 'accepted' : 'pending',
                };
            @endphp
            <div class="progress-step-card {{ $step['done'] ? 'done' : 'upcoming' }}">
                <div class="progress-step-icon">
                    <i class="fas fa-{{ $step['done'] ? 'check' : 'circle' }}"></i>
                </div>
                <div>
                    <strong>{{ $step['label'] }}</strong>
                    @if (!empty($step['date']))
                        <span>{{ $step['date'] }}</span>
                    @endif
                    <span class="status-pill {{ $pillClass }} status-pill--stacked">
                        {{ $step['status_label'] ?? ($step['done'] ? 'Completed' : 'Upcoming') }}
                    </span>
                    @if (!empty($step['latest_submission_title']))
                        <span class="meta-subtext">
                            {{ $step['latest_submission_title'] }}
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="insight-card insight-card--spaced">
        <h3><i class="fas fa-file-upload"></i> Submission Summary</h3>
        <div class="report-stat-row"><span>Total uploads</span><span>{{ $submissions->count() }}</span></div>
        <div class="report-stat-row"><span>Approved</span><span>{{ $submissions->where('status', 'approved')->count() }}</span></div>
        <div class="report-stat-row"><span>Needs revision</span><span>{{ $submissions->where('status', 'needs_revision')->count() }}</span></div>
        <div class="empty-state-actions empty-state-actions--spaced">
            <button type="button" class="btn-primary dash-tab-trigger" data-tab="submissions">
                <i class="fas fa-upload"></i> Submit a File
            </button>
        </div>
    </div>
@endif
