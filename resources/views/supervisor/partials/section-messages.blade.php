<div class="tab-panel-header">
    <h2><i class="fas fa-envelope"></i> Communication Center</h2>
    <p>Reply to student messages and publish announcements to your project teams.</p>
</div>

<div class="acceptance-kpi-grid messages-kpi-grid">
    <div class="kpi-card total">
        <div class="kpi-card-top">
            <span class="kpi-label">Inbox</span>
            <span class="kpi-icon"><i class="fas fa-inbox"></i></span>
        </div>
        <div class="kpi-value">{{ $inboxMessages->count() }}</div>
        <div class="kpi-meta">Messages from students</div>
    </div>
    <div class="kpi-card pending">
        <div class="kpi-card-top">
            <span class="kpi-label">Awaiting Reply</span>
            <span class="kpi-icon"><i class="fas fa-clock"></i></span>
        </div>
        <div class="kpi-value">{{ $pendingReplyCount }}</div>
        <div class="kpi-meta">Need your response</div>
    </div>
    <div class="kpi-card accepted">
        <div class="kpi-card-top">
            <span class="kpi-label">Announcements</span>
            <span class="kpi-icon"><i class="fas fa-bullhorn"></i></span>
        </div>
        <div class="kpi-value">{{ $announcements->count() }}</div>
        <div class="kpi-meta">Published to students</div>
    </div>
</div>

<div class="dash-nav-shell dash-nav-shell--compact">
    <nav class="tabs-container dash-nav supervisor-tabs" aria-label="Message sections">
        <button type="button" class="tab-btn active message-subtab" data-message-panel="inbox" title="Student Inbox">
            <i class="fas fa-inbox"></i>
            <span class="tab-label">Student Inbox</span>
            @if ($pendingReplyCount > 0)
                <span class="form-badge optional-badge badge-inline">{{ $pendingReplyCount }}</span>
            @endif
        </button>
        <button type="button" class="tab-btn message-subtab" data-message-panel="announce" title="Send Announcement">
            <i class="fas fa-bullhorn"></i>
            <span class="tab-label">Send Announcement</span>
        </button>
        <button type="button" class="tab-btn message-subtab" data-message-panel="sent" title="Sent Announcements">
            <i class="fas fa-paper-plane"></i>
            <span class="tab-label">Sent Announcements</span>
        </button>
    </nav>
</div>

