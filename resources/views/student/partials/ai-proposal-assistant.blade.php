{{-- Advisory AI Project Proposal Assistant (PM-024A). Never submits ideas. --}}
<div
    class="ai-proposal-assistant"
    id="ai-proposal-assistant"
    data-endpoint="{{ route('student.ai.proposal') }}"
    data-csrf="{{ csrf_token() }}"
>
    <div class="form-pro-card ai-proposal-card">
        <div class="form-pro-card-header">
            <span class="form-step-badge">AI</span>
            <div>
                <h3>Project Proposal Assistant</h3>
                <p>Optional advisory help — improve your idea wording before you submit</p>
            </div>
            <span class="form-badge">Advisory</span>
        </div>
        <div class="form-pro-card-body">
            <div class="form-pro-notice ai-proposal-notice">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
                <div>
                    <strong>Does not submit your idea</strong>
                    <span>Suggestions stay on this page until you choose to use them. Your idea is only sent when you click Submit Idea below.</span>
                </div>
            </div>

            <div class="form-field form-field-pro">
                <label for="ai-raw-idea"><i class="fas fa-pencil-alt"></i> Describe your raw idea</label>
                <textarea
                    id="ai-raw-idea"
                    class="ai-raw-idea-input"
                    rows="4"
                    maxlength="2000"
                    placeholder="Example: An app that helps students find empty study rooms on campus using live occupancy data..."
                ></textarea>
                <div class="ai-proposal-meta">
                    <span id="ai-char-count">0 / 2000</span>
                    <span class="ai-proposal-hint">Minimum 20 characters</span>
                </div>
            </div>

            <div class="ai-proposal-actions">
                <button type="button" class="btn-secondary" id="ai-generate-btn">
                    <i class="fas fa-magic" aria-hidden="true"></i> Generate suggestion
                </button>
            </div>

            <div class="form-pro-alert error ai-proposal-error" id="ai-proposal-error" hidden></div>
            <div class="form-pro-alert ai-proposal-notice-banner" id="ai-proposal-notice" hidden></div>

            <div class="ai-proposal-result" id="ai-proposal-result" hidden>
                <div class="ai-proposal-result-header">
                    <div>
                        <h4>Suggested proposal</h4>
                        <p class="ai-proposal-disclaimer" id="ai-proposal-disclaimer"></p>
                    </div>
                    <span class="ai-mode-badge" id="ai-mode-badge"></span>
                </div>

                <div class="ai-suggestion-block">
                    <label>Improved title</label>
                    <p id="ai-suggestion-title"></p>
                </div>
                <div class="ai-suggestion-block">
                    <label>Problem statement</label>
                    <p id="ai-suggestion-problem"></p>
                </div>
                <div class="ai-suggestion-block">
                    <label>Objectives</label>
                    <ul id="ai-suggestion-objectives"></ul>
                </div>
                <div class="ai-suggestion-block">
                    <label>Scope</label>
                    <p id="ai-suggestion-scope"></p>
                </div>
                <div class="ai-suggestion-block">
                    <label>Initial functional requirements</label>
                    <ul id="ai-suggestion-requirements"></ul>
                </div>

                <div class="ai-proposal-actions">
                    <button type="button" class="btn-secondary" id="ai-apply-suggestion-btn">
                        <i class="fas fa-file-import" aria-hidden="true"></i> Apply AI Suggestion
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="ai-apply-toast" id="ai-apply-toast" hidden role="status" aria-live="polite"></div>

