<aside class="auth-brand-panel">
    <div class="auth-brand-top">
        <a href="{{ url('/') }}" class="auth-home-link">
            <i class="fas fa-arrow-left" aria-hidden="true"></i>
            Back to home
        </a>

        <div class="auth-brand">
            <span class="auth-brand-icon auth-brand-icon--logo">
                @include('partials.brand-logo')
            </span>
            <div class="auth-brand-text">
                <strong>Projects Hub</strong>
                <small>Graduation Projects Portal</small>
            </div>
        </div>

        <h2 class="auth-headline">Manage your graduation project in one place</h2>
        <p class="auth-subline">Browse projects, submit requests, track progress, and communicate with your supervisor.</p>

        <ul class="auth-features">
            <li><i class="fas fa-folder-open" aria-hidden="true"></i><span>Discover and request available graduation projects</span></li>
            <li><i class="fas fa-paper-plane" aria-hidden="true"></i><span>Submit ideas and track supervisor responses</span></li>
            <li><i class="fas fa-comments" aria-hidden="true"></i><span>Message your supervisor and upload deliverables</span></li>
        </ul>
    </div>

    <p class="auth-brand-footer">&copy; {{ date('Y') }} Projects Hub · University graduation portal</p>
</aside>
