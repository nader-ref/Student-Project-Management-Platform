<div class="tab-panel-header">
    <h2><i class="fas fa-envelope"></i> Communication Center</h2>
    <p>Reply to student messages and publish announcements to your project teams.</p>
</div>

<div class="acceptance-kpi-grid" style="margin-bottom: 1.5rem;">
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

<div class="tabs-container dash-nav" style="margin-bottom: 1.25rem;">
    <button type="button" class="tab-btn active message-subtab" data-message-panel="inbox">
        <i class="fas fa-inbox"></i> Student Inbox
        @if ($pendingReplyCount > 0)
            <span class="form-badge optional-badge badge-inline">{{ $pendingReplyCount }}</span>
        @endif
    </button>
    <button type="button" class="tab-btn message-subtab" data-message-panel="announce">
        <i class="fas fa-bullhorn"></i> Send Announcement
    </button>
    <button type="button" class="tab-btn message-subtab" data-message-panel="sent">
        <i class="fas fa-paper-plane"></i> Sent Announcements
    </button>
</div>

<div id="message-inbox" class="message-panel active-content">
    @if ($inboxMessages->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-envelope-open"></i></div>
            <h3>No messages yet</h3>
            <p>Students have not contacted you. Messages will appear here when they use the Contact tab.</p>
        </div>
    @else
        <div class="request-list">
            @foreach ($inboxMessages as $msg)
                @php $hasReply = !empty($msg->Replay); @endphp
                <article class="request-item {{ $hasReply ? 'is-accepted' : 'is-pending' }}">
                    <div class="request-item-body">
                        <div class="request-item-top">
                            <div>
                                <div class="request-ref">
                                    <i class="fas fa-user-graduate"></i> {{ $msg->student?->email ?? '—' }}
                                </div>
                                <h3>{{ $msg->subject }}</h3>
                            </div>
                            <span class="status-pill {{ $hasReply ? 'accepted' : 'pending' }}">
                                <i class="fas fa-{{ $hasReply ? 'check-circle' : 'clock' }}"></i>
                                {{ $hasReply ? 'Replied' : 'Awaiting Reply' }}
                            </span>
                        </div>
                        <div class="meta-block team-block">
                            <label>Message</label>
                            <span>{{ $msg->Message }}</span>
                        </div>
                        <div class="request-meta-grid">
                            <div class="meta-block">
                                <label>Student</label>
                                <span>{{ $msg->student?->name ?? 'Unknown student' }}</span>
                            </div>
                            <div class="meta-block">
                                <label>Received</label>
                                <span>{{ $msg->created_at?->format('M d, Y H:i') ?? '—' }}</span>
                            </div>
                        </div>
                        @if ($hasReply)
                            <div class="reply-bubble">
                                <label><i class="fas fa-reply"></i> Your Reply</label>
                                {{ $msg->Replay }}
                            </div>
                        @else
                            <form action="{{ url('/supervisor/reply') }}" method="POST" class="reply-form-inline">
                                @csrf
                                <input type="hidden" name="contact_id" value="{{ $msg->id }}">
                                <div class="form-field form-field-pro">
                                    <label><i class="fas fa-reply"></i> Write Reply</label>
                                    <textarea name="replay" rows="3" required placeholder="Type your response to the student..."></textarea>
                                </div>
                                <div class="form-pro-actions" style="padding: 0; margin-top: 0.75rem;">
                                    <button type="submit" class="btn-primary">
                                        <i class="fas fa-paper-plane"></i> Send Reply
                                    </button>
                                </div>
                            </form>
                        @endif
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
                <div class="form-field form-field-pro" style="margin-top: 1rem;">
                    <label><i class="fas fa-align-left"></i> Message</label>
                    <textarea name="message" rows="5" required placeholder="Write your announcement..."></textarea>
                </div>
                <div class="form-pro-actions" style="padding: 0; margin-top: 1rem;">
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
        <div class="request-list">
            @foreach ($announcements as $ann)
                <article class="request-item is-accepted">
                    <div class="request-item-body">
                        <div class="request-item-top">
                            <div>
                                <div class="request-ref">
                                    <i class="fas fa-folder"></i> {{ $ann->project?->name ?? '—' }}
                                </div>
                                <h3>{{ $ann->subject }}</h3>
                            </div>
                            <span class="status-pill accepted">
                                <i class="fas fa-check"></i> Published
                            </span>
                        </div>
                        <div class="meta-block team-block">
                            <label>Message</label>
                            <span>{{ $ann->Message }}</span>
                        </div>
                        <div class="meta-block">
                            <label>Sent</label>
                            <span>{{ $ann->created_at?->format('M d, Y H:i') ?? '—' }}</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
