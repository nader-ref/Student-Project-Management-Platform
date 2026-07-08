@extends('layouts.student')

@section('title', 'Projects Hub · Messages')

@section('content')
    @php
        $student = auth()->user();
        $studentName = $student?->name ?? 'Student';
        $myMessages = $Messages;
        $repliedCount = $myMessages->filter(fn ($m) => !empty($m->Replay))->count();
        $pendingReplyCount = $myMessages->count() - $repliedCount;
        $totalSent = $myMessages->count();
        $broadcastCount = $supmessages->count();
        $replyRate = $totalSent > 0 ? round(($repliedCount / $totalSent) * 100) : 0;
        $pendingRate = $totalSent > 0 ? round(($pendingReplyCount / $totalSent) * 100) : 0;
    @endphp

    <div class="dashboard messages-center-page">
        @include('student.partials.navbar')

        <div class="content-panel">
            <section class="acceptance-hero">
                <div class="acceptance-hero-inner">
                    <div>
                        <nav class="breadcrumb" aria-label="Breadcrumb">
                            <a href="{{ url('/StudentDashboard') }}">Dashboard</a>
                            <span class="sep">/</span>
                            <span>Messages</span>
                        </nav>
                        <h1>Communication Center</h1>
                        <p>View your sent messages, track supervisor replies, and read announcements from your supervisors.</p>
                    </div>
                    <div class="hero-actions">
                        <a href="{{ url('/StudentDashboard') }}" class="btn-hero-outline">
                            <i class="fas fa-arrow-left"></i> Dashboard
                        </a>
                        <a href="{{ url('/StudentDashboard?tab=message') }}" class="btn-hero-solid">
                            <i class="fas fa-envelope"></i> New Message
                        </a>
                    </div>
                </div>
            </section>

            <div class="acceptance-kpi-grid">
                <div class="kpi-card total">
                    <div class="kpi-card-top">
                        <span class="kpi-label">Messages Sent</span>
                        <span class="kpi-icon"><i class="fas fa-paper-plane"></i></span>
                    </div>
                    <div class="kpi-value">{{ $totalSent }}</div>
                    <div class="kpi-meta">Messages you sent to supervisors</div>
                    <div class="kpi-bar" @style(['--bar-width: 100%' => true])><div class="kpi-bar-fill"></div></div>
                </div>
                <div class="kpi-card accepted">
                    <div class="kpi-card-top">
                        <span class="kpi-label">Replied</span>
                        <span class="kpi-icon"><i class="fas fa-reply"></i></span>
                    </div>
                    <div class="kpi-value">{{ $repliedCount }}</div>
                    <div class="kpi-meta">{{ $replyRate }}% response rate</div>
                    <div class="kpi-bar" @style(['--bar-width: '.$replyRate.'%' => true])><div class="kpi-bar-fill"></div></div>
                </div>
                <div class="kpi-card pending">
                    <div class="kpi-card-top">
                        <span class="kpi-label">Awaiting Reply</span>
                        <span class="kpi-icon"><i class="fas fa-clock"></i></span>
                    </div>
                    <div class="kpi-value">{{ $pendingReplyCount }}</div>
                    <div class="kpi-meta">Still waiting for a response</div>
                    <div class="kpi-bar" @style(['--bar-width: '.$pendingRate.'%' => true])><div class="kpi-bar-fill"></div></div>
                </div>
            </div>

            <div class="dash-nav-shell">
                <nav class="tabs-container dash-nav" id="replay-tabs" aria-label="Message views">
                    <button type="button" class="tab-btn active" data-tab="replay" title="My Messages">
                        <i class="fas fa-inbox"></i>
                        <span class="tab-label">My Messages</span>
                    </button>
                    <button type="button" class="tab-btn" data-tab="messages" title="Supervisor Announcements">
                        <i class="fas fa-bullhorn"></i>
                        <span class="tab-label">Announcements</span>
                        @if ($broadcastCount > 0)
                            <span class="form-badge optional-badge badge-inline">{{ $broadcastCount }}</span>
                        @endif
                    </button>
                </nav>
            </div>

            <div id="replay" class="tab-content active-content">
                <div class="acceptance-toolbar">
                    <div class="toolbar-title">
                        Sent Messages
                        <span>· {{ $totalSent }} message(s)</span>
                    </div>
                    <div class="filter-group" role="group" aria-label="Filter messages">
                        <button type="button" class="filter-btn active" data-message-filter="all">All</button>
                        <button type="button" class="filter-btn" data-message-filter="replied">Replied</button>
                        <button type="button" class="filter-btn" data-message-filter="pending">Awaiting Reply</button>
                    </div>
                </div>

                @if ($myMessages->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-envelope-open"></i>
                        </div>
                        <h3>No messages sent yet</h3>
                        <p>You have not contacted any supervisor. Use the Contact tab on your dashboard to send your first message.</p>
                        <div class="empty-state-actions">
                            <a href="{{ url('/StudentDashboard?tab=message') }}" class="btn-primary">
                                <i class="fas fa-paper-plane"></i> Send a Message
                            </a>
                        </div>
                    </div>
                @else
                    <div class="request-list message-thread-list" id="message-list">
                        @foreach ($myMessages as $message)
                            @php $hasReply = !empty($message->Replay); @endphp
                            <article
                                class="request-item {{ $hasReply ? 'is-accepted' : 'is-pending' }}"
                                data-status="{{ $hasReply ? 'replied' : 'pending' }}"
                            >
                                <div class="request-item-body">
                                    <div class="request-item-top">
                                        <div>
                                            <div class="request-ref">
                                                <i class="fas fa-hashtag"></i> MSG-{{ str_pad($message->id, 4, '0', STR_PAD_LEFT) }}
                                            </div>
                                            <h3>{{ $message->subject }}</h3>
                                        </div>
                                        <span class="status-pill {{ $hasReply ? 'accepted' : 'pending' }}">
                                            <i class="fas fa-{{ $hasReply ? 'check-circle' : 'hourglass-half' }}"></i>
                                            {{ $hasReply ? 'Replied' : 'Awaiting Reply' }}
                                        </span>
                                    </div>

                                    <div class="request-meta-grid">
                                        <div class="meta-block">
                                            <label>To</label>
                                            <span>{{ $message->supervisor?->name ?? '—' }}</span>
                                        </div>
                                        <div class="meta-block">
                                            <label>From</label>
                                            <span>{{ $message->student?->name ?? $studentName }}</span>
                                        </div>
                                        <div class="meta-block">
                                            <label>Sent On</label>
                                            <span>{{ $message->created_at?->format('M d, Y') ?? '—' }}</span>
                                        </div>
                                    </div>

                                    <div class="message-chat-thread">
                                        <div class="chat-bubble chat-bubble--outgoing">
                                            <div class="chat-bubble-header">
                                                <span class="chat-bubble-author">{{ $message->student?->name ?? $studentName }}</span>
                                                <time>{{ $message->created_at?->format('M d, Y') ?? '—' }}</time>
                                            </div>
                                            <p class="chat-bubble-text">{{ $message->Message ?: '—' }}</p>
                                        </div>

                                        <div class="chat-bubble chat-bubble--incoming {{ $hasReply ? '' : 'is-pending' }}">
                                            <div class="chat-bubble-header">
                                                <span class="chat-bubble-author">{{ $message->supervisor?->name ?? 'Supervisor' }}</span>
                                                <span class="chat-bubble-status">{{ $hasReply ? 'Replied' : 'Awaiting reply' }}</span>
                                            </div>
                                            <p class="chat-bubble-text">{{ $message->Replay ?? 'No reply yet — check back later.' }}</p>
                                        </div>
                                    </div>

                                    <div class="request-progress" aria-label="Message progress">
                                        <div class="progress-step done">
                                            <div class="progress-dot"><i class="fas fa-check"></i></div>
                                            <div class="progress-label">Sent</div>
                                        </div>
                                        <div class="progress-step {{ $hasReply ? 'done' : 'active' }}">
                                            <div class="progress-dot"></div>
                                            <div class="progress-label">Reviewed</div>
                                        </div>
                                        <div class="progress-step {{ $hasReply ? 'done active is-final' : '' }}">
                                            <div class="progress-dot">
                                                @if ($hasReply)
                                                    <i class="fas fa-check"></i>
                                                @endif
                                            </div>
                                            <div class="progress-label">Replied</div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

            <div id="messages" class="tab-content">
                <div class="tab-panel-header">
                    <h2><i class="fas fa-bullhorn"></i> Supervisor Announcements</h2>
                    <p>Messages and updates broadcast by your supervisors to students.</p>
                </div>

                @if ($supmessages->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h3>No announcements yet</h3>
                        <p>Your supervisors have not posted any announcements. Check back later for updates.</p>
                    </div>
                @else
                    <div class="request-list message-thread-list">
                        @foreach ($supmessages as $message)
                            <article class="request-item is-available">
                                <div class="request-item-body">
                                    <div class="request-item-top">
                                        <div>
                                            <div class="request-ref">
                                                <i class="fas fa-hashtag"></i> ANN-{{ str_pad($message->id, 4, '0', STR_PAD_LEFT) }}
                                            </div>
                                            <h3>{{ $message->subject }}</h3>
                                        </div>
                                        <span class="status-pill accepted">
                                            <i class="fas fa-user-tie"></i> {{ $message->supervisor?->name ?? '—' }}
                                        </span>
                                    </div>

                                    <div class="request-meta-grid">
                                        <div class="meta-block">
                                            <label>Project</label>
                                            <span>{{ $message->project?->name ?? '—' }}</span>
                                        </div>
                                        <div class="meta-block">
                                            <label>Posted On</label>
                                            <span>{{ $message->created_at?->format('M d, Y') ?? '—' }}</span>
                                        </div>
                                    </div>

                                    <div class="meta-block message-content-block message-content-block--announcement">
                                        <label>Announcement</label>
                                        <div class="chat-bubble chat-bubble--announcement">
                                            <p class="chat-bubble-text">{{ $message->Message }}</p>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="settings-note acceptance-note">
                <i class="fas fa-info-circle"></i>
                Replies may take 1–3 business days. For urgent matters, follow up with a new message referencing your original subject.
            </div>
        </div>

        <div class="dashboard-footer-accent"></div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const mainTabs = document.getElementById('replay-tabs');
            if (!mainTabs) return;

            const tabButtons = mainTabs.querySelectorAll('.tab-btn[data-tab]');
            const contents = {
                replay: document.getElementById('replay'),
                messages: document.getElementById('messages'),
            };

            function activateTab(tabId) {
                tabButtons.forEach(function(btn) {
                    btn.classList.toggle('active', btn.dataset.tab === tabId);
                });
                Object.entries(contents).forEach(function(entry) {
                    const id = entry[0];
                    const section = entry[1];
                    if (section) {
                        section.classList.toggle('active-content', id === tabId);
                    }
                });
            }

            tabButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    activateTab(btn.dataset.tab);
                });
            });

            const filterButtons = document.querySelectorAll('[data-message-filter]');
            const messageItems = document.querySelectorAll('#message-list .request-item');

            filterButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const filter = button.dataset.messageFilter;

                    filterButtons.forEach(btn => btn.classList.toggle('active', btn === button));

                    messageItems.forEach(function(item) {
                        const status = item.dataset.status;
                        item.classList.toggle('hidden', filter !== 'all' && status !== filter);
                    });
                });
            });
        })();
    </script>
@endpush
