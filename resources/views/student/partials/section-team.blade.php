@php($teamMembers = collect($teamMembers ?? []))

<div class="tab-panel-header">
    <h2><i class="fas fa-users"></i> My Team</h2>
    <p>Students registered on this project with you.</p>
</div>

@if ($teamMembers->isEmpty())
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-users"></i></div>
        <h3>No team data</h3>
        <p>Team member information has not been recorded for this project yet.</p>
    </div>
@else
    <div class="request-list">
        @foreach ($teamMembers as $member)
            <article class="request-item {{ $member['is_you'] ? 'is-accepted' : '' }}">
                <div class="request-item-body">
                    <div class="request-item-top">
                        <div>
                            <div class="request-ref">
                                <i class="fas fa-user-graduate"></i>
                                {{ $member['is_you'] ? 'You' : 'Team Member' }}
                            </div>
                            <h3>{{ $member['name'] }}</h3>
                        </div>
                        @if ($member['is_you'])
                            <span class="status-pill accepted"><i class="fas fa-star"></i> You</span>
                        @endif
                    </div>
                    <div class="meta-block">
                        <label>Student ID</label>
                        <span>{{ $member['id'] ?? '—' }}</span>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@endif
