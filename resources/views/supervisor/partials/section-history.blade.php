<div class="tab-panel-header">
    <h2><i class="fas fa-history"></i> Processing History</h2>
    <p>Archive of accepted, rejected, and completed requests and idea proposals.</p>
</div>

<div class="history-subtabs" role="group" aria-label="History filters">
    <button type="button" class="history-subtab active" data-history-panel="req-history">Requests ({{ $processedRequests->count() }})</button>
    <button type="button" class="history-subtab" data-history-panel="idea-history">Ideas ({{ $processedIdeas->count() }})</button>
</div>

<div id="req-history" class="history-panel active-content">
    @if ($processedRequests->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-archive"></i></div>
            <h3>No processed requests</h3>
            <p>Accepted and rejected join requests will appear here.</p>
        </div>
    @else
        <div class="request-list">
            @foreach ($processedRequests->sortByDesc('updated_at') as $req)
                @php
                    $proj = $req->project;
                    $isAccepted = $req->accepted == 1;
                    $requestMembers = $req->members->sortBy('position');
                @endphp
                <article class="request-item {{ $isAccepted ? 'is-accepted' : 'is-rejected' }}">
                    <div class="request-item-body">
                        <div class="request-item-top">
                            <div>
                                <div class="request-ref">REQ-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</div>
                                <h3>{{ $proj->name ?? 'Project request' }}</h3>
                            </div>
                            <span class="status-pill {{ $isAccepted ? 'accepted' : 'rejected' }}">
                                <i class="fas fa-{{ $isAccepted ? 'check-circle' : 'times-circle' }}"></i>
                                {{ $isAccepted ? 'Accepted' : 'Rejected' }}
                            </span>
                        </div>
                        <div class="request-meta-grid">
                            <div class="meta-block">
                                <label>Team</label>
                                <span>{{ $requestMembers->pluck('user.name')->implode(', ') }}</span>
                            </div>
                            <div class="meta-block">
                                <label>Processed</label>
                                <span>{{ $req->updated_at?->format('M d, Y') ?? '—' }}</span>
                            </div>
                        </div>
                        @if (!$isAccepted && !empty($req->reason))
                            <div class="reply-bubble">
                                <label>Rejection Reason</label>
                                {{ $req->reason }}
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>

<div id="idea-history" class="history-panel tab-content">
    @if ($processedIdeas->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-lightbulb"></i></div>
            <h3>No processed ideas</h3>
            <p>Accepted and rejected idea proposals will appear here.</p>
        </div>
    @else
        <div class="request-list">
            @foreach ($processedIdeas->sortByDesc('updated_at') as $idea)
                @php $ideaMembers = $idea->members->sortBy('position'); @endphp
                <article class="request-item {{ $idea->accepted ? 'is-accepted' : 'is-rejected' }}">
                    <div class="request-item-body">
                        <div class="request-item-top">
                            <div>
                                <div class="request-ref">IDEA-{{ str_pad($idea->id, 4, '0', STR_PAD_LEFT) }}</div>
                                <h3>{{ $idea->projectname }}</h3>
                                <div class="idea-similarity-row">
                                    @include('supervisor.partials.similarity-badge', ['idea' => $idea])
                                </div>
                            </div>
                            <span class="status-pill {{ $idea->accepted ? 'accepted' : 'rejected' }}">
                                <i class="fas fa-{{ $idea->accepted ? 'check-circle' : 'times-circle' }}"></i>
                                {{ $idea->accepted ? 'Accepted' : 'Rejected' }}
                            </span>
                        </div>
                        <div class="request-meta-grid">
                            <div class="meta-block">
                                <label>Team</label>
                                <span>{{ $ideaMembers->pluck('user.name')->implode(', ') }}</span>
                            </div>
                            <div class="meta-block">
                                <label>Processed</label>
                                <span>{{ $idea->updated_at?->format('M d, Y') ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="idea-proposal-actions">
                            <button
                                type="button"
                                class="btn-secondary js-view-proposal-details"
                                data-proposal-title="{{ $idea->projectname }}"
                                data-proposal-team="{{ $ideaMembers->pluck('user.name')->implode(', ') }}"
                                data-proposal-ref="IDEA-{{ str_pad($idea->id, 4, '0', STR_PAD_LEFT) }}"
                                data-proposal-src="proposal-src-history-{{ $idea->id }}"
                                data-similarity-status="{{ $idea->similarity_status ?? '' }}"
                                data-similarity-label="{{ $idea->similarityDisplayLabel() }}"
                                data-similarity-percentage="{{ $idea->hasSimilarityMatch() ? number_format((float) $idea->similarity_percentage, 1) : '' }}"
                                data-similarity-level="{{ $idea->similarity_level ?? '' }}"
                                data-similarity-match-title="{{ $idea->similarity_match_title ?? '' }}"
                                data-similarity-match-source="{{ $idea->hasSimilarityMatch() ? $idea->similarityMatchSourceLabel() : '' }}"
                                data-similarity-checked-at="{{ $idea->similarity_checked_at?->format('M d, Y H:i') ?? '' }}"
                                data-similarity-model="{{ $idea->similarity_model ?? '' }}"
                            >
                                <i class="fas fa-file-alt"></i> View Proposal Details
                            </button>
                            @if (filled($idea->proposal_description))
                                <pre id="proposal-src-history-{{ $idea->id }}" class="proposal-src-hidden" hidden>{{ $idea->proposal_description }}</pre>
                            @else
                                <pre id="proposal-src-history-{{ $idea->id }}" class="proposal-src-hidden" hidden></pre>
                                <p class="idea-proposal-empty">No proposal details provided.</p>
                            @endif
                        </div>
                        @if ($idea->rejected && !empty($idea->reason))
                            <div class="reply-bubble">
                                <label>Rejection Reason</label>
                                {{ $idea->reason }}
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
