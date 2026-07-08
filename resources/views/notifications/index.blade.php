@extends($isAdmin ? 'layouts.admin' : ($isSupervisor ? 'layouts.supervisor' : 'layouts.student'))

@section('title', 'Projects Hub · Notifications')

@push('styles')
    @unless ($isAdmin)
        <link rel="stylesheet" href="{{ asset('css/studentstyles/notifications.css') }}?v={{ filemtime(public_path('css/studentstyles/notifications.css')) }}">
    @endunless
@endpush

@section('content')
    @if ($isAdmin)
        <div class="admin-page-header">
            <span class="admin-page-header-icon"><i class="fas fa-bell"></i></span>
            <div>
                <h1>Notifications</h1>
                <p>Stay up to date with platform activity and workflow events.</p>
            </div>
        </div>

        @if ($notifications->whereNull('read_at')->isNotEmpty())
            <div class="admin-notifications-toolbar">
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn-hero-outline admin-mark-all-btn">
                        <i class="fas fa-check-double"></i> Mark all as read
                    </button>
                </form>
            </div>
        @endif

        <div class="admin-notifications-page">
            @include('notifications.partials.list')
        </div>
    @else
        <div class="dashboard">
            @if ($isSupervisor)
                @include('supervisor.partials.navbar')
            @else
                @include('student.partials.navbar')
            @endif

            <div class="content-panel notifications-page">
                <section class="acceptance-hero">
                    <div class="acceptance-hero-inner">
                        <div>
                            <nav class="breadcrumb" aria-label="Breadcrumb">
                                <a href="{{ $isSupervisor ? url('/supervisorDashboard') : url('/StudentDashboard') }}">Dashboard</a>
                                <span class="sep">/</span>
                                <span>Notifications</span>
                            </nav>
                            <h1>Notifications</h1>
                            <p>Stay up to date with project requests, messages, and submission activity.</p>
                        </div>
                        @if ($notifications->whereNull('read_at')->isNotEmpty())
                            <div class="hero-actions">
                                <form method="POST" action="{{ route('notifications.read-all') }}">
                                    @csrf
                                    <button type="submit" class="btn-hero-outline">
                                        <i class="fas fa-check-double"></i> Mark all as read
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </section>

                @include('notifications.partials.list')
            </div>

            <div class="dashboard-footer-accent"></div>
        </div>
    @endif
@endsection
