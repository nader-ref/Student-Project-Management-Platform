<div class="tab-panel-header">
    <h2><i class="fas fa-clock"></i> Application Status</h2>
    <p>Your pending submissions. New requests and ideas are disabled until a decision is made.</p>
</div>

<div class="request-list">
    @if ($pendingRequest)
        @php $reqProject = $projects->firstWhere('id', $pendingRequest->project_id); @endphp
        <article class="request-item is-pending">
            <div class="request-item-body">
                <div class="request-item-top">
                    <div>
                        <div class="request-ref">PROJECT REQUEST</div>
                        <h3>{{ $reqProject->name ?? 'Project #'.$pendingRequest->project_id }}</h3>
                    </div>
                    <span class="status-pill pending"><i class="fas fa-hourglass-half"></i> Under Review</span>
                </div>
                <div class="request-meta-grid">
                    <div class="meta-block">
                        <label>Submitted</label>
                        <span>{{ $pendingRequest->created_at?->format('M d, Y') ?? '—' }}</span>
                    </div>
                    <div class="meta-block">
                        <label>Team Size</label>
                        <span>{{ $pendingRequest->count }} member(s)</span>
                    </div>
                </div>
                <div class="empty-state-actions" style="margin-top: 1rem;">
                    <a href="{{ url('/StudentDashboard/acceptance') }}" class="btn-primary">
                        <i class="fas fa-external-link-alt"></i> Full Request History
                    </a>
                </div>
            </div>
        </article>
    @endif

    @if ($pendingIdea)
        <article class="request-item is-pending">
            <div class="request-item-body">
                <div class="request-item-top">
                    <div>
                        <div class="request-ref">IDEA PROPOSAL</div>
                        <h3>{{ $pendingIdea->projectname }}</h3>
                    </div>
                    <span class="status-pill pending"><i class="fas fa-hourglass-half"></i> Under Review</span>
                </div>
                <div class="request-meta-grid">
                    <div class="meta-block">
                        <label>Supervisor</label>
                        <span>{{ $pendingIdea->supervisor->name ?? 'Supervisor unavailable' }}</span>
                    </div>
                    <div class="meta-block">
                        <label>Submitted</label>
                        <span>{{ $pendingIdea->created_at?->format('M d, Y') ?? '—' }}</span>
                    </div>
                </div>
                <div class="empty-state-actions" style="margin-top: 1rem;">
                    <a href="{{ url('/StudentDashboard/acceptanceidea') }}" class="btn-primary">
                        <i class="fas fa-external-link-alt"></i> Full Idea History
                    </a>
                </div>
            </div>
        </article>
    @endif
</div>
