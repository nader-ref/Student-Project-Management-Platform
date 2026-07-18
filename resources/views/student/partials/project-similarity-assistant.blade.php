{{-- Advisory project similarity check (PM-025B). Never submits ideas. --}}
<div
    class="project-similarity-assistant"
    id="project-similarity-assistant"
    data-endpoint="{{ route('student.ai.similarity') }}"
    data-csrf="{{ csrf_token() }}"
>
    <div class="form-pro-card project-similarity-card">
        <div class="form-pro-card-header">
            <span class="form-step-badge">AI</span>
            <div>
                <h3>Similar Projects Check</h3>
                <p>Optional advisory check — compare your draft before you submit</p>
            </div>
            <span class="form-badge">Advisory</span>
        </div>
        <div class="form-pro-card-body">
            <div class="form-pro-notice project-similarity-notice">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
                <div>
                    <strong>Similarity results are advisory only and are not plagiarism detection.</strong>
                    <span>Compare your draft with existing projects and accepted ideas. This does not submit your idea or notify supervisors.</span>
                </div>
            </div>

            <p class="form-help-text project-similarity-help">
                Uses your current Project Name and Proposal Description. You can write them manually or apply an AI suggestion first.
            </p>

            <div class="project-similarity-actions">
                <button type="button" class="btn-secondary" id="project-similarity-check-btn">
                    <i class="fas fa-search" aria-hidden="true"></i> Check Similar Projects
                </button>
            </div>

            <div class="form-pro-alert error project-similarity-error" id="project-similarity-error" hidden></div>
            <div class="form-pro-alert project-similarity-status" id="project-similarity-status" hidden role="status" aria-live="polite"></div>

            <div class="project-similarity-result" id="project-similarity-result" hidden>
                <div class="project-similarity-result-header">
                    <h4>Similarity results</h4>
                    <p class="project-similarity-disclaimer" id="project-similarity-disclaimer"></p>
                </div>
                <div class="project-similarity-summary" id="project-similarity-summary"></div>
                <div class="project-similarity-matches" id="project-similarity-matches"></div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const root = document.getElementById('project-similarity-assistant');
    if (!root) return;

    const endpoint = root.dataset.endpoint;
    const csrf = root.dataset.csrf;
    const checkBtn = document.getElementById('project-similarity-check-btn');
    const errorBox = document.getElementById('project-similarity-error');
    const statusBox = document.getElementById('project-similarity-status');
    const resultBox = document.getElementById('project-similarity-result');
    const disclaimerEl = document.getElementById('project-similarity-disclaimer');
    const summaryEl = document.getElementById('project-similarity-summary');
    const matchesEl = document.getElementById('project-similarity-matches');

    let lastCheckedFingerprint = null;
    let resultsAreStale = false;

    function getTitleInput() {
        return document.querySelector('#idea form.request-form-pro input[name="projectname"]');
    }

    function getProposalInput() {
        return document.querySelector('#idea form.request-form-pro textarea[name="proposal_description"]');
    }

    function currentFingerprint() {
        const title = ((getTitleInput() && getTitleInput().value) || '').trim();
        const proposal = ((getProposalInput() && getProposalInput().value) || '').trim();
        return title + '\n' + proposal;
    }

    function showError(message) {
        clearStatus();
        const text = (message || '').trim();
        if (!text) {
            clearError();
            return;
        }
        errorBox.hidden = false;
        errorBox.innerHTML = '<i class="fas fa-exclamation-circle" aria-hidden="true"></i> ' + escapeHtml(text);
    }

    function clearError() {
        errorBox.hidden = true;
        errorBox.textContent = '';
    }

    function showStatus(message, tone) {
        const text = (message || '').trim();
        if (!text) {
            clearStatus();
            return;
        }
        statusBox.hidden = false;
        statusBox.classList.toggle('is-unavailable', tone === 'unavailable');
        statusBox.classList.toggle('is-empty', tone === 'empty');
        statusBox.innerHTML = '<i class="fas fa-info-circle" aria-hidden="true"></i> ' + escapeHtml(text);
    }

    function clearStatus() {
        statusBox.hidden = true;
        statusBox.textContent = '';
        statusBox.classList.remove('is-unavailable', 'is-empty');
    }

    function clearResults() {
        resultBox.hidden = true;
        disclaimerEl.textContent = '';
        summaryEl.textContent = '';
        matchesEl.innerHTML = '';
        lastCheckedFingerprint = null;
        resultsAreStale = false;
        root.classList.remove('is-stale');
    }

    function markStaleIfNeeded() {
        if (!lastCheckedFingerprint || resultBox.hidden) return;
        if (currentFingerprint() !== lastCheckedFingerprint) {
            resultsAreStale = true;
            root.classList.add('is-stale');
            if (!summaryEl.querySelector('.project-similarity-stale-hint')) {
                const hint = document.createElement('p');
                hint.className = 'project-similarity-stale-hint';
                hint.textContent = 'Your draft changed since the last check. Run Check Similar Projects again for updated results.';
                summaryEl.appendChild(hint);
            }
        }
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function sourceTypeLabel(sourceType) {
        if (sourceType === 'existing_project') return 'Existing Project';
        if (sourceType === 'accepted_idea') return 'Accepted Idea';
        return 'Record';
    }

    function levelLabel(level) {
        if (level === 'high') return 'High';
        if (level === 'moderate') return 'Moderate';
        return 'Low';
    }

    function recommendationForLevel(level) {
        if (level === 'high') {
            return 'Strong overlap was found. Consider clarifying what makes your idea different.';
        }
        if (level === 'moderate') {
            return 'Related themes were found. Review your objectives and scope for clearer differentiation.';
        }
        return 'Review related records and refine your draft if needed.';
    }

    function overallRecommendation(matches) {
        if (!matches.length) {
            return 'No strong overlap was found in the current project records.';
        }
        const hasHigh = matches.some(function (match) { return match.level === 'high'; });
        return hasHigh
            ? 'Strong overlap was found. Consider clarifying what makes your idea different.'
            : 'Related themes were found. Review your objectives and scope for clearer differentiation.';
    }

    function renderMatches(payload) {
        clearError();
        clearStatus();
        matchesEl.innerHTML = '';
        root.classList.remove('is-stale');
        resultsAreStale = false;

        const matches = Array.isArray(payload.matches) ? payload.matches : [];
        disclaimerEl.textContent = payload.disclaimer
            || 'Similarity results are advisory only and are not plagiarism detection.';

        if (payload.ok === false || payload.mode === 'unavailable') {
            resultBox.hidden = true;
            showStatus(
                payload.message || 'Similarity checking is currently unavailable. You can still submit your idea.',
                'unavailable'
            );
            lastCheckedFingerprint = currentFingerprint();
            return;
        }

        summaryEl.textContent = '';
        const summaryText = document.createElement('p');
        summaryText.className = 'project-similarity-summary-text';
        summaryText.textContent = matches.length
            ? (payload.message || overallRecommendation(matches))
            : (payload.message || 'No significant similarity was found.');
        summaryEl.appendChild(summaryText);

        if (!matches.length) {
            showStatus(
                payload.message || 'No significant similarity was found.',
                'empty'
            );
            resultBox.hidden = false;
            lastCheckedFingerprint = currentFingerprint();
            return;
        }

        matches.forEach(function (match) {
            const card = document.createElement('article');
            card.className = 'project-similarity-match level-' + String(match.level || 'moderate');

            const pct = typeof match.percentage === 'number'
                ? match.percentage.toFixed(1)
                : String(match.percentage || '');

            card.innerHTML =
                '<div class="project-similarity-match-top">' +
                    '<span class="project-similarity-percentage">' + escapeHtml(pct) + '%</span>' +
                    '<span class="project-similarity-level">' + escapeHtml(levelLabel(match.level)) + '</span>' +
                    '<span class="project-similarity-source">' + escapeHtml(sourceTypeLabel(match.source_type)) + '</span>' +
                '</div>' +
                '<h5 class="project-similarity-match-title">' + escapeHtml(match.title || 'Untitled') + '</h5>' +
                '<p class="project-similarity-match-advice">' + escapeHtml(recommendationForLevel(match.level)) + '</p>';

            matchesEl.appendChild(card);
        });

        resultBox.hidden = false;
        lastCheckedFingerprint = currentFingerprint();
    }

    checkBtn.addEventListener('click', function () {
        clearError();
        clearStatus();

        const titleInput = getTitleInput();
        const proposalInput = getProposalInput();
        const title = ((titleInput && titleInput.value) || '').trim();
        const proposalDescription = ((proposalInput && proposalInput.value) || '').trim();

        if (title.length < 3) {
            showError('Please enter a project name with at least 3 characters before checking similarity.');
            if (titleInput) titleInput.focus();
            return;
        }

        checkBtn.disabled = true;
        checkBtn.classList.add('is-loading');
        const originalHtml = checkBtn.innerHTML;
        checkBtn.innerHTML = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Checking...';

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                title: title,
                proposal_description: proposalDescription || null
            }),
            credentials: 'same-origin'
        })
            .then(async function (response) {
                const data = await response.json().catch(function () { return null; });

                if (!response.ok) {
                    if (response.status === 422 && data && data.errors) {
                        const first = Object.values(data.errors)[0];
                        throw new Error(Array.isArray(first) ? first[0] : 'Invalid input.');
                    }
                    if (response.status === 429) {
                        throw new Error('Too many requests. Please wait a moment and try again.');
                    }
                    throw new Error((data && data.message) || 'Unable to check similarity right now.');
                }

                if (!data || typeof data.ok === 'undefined') {
                    throw new Error('Unexpected response from similarity check.');
                }

                renderMatches(data);
            })
            .catch(function (err) {
                clearResults();
                showError((err && err.message) ? err.message : 'Unable to check similarity right now.');
            })
            .finally(function () {
                checkBtn.disabled = false;
                checkBtn.classList.remove('is-loading');
                checkBtn.innerHTML = originalHtml;
            });
    });

    const titleInput = getTitleInput();
    const proposalInput = getProposalInput();
    if (titleInput) {
        titleInput.addEventListener('input', markStaleIfNeeded);
        titleInput.addEventListener('change', markStaleIfNeeded);
    }
    if (proposalInput) {
        proposalInput.addEventListener('input', markStaleIfNeeded);
        proposalInput.addEventListener('change', markStaleIfNeeded);
    }
})();
</script>
