@php
    $timelineStates = collect($milestoneStates ?? [])->filter(fn ($state) => $state['status_key'] !== 'not_scheduled')->values();
    $activeMilestoneKey = $timelineStates->search(fn ($state) => ! $state['is_done']);
@endphp

<div class="tab-panel-header">
    <h2><i class="fas fa-calendar-alt"></i> Project Timeline</h2>
    <p>Seminar milestones and final presentation schedule.</p>
</div>

@if ($timelineStates->isEmpty())
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
    <div class="milestone-tracker">
        @foreach ($timelineStates as $index => $state)
            @php
                $pillClass = match ($state['status_key']) {
                    'approved' => 'accepted',
                    'revision_required', 'overdue' => 'rejected',
                    'pending_review', 'due_soon' => 'pending',
                    default => 'pending',
                };
            @endphp
            <div class="milestone-step {{ $state['is_done'] ? 'done' : ($index === $activeMilestoneKey ? 'active' : '') }}">
                <div class="milestone-dot">
                    <i class="fas fa-{{ $state['is_done'] ? 'check' : ($state['status_key'] === 'overdue' ? 'exclamation-triangle' : 'calendar') }}"></i>
                </div>
                <div class="milestone-body">
                    <strong>{{ $state['label'] }}</strong>
                    <span>{{ $state['formatted'] }}</span>
                    <span class="status-pill {{ $pillClass }}" style="margin-top: 0.35rem; display: inline-flex;">
                        {{ $state['status_label'] }}
                    </span>
                    @if ($state['latest_submission'])
                        <span style="display: block; margin-top: 0.35rem; color: #64748b; font-size: 0.9rem;">
                            Latest: {{ $state['latest_submission']->title }}
                            ({{ $state['latest_submission']->statusLabel() }})
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
