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
    <div class="team-grid">
        @foreach ($teamMembers as $member)
            <article class="team-member-card {{ $member['is_you'] ? 'is-you' : '' }}">
                <span class="team-member-avatar">{{ strtoupper(substr($member['name'], 0, 1)) }}</span>
                <div class="team-member-info">
                    <strong>{{ $member['name'] }}</strong>
                    <span class="team-member-id">
                        <i class="fas fa-id-card"></i>
                        {{ $member['id'] ?? '—' }}
                    </span>
                </div>
                @if ($member['is_you'])
                    <span class="status-pill accepted team-you-badge">
                        <i class="fas fa-star"></i> You
                    </span>
                @else
                    <span class="status-pill pending team-role-badge">Member</span>
                @endif
            </article>
        @endforeach
    </div>
@endif