<script>
(function () {
    const root = document.getElementById('ai-proposal-assistant');
    if (!root) return;

    const endpoint = root.dataset.endpoint;
    const csrf = root.dataset.csrf;
    const textarea = document.getElementById('ai-raw-idea');
    const charCount = document.getElementById('ai-char-count');
    const generateBtn = document.getElementById('ai-generate-btn');
    const errorBox = document.getElementById('ai-proposal-error');
    const noticeBox = document.getElementById('ai-proposal-notice');
    const resultBox = document.getElementById('ai-proposal-result');
    const disclaimerEl = document.getElementById('ai-proposal-disclaimer');
    const modeBadge = document.getElementById('ai-mode-badge');
    const applyBtn = document.getElementById('ai-apply-suggestion-btn');
    const toastEl = document.getElementById('ai-apply-toast');

    /** @type {{title?: string, problem_statement?: string, objectives?: string[], scope?: string|string[], functional_requirements?: string[]}|null} */
    let lastSuggestion = null;
    let toastTimer = null;

    function updateCount() {
        const len = (textarea.value || '').length;
        charCount.textContent = len + ' / 2000';
    }

    function showError(message) {
        clearNotice();
        const text = (message || '').trim();
        if (!text) {
            clearError();
            return;
        }
        errorBox.hidden = false;
        errorBox.innerHTML = '<i class="fas fa-exclamation-circle" aria-hidden="true"></i> ' + text;
    }

    function clearError() {
        errorBox.hidden = true;
        errorBox.textContent = '';
    }

    function showNotice(message) {
        const text = (message || '').trim();
        if (!text) {
            clearNotice();
            return;
        }
        noticeBox.hidden = false;
        noticeBox.innerHTML = '<i class="fas fa-info-circle" aria-hidden="true"></i> ' + text;
    }

    function clearNotice() {
        noticeBox.hidden = true;
        noticeBox.textContent = '';
    }

    function showToast(message) {
        if (!toastEl) return;
        if (toastTimer) {
            clearTimeout(toastTimer);
            toastTimer = null;
        }
        toastEl.textContent = message;
        toastEl.hidden = false;
        toastEl.classList.add('is-visible');
        toastTimer = setTimeout(function () {
            toastEl.classList.remove('is-visible');
            toastEl.hidden = true;
            toastTimer = null;
        }, 4200);
    }

    function renderList(ul, items) {
        ul.innerHTML = '';
        (items || []).forEach(function (item) {
            const li = document.createElement('li');
            li.textContent = item;
            ul.appendChild(li);
        });
    }

    function asBulletItems(value) {
        if (Array.isArray(value)) {
            return value.map(function (item) { return String(item).trim(); }).filter(Boolean);
        }

        const text = String(value || '').trim();
        if (!text) return [];

        const byBreak = text.split(/\n+|;\s*/).map(function (part) { return part.trim(); }).filter(Boolean);
        if (byBreak.length > 1) return byBreak;

        const inOut = text.match(/^(.*?)\s*(Out of scope:\s*.+)$/i);
        if (inOut) {
            const items = [];
            const inScope = inOut[1].replace(/^In scope:\s*/i, '').trim();
            if (inScope) items.push(inScope);
            if (inOut[2].trim()) items.push(inOut[2].trim());
            if (items.length) return items;
        }

        return [text];
    }

    function formatBulletBlock(title, items) {
        const lines = [title];
        (items || []).forEach(function (item) {
            lines.push('• ' + item);
        });
        return lines.join('\n');
    }

    function buildIdeaDescription(suggestion) {
        const problem = String(suggestion.problem_statement || '').trim();
        const objectives = asBulletItems(suggestion.objectives);
        const scopeItems = asBulletItems(suggestion.scope);
        const requirements = asBulletItems(suggestion.functional_requirements);

        const sections = [];

        sections.push('Problem Statement');
        sections.push(problem || '—');
        sections.push('');
        sections.push(formatBulletBlock('Objectives', objectives.length ? objectives : ['—']));
        sections.push('');
        sections.push(formatBulletBlock('Scope', scopeItems.length ? scopeItems : ['—']));
        sections.push('');
        sections.push(formatBulletBlock('Initial Functional Requirements', requirements.length ? requirements : ['—']));

        return sections.join('\n');
    }

    function getProjectNameInput() {
        return document.querySelector('#idea form.request-form-pro input[name="projectname"]');
    }

    function getProposalDescriptionInput() {
        return document.querySelector('#idea form.request-form-pro textarea[name="proposal_description"]');
    }

    function applySuggestionToForm() {
        if (!lastSuggestion) return;

        const proposalField = getProposalDescriptionInput();
        if (!proposalField) return;

        const description = buildIdeaDescription(lastSuggestion);
        const existingProposal = (proposalField.value || '').trim();

        if (existingProposal && !window.confirm('Replace the current proposal description with the AI-generated content?')) {
            return;
        }

        const nameInput = getProjectNameInput();
        if (nameInput) {
            nameInput.value = lastSuggestion.title || '';
        }

        const maxLen = Number(proposalField.getAttribute('maxlength')) || 5000;
        proposalField.value = description.length > maxLen ? description.slice(0, maxLen) : description;
        proposalField.dispatchEvent(new Event('input', { bubbles: true }));

        showToast('AI suggestions have been applied. Please review and edit before submitting.');

        if (nameInput) {
            nameInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            nameInput.focus({ preventScroll: true });
        } else {
            proposalField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            proposalField.focus({ preventScroll: true });
        }
    }

    function renderSuggestion(payload) {
        const suggestion = payload.suggestion || {};
        lastSuggestion = {
            title: suggestion.title || '',
            problem_statement: suggestion.problem_statement || '',
            objectives: Array.isArray(suggestion.objectives) ? suggestion.objectives.slice() : [],
            scope: suggestion.scope || '',
            functional_requirements: Array.isArray(suggestion.functional_requirements)
                ? suggestion.functional_requirements.slice()
                : []
        };

        clearError();
        if (payload.mode === 'fallback') {
            showNotice(payload.notice || 'Ollama was unavailable, so a starter template was used.');
        } else {
            clearNotice();
        }

        disclaimerEl.textContent = payload.disclaimer || '';
        modeBadge.textContent = payload.mode === 'ai' ? 'AI' : 'Fallback';
        modeBadge.classList.toggle('is-ai', payload.mode === 'ai');
        modeBadge.classList.toggle('is-fallback', payload.mode !== 'ai');

        document.getElementById('ai-suggestion-title').textContent = lastSuggestion.title;
        document.getElementById('ai-suggestion-problem').textContent = lastSuggestion.problem_statement;
        document.getElementById('ai-suggestion-scope').textContent = typeof lastSuggestion.scope === 'string'
            ? lastSuggestion.scope
            : asBulletItems(lastSuggestion.scope).join(' ');
        renderList(document.getElementById('ai-suggestion-objectives'), lastSuggestion.objectives);
        renderList(document.getElementById('ai-suggestion-requirements'), lastSuggestion.functional_requirements);

        resultBox.hidden = false;
    }

    textarea.addEventListener('input', updateCount);
    updateCount();

    generateBtn.addEventListener('click', function () {
        clearError();
        clearNotice();
        const rawIdea = (textarea.value || '').trim();

        if (rawIdea.length < 20) {
            showError('Please enter at least 20 characters describing your idea.');
            return;
        }

        generateBtn.disabled = true;
        generateBtn.classList.add('is-loading');
        const originalHtml = generateBtn.innerHTML;
        generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Generating...';

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ raw_idea: rawIdea }),
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
                    throw new Error((data && data.message) || 'Unable to generate a suggestion right now.');
                }

                if (!data || !data.ok || !data.suggestion) {
                    throw new Error('Unexpected response from the assistant.');
                }

                renderSuggestion(data);
            })
            .catch(function (err) {
                showError((err && err.message) ? err.message : 'Unable to generate a suggestion right now.');
            })
            .finally(function () {
                generateBtn.disabled = false;
                generateBtn.classList.remove('is-loading');
                generateBtn.innerHTML = originalHtml;
            });
    });

    applyBtn.addEventListener('click', function () {
        applySuggestionToForm();
    });
})();
</script>
