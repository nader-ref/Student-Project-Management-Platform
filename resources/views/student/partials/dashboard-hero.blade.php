@php
    $isEnrolled = $enrollmentMode === 'enrolled';
    $isPending = $enrollmentMode === 'pending';
    $isDiscovery = $enrollmentMode === 'discovery';
    $deptLabels = [
        'software' => 'Software Engineering',
        'ai' => 'Artificial Intelligence',
        'network' => 'Network & Cybersecurity',
    ];
@endphp

<section class="acceptance-hero">
    <div class="acceptance-hero-inner">
        <div>
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <span>Student Portal</span>
                <span class="sep">/</span>
                <span>{{ $isEnrolled ? 'My Project' : 'Dashboard' }}</span>
            </nav>
            @if ($isEnrolled && $enrolledProject)
                <h1>{{ $enrolledProject->name }}</h1>
                <p>
                    You are enrolled in this graduation project.
                    @if ($nextMilestone)
                        <strong>{{ $nextMilestone['label'] }}</strong> in {{ $nextMilestone['days_left'] }} day{{ $nextMilestone['days_left'] === 1 ? '' : 's' }}.
                    @endif
                </p>
            @elseif ($isPending)
                <h1>Welcome back, {{ $studentName }}</h1>
                <p>Your application is under review. Track its status below — you cannot submit new requests until a decision is made.</p>
            @else
                <h1>Welcome back, {{ $studentName }}</h1>
                <p>Manage your projects, submit requests, contact supervisors, and track everything from one workspace.</p>
            @endif
        </div>
        <div class="hero-actions">
            @if ($isEnrolled)
                <button type="button" class="btn-hero-outline dash-tab-trigger" data-tab="my-project">
                    <i class="fas fa-folder-open"></i> Project Details
                </button>
                <button type="button" class="btn-hero-solid dash-tab-trigger" data-tab="timeline">
                    <i class="fas fa-calendar-alt"></i> Timeline
                </button>
            @elseif ($isPending)
                <button type="button" class="btn-hero-outline dash-tab-trigger" data-tab="pending-status">
                    <i class="fas fa-clock"></i> Application Status
                </button>
                <a href="{{ url('/StudentDashboard/replay') }}" class="btn-hero-solid">
                    <i class="fas fa-envelope"></i> Messages
                </a>
            @else
                <button type="button" class="btn-hero-outline dash-tab-trigger" data-tab="projects">
                    <i class="fas fa-folder-open"></i> Browse Projects
                </button>
                <button type="button" class="btn-hero-solid dash-tab-trigger" data-tab="request">
                    <i class="fas fa-plus"></i> New Request
                </button>
            @endif
        </div>
    </div>
</section>

@if ($isEnrolled)
    <div class="enrollment-banner enrolled">
        <i class="fas fa-check-circle"></i>
        <span>You are registered in <strong>{{ $enrolledProject->name }}</strong>. Discovery tabs are hidden — use your project workspace below.</span>
    </div>
@elseif ($isPending)
    <div class="enrollment-banner pending">
        <i class="fas fa-hourglass-half"></i>
        <span>Application pending review. Browse and new submission tabs are temporarily hidden.</span>
    </div>
@endif
