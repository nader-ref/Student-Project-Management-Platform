@extends($isSupervisor ? 'layouts.supervisor' : 'layouts.student')

@section('title', 'Projects Hub · Notifications')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/studentstyles/notifications.css') }}?v={{ filemtime(public_path('css/studentstyles/notifications.css')) }}">
@endpush

@section('content')
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

            @if (session('success'))
                <div class="form-pro-alert success">{{ session('success') }}</div>
            @endif

            @if ($notifications->isEmpty())
                @include('partials.empty-state', [
                    'wrapperClass' => 'notification-empty',
                    'iconClass' => 'notification-empty-icon',
                    'heading' => 'h2',
                    'icon' => 'fas fa-bell-slash',
                    'title' => 'No notifications yet',
                    'message' => 'When something important happens in your workflow, it will appear here.',
                ])
            @else
                <div class="notification-list">
                    @foreach ($notifications as $notification)
                        @php
                            $data = $notification->data;
                            $isUnread = $notification->read_at === null;
                        @endphp
                        <article class="notification-item {{ $isUnread ? 'notification-item--unread' : '' }}">
                            <div class="notification-item-main">
                                <div class="notification-item-header">
                                    <h2>{{ $data['title'] ?? 'Notification' }}</h2>
                                    @if ($isUnread)
                                        <span class="notification-status-badge">Unread</span>
                                    @else
                                        <span class="notification-status-badge notification-status-badge--read">Read</span>
                                    @endif
                                </div>
                                <p class="notification-item-body">{{ $data['body'] ?? '' }}</p>
                                <div class="notification-item-meta">
                                    <span><i class="fas fa-clock"></i> {{ $notification->created_at->diffForHumans() }}</span>
                                    <span>{{ $notification->created_at->format('M j, Y g:i A') }}</span>
                                </div>
                            </div>
                            <div class="notification-item-actions">
                                @if (! empty($data['action_url']))
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                        @csrf
                                        <input type="hidden" name="redirect" value="1">
                                        <button type="submit" class="btn-notification-primary">
                                            <i class="fas fa-external-link-alt"></i> Open
                                        </button>
                                    </form>
                                @endif
                                @if ($isUnread)
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                        @csrf
                                        <button type="submit" class="btn-notification-secondary">
                                            <i class="fas fa-check"></i> Mark read
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
