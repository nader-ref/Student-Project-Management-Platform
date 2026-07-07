@php
    $deptLabels = $deptLabels ?? [
        'software' => 'Software Engineering',
        'ai' => 'Artificial Intelligence',
        'network' => 'Network & Cybersecurity',
    ];
@endphp

<div class="tab-panel-header">
    <h2><i class="fas fa-folder-open"></i> My Project</h2>
    <p>Full details of your enrolled graduation project.</p>
</div>

<article class="request-item is-accepted">
    <div class="request-item-body">
        <div class="request-item-top">
            <div>
                <div class="request-ref">
                    PRJ-{{ str_pad($enrolledProject->id, 4, '0', STR_PAD_LEFT) }}
                </div>
                <h3>{{ $enrolledProject->name }}</h3>
            </div>
            <span class="status-pill accepted">
                <i class="fas fa-check-circle"></i> Enrolled
            </span>
        </div>
        <div class="request-meta-grid">
            <div class="meta-block">
                <label>Supervisor</label>
                <span>{{ $enrolledProject->supervisor->name ?? '—' }}</span>
            </div>
            <div class="meta-block">
                <label>Department</label>
                <span>{{ $deptLabels[$enrolledProject->department] ?? $enrolledProject->department ?? '—' }}</span>
            </div>
            <div class="meta-block">
                <label>Status</label>
                <span>Active</span>
            </div>
        </div>
        <div class="meta-block team-block">
            <label>Description</label>
            <span>{{ $enrolledProject->description ?? 'No description provided.' }}</span>
        </div>
        <div class="project-tags">
            <span class="project-tag"><i class="fas fa-calendar"></i> S1: {{ $enrolledProject->seminar_1 ? \Carbon\Carbon::parse($enrolledProject->seminar_1)->format('M d, Y') : '—' }}</span>
            <span class="project-tag"><i class="fas fa-flag-checkered"></i> Final: {{ $enrolledProject->final ? \Carbon\Carbon::parse($enrolledProject->final)->format('M d, Y') : '—' }}</span>
        </div>
    </div>
</article>
