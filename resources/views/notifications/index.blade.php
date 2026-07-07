@extends($isSupervisor ? 'layouts.supervisor' : 'layouts.student')

@section('title', 'Projects Hub · Notifications')

@section('content')
    <div class="dashboard">
        @if ($isSupervisor)
            @include('supervisor.partials.navbar')
        @else
            @include('student.partials.navbar')
        @endif

        <div class="content-panel">
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
                <div class="notification-empty">
                    <div class="notification-empty-icon"><i class="fas fa-bell-slash"></i></div>
                    <h2>No notifications yet</h2>
                    <p>When something important happens in your workflow, it will appear here.</p>
                </div>
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

@push('styles')
    <style>
        .notification-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .notification-item {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 1.25rem 1.5rem;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(10, 41, 66, 0.08);
            box-shadow: 0 8px 24px rgba(10, 41, 66, 0.06);
        }

        .notification-item--unread {
            border-left: 4px solid #4fc3f7;
            background: linear-gradient(90deg, rgba(79, 195, 247, 0.08), rgba(255, 255, 255, 0.92) 18%);
        }

        .notification-item-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-bottom: 0.5rem;
        }

        .notification-item-header h2 {
            margin: 0;
            font-size: 1.05rem;
            color: #0a2942;
        }

        .notification-item-body {
            margin: 0 0 0.75rem;
            color: #4a6278;
            line-height: 1.5;
        }

        .notification-item-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            font-size: 0.85rem;
            color: #6b8296;
        }

        .notification-item-actions {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .notification-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            background: rgba(79, 195, 247, 0.15);
            color: #0a7ea4;
        }

        .notification-status-badge--read {
            background: rgba(10, 41, 66, 0.08);
            color: #6b8296;
        }

        .btn-notification-primary,
        .btn-notification-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 0.9rem;
            border-radius: 10px;
            border: none;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-notification-primary {
            background: linear-gradient(135deg, #0a2942, #123d5c);
            color: #fff;
        }

        .btn-notification-secondary {
            background: rgba(10, 41, 66, 0.06);
            color: #0a2942;
        }

        .notification-empty {
            text-align: center;
            padding: 3rem 1.5rem;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px dashed rgba(10, 41, 66, 0.15);
        }

        .notification-empty-icon {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(79, 195, 247, 0.12);
            color: #0a7ea4;
            font-size: 1.5rem;
        }

        .notification-empty h2 {
            margin: 0 0 0.5rem;
            color: #0a2942;
        }

        .notification-empty p {
            margin: 0;
            color: #6b8296;
        }

        body.dark-mode .notification-item {
            background: rgba(18, 45, 68, 0.92);
            border-color: rgba(255, 255, 255, 0.08);
        }

        body.dark-mode .notification-item-header h2,
        body.dark-mode .btn-notification-secondary {
            color: #e8f4fc;
        }

        body.dark-mode .notification-item-body,
        body.dark-mode .notification-item-meta,
        body.dark-mode .notification-empty p {
            color: #a8c4d8;
        }

        body.dark-mode .notification-empty {
            background: rgba(18, 45, 68, 0.92);
        }
    </style>
@endpush
