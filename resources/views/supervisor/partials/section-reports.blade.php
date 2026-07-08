<div class="tab-panel-header">
    <h2><i class="fas fa-chart-pie"></i> Reports & Analytics</h2>
    <p>Overview of your project portfolio, acceptance rates, and department distribution.</p>
</div>

<div class="reports-grid">
    <div class="report-card">
        <h3><i class="fas fa-layer-group"></i> Project Summary</h3>
        <div class="report-stat-row"><span>Total Projects</span><span>{{ $totalProjects }}</span></div>
        <div class="report-stat-row"><span>Available</span><span>{{ $availableProjects }}</span></div>
        <div class="report-stat-row"><span>Taken</span><span>{{ $takenProjects }}</span></div>
        <div class="report-stat-row"><span>Utilization Rate</span><span>{{ $takenRate }}%</span></div>
    </div>

    <div class="report-card">
        <h3><i class="fas fa-clipboard-check"></i> Request Metrics</h3>
        <div class="report-stat-row"><span>Total Requests</span><span>{{ $totalReqCount }}</span></div>
        <div class="report-stat-row"><span>Accepted</span><span>{{ $acceptedReqCount }}</span></div>
        <div class="report-stat-row"><span>Rejected</span><span>{{ $rejectedReqCount }}</span></div>
        <div class="report-stat-row"><span>Pending</span><span>{{ $pendingReqCount }}</span></div>
        <div class="report-stat-row"><span>Acceptance Rate</span><span>{{ $requestAcceptRate }}%</span></div>
    </div>

    <div class="report-card">
        <h3><i class="fas fa-lightbulb"></i> Idea Metrics</h3>
        <div class="report-stat-row"><span>Total Ideas</span><span>{{ $totalIdeaCount }}</span></div>
        <div class="report-stat-row"><span>Accepted</span><span>{{ $acceptedIdeaCount }}</span></div>
        <div class="report-stat-row"><span>Rejected</span><span>{{ $rejectedIdeaCount }}</span></div>
        <div class="report-stat-row"><span>Pending</span><span>{{ $pendingIdeas->count() }}</span></div>
        <div class="report-stat-row"><span>Acceptance Rate</span><span>{{ $ideaAcceptRate }}%</span></div>
    </div>

    <div class="report-card">
        <h3><i class="fas fa-envelope"></i> Communication</h3>
        <div class="report-stat-row"><span>Messages Received</span><span>{{ $inboxMessages->count() }}</span></div>
        <div class="report-stat-row"><span>Replied</span><span>{{ $inboxMessages->count() - $pendingReplyCount }}</span></div>
        <div class="report-stat-row"><span>Awaiting Reply</span><span>{{ $pendingReplyCount }}</span></div>
        <div class="report-stat-row"><span>Announcements Sent</span><span>{{ $announcements->count() }}</span></div>
    </div>
</div>

<div class="report-card" style="margin-bottom: 1.5rem;">
    <h3><i class="fas fa-building"></i> Projects by Department</h3>
    @if ($byDepartment->isEmpty())
        <p class="report-empty-note">No projects to report yet.</p>
    @else
        @php $maxDept = $byDepartment->max() ?: 1; @endphp
        @foreach ($byDepartment as $dept => $count)
            @php $pct = round(($count / $maxDept) * 100); @endphp
            <div class="report-bar-row">
                <label>
                    <span>{{ $deptLabels[$dept] ?? ucfirst($dept) }}</span>
                    <span>{{ $count }}</span>
                </label>
                <div class="report-bar-track">
                    <div class="report-bar-fill" @style(['--bar-width: '.$pct.'%' => true])></div>
                </div>
            </div>
        @endforeach
    @endif
</div>

<div class="insight-card">
    <h3><i class="fas fa-chart-line"></i> Performance Snapshot</h3>
    <div class="distribution-row">
        <span>Request Acceptance</span>
        <div class="distribution-track" @style(['--bar-width: '.$requestAcceptRate.'%' => true])>
            <div class="distribution-fill taken"></div>
        </div>
        <span>{{ $requestAcceptRate }}%</span>
    </div>
    <div class="distribution-row">
        <span>Idea Acceptance</span>
        <div class="distribution-track" @style(['--bar-width: '.$ideaAcceptRate.'%' => true])>
            <div class="distribution-fill available"></div>
        </div>
        <span>{{ $ideaAcceptRate }}%</span>
    </div>
    <div class="distribution-row">
        <span>Project Utilization</span>
        <div class="distribution-track" @style(['--bar-width: '.$takenRate.'%' => true])>
            <div class="distribution-fill taken"></div>
        </div>
        <span>{{ $takenRate }}%</span>
    </div>
</div>
