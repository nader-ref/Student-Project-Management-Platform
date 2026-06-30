<div class="tab-panel-header">
    <h2><i class="fas fa-calendar-alt"></i> Project Timeline</h2>
    <p>Seminar milestones and final presentation schedule.</p>
</div>

@if ($milestones->isEmpty())
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-calendar"></i></div>
        <h3>No dates scheduled</h3>
        <p>Your supervisor has not set seminar dates yet. Contact them via Messages.</p>
        <div class="empty-state-actions">
            <button type="button" class="btn-primary dash-tab-trigger" data-tab="message">
                <i class="fas fa-envelope"></i> Contact Supervisor
            </button>
        </div>
    </div>
@else
    @php $activeMilestoneKey = $milestones->search(fn ($m) => ! $m['is_past']); @endphp
    <div class="milestone-tracker">
        @foreach ($milestones as $index => $milestone)
            <div class="milestone-step {{ $milestone['is_past'] ? 'done' : ($index === $activeMilestoneKey ? 'active' : '') }}">
                <div class="milestone-dot">
                    <i class="fas fa-{{ $milestone['is_past'] ? 'check' : 'calendar' }}"></i>
                </div>
                <div class="milestone-body">
                    <strong>{{ $milestone['label'] }}</strong>
                    <span>{{ $milestone['formatted'] }}</span>
                    @if (! $milestone['is_past'] && $milestone['days_left'] <= 14)
                        <span class="days-badge">{{ $milestone['days_left'] }} day{{ $milestone['days_left'] === 1 ? '' : 's' }} left</span>
                    @elseif ($milestone['is_past'])
                        <span class="status-pill accepted" style="margin-top: 0.35rem; display: inline-flex;">Completed</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