<div id="message-inbox" class="message-panel active-content">
    @if ($inboxMessages->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-envelope-open"></i></div>
            <h3>No messages yet</h3>
            <p>Students have not contacted you. Messages will appear here when they use the Contact tab.</p>
        </div>
    @else
        <div class="request-list message-thread-list">
            @foreach ($inboxMessages as $msg)
                @php
                    $hasReply = !empty($msg->Replay);
                    $studentName = $msg->student?->name ?? 'Student';
                    $studentInitial = strtoupper(substr($studentName, 0, 1));
                @endphp
                <article class="request-item {{ $hasReply ? 'is-accepted' : 'is-pending' }}">
                    <div class="request-item-body">
                        <div class="request-item-top">
                            <div>
                                <div class="request-ref">
                                    <i class="fas fa-hashtag"></i> MSG-{{ str_pad($msg->id, 4, '0', STR_PAD_LEFT) }}
                                </div>
                                <h3>{{ $msg->subject }}</h3>
                            </div>
                            <span class="status-pill {{ $hasReply ? 'accepted' : 'pending' }}">
                                <i class="fas fa-{{ $hasReply ? 'check-circle' : 'clock' }}"></i>
                                {{ $hasReply ? 'Replied' : 'Awaiting Reply' }}
                            </span>
                        </div>
                        <div class="request-meta-grid">
                            <div class="meta-block">
                                <label>Student</label>
                                <span>{{ $studentName }}</span>
                            </div>
                            <div class="meta-block">
                                <label>Email</label>
                                <span>{{ $msg->student?->email ?? '—' }}</span>
                            </div>
                            <div class="meta-block">
                                <label>Received</label>
                                <span>{{ $msg->created_at?->format('M d, Y H:i') ?? '—' }}</span>
                            </div>
                        </div>

                        <div class="message-chat-thread">
                            <div class="chat-bubble chat-bubble--incoming">
                                <div class="chat-bubble-header">
                                    <span class="chat-bubble-avatar" aria-hidden="true">{{ $studentInitial }}</span>
                                    <span class="chat-bubble-author">{{ $studentName }}</span>
                                    <time datetime="{{ $msg->created_at?->toIso8601String() }}">{{ $msg->created_at?->format('M d, H:i') ?? '—' }}</time>
                                </div>
                                <p class="chat-bubble-text">{{ $msg->Message ?: '—' }}</p>
                            </div>

                            @if ($hasReply)
                                <div class="chat-bubble chat-bubble--outgoing">
                                    <div class="chat-bubble-header">
                                        <span class="chat-bubble-avatar" aria-hidden="true"><i class="fas fa-user-tie"></i></span>
                                        <span class="chat-bubble-author">You</span>
                                        <span class="chat-bubble-status">Replied</span>
                                    </div>
                                    <p class="chat-bubble-text">{{ $msg->Replay }}</p>
                                </div>
                            @else
                                <form action="{{ url('/supervisor/reply') }}" method="POST" class="chat-compose-panel">
                                    @csrf
                                    <input type="hidden" name="contact_id" value="{{ $msg->id }}">
                                    <div class="form-field form-field-pro">
                                        <label><i class="fas fa-reply"></i> Write Reply</label>
                                        <textarea name="replay" rows="3" required placeholder="Type your response to the student..."></textarea>
                                    </div>
                                    <div class="form-pro-actions form-pro-actions--compact">
                                        <button type="submit" class="btn-primary">
                                            <i class="fas fa-paper-plane"></i> Send Reply
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>

<div id="message-announce" class="message-panel tab-content">
    <form method="POST" action="{{ url('/supervisor/announce') }}" class="request-form-pro">
        @csrf
        <div class="form-pro-card message-compose-card">
            <div class="form-pro-card-header">
                <span class="form-step-badge">01</span>
                <div>
                    <h3>New Announcement</h3>
                    <p>Broadcast a message visible to students on their Messages page</p>
                </div>
            </div>
            <div class="form-pro-card-body">
                <div class="form-grid">
                    <div class="form-field form-field-pro">
                        <label><i class="fas fa-folder"></i> Related Project</label>
                        <select name="project_id" required>
                            <option value="" disabled selected>Select project</option>
                            @foreach ($projects as $proj)
                                <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field form-field-pro">
                        <label><i class="fas fa-heading"></i> Subject</label>
                        <input type="text" name="subject" required placeholder="Seminar schedule update">
                    </div>
                </div>
                <div class="form-field form-field-pro form-field-pro--spaced">
                    <label><i class="fas fa-align-left"></i> Message</label>
                    <textarea name="message" rows="5" required placeholder="Write your announcement..."></textarea>
                </div>
                <div class="form-pro-actions form-pro-actions--compact">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-bullhorn"></i> Publish Announcement
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div id="message-sent" class="message-panel tab-content">
    @if ($announcements->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-bullhorn"></i></div>
            <h3>No announcements sent</h3>
            <p>Publish your first announcement to keep students informed.</p>
        </div>
    @else
        <div class="request-list message-thread-list">
            @foreach ($announcements as $ann)
                @php
                    $supervisorInitial = strtoupper(substr($ann->supervisor?->name ?? 'S', 0, 1));
                @endphp
                <article class="request-item is-accepted">
                    <div class="request-item-body">
                        <div class="request-item-top">
                            <div>
                                <div class="request-ref">
                                    <i class="fas fa-hashtag"></i> ANN-{{ str_pad($ann->id, 4, '0', STR_PAD_LEFT) }}
                                </div>
                                <h3>{{ $ann->subject }}</h3>
                            </div>
                            <span class="status-pill accepted">
                                <i class="fas fa-check"></i> Published
                            </span>
                        </div>
                        <div class="request-meta-grid">
                            <div class="meta-block">
                                <label>Project</label>
                                <span>{{ $ann->project?->name ?? '—' }}</span>
                            </div>
                            <div class="meta-block">
                                <label>Sent</label>
                                <span>{{ $ann->created_at?->format('M d, Y H:i') ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="message-chat-thread">
                            <div class="chat-bubble chat-bubble--announcement">
                                <div class="chat-bubble-header">
                                    <span class="chat-bubble-avatar" aria-hidden="true">{{ $supervisorInitial }}</span>
                                    <span class="chat-bubble-author">Announcement</span>
                                    <time datetime="{{ $ann->created_at?->toIso8601String() }}">{{ $ann->created_at?->format('M d, H:i') ?? '—' }}</time>
                                </div>
                                <p class="chat-bubble-text">{{ $ann->Message }}</p>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
