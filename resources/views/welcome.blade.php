<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects Hub | University Graduation Projects</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/welcome/welcome.css') }}?v={{ filemtime(public_path('css/welcome/welcome.css')) }}">
</head>
<body class="welcome-page">
    <header class="welcome-header">
        <div class="welcome-container welcome-header-inner">
            <a href="#home" class="welcome-brand" aria-label="Projects Hub home">
                <span class="welcome-brand-icon"><i class="fas fa-cubes" aria-hidden="true"></i></span>
                <span class="welcome-brand-text">
                    <strong>Projects Hub</strong>
                    <small>University Portal</small>
                </span>
            </a>

            <nav class="welcome-nav" aria-label="Primary navigation">
                <a href="#overview">Overview</a>
                <a href="#workflow">How it works</a>
                <a href="#features">Features</a>
                <a href="#portals">Portals</a>
            </nav>

            <a href="{{ route('login') }}" class="welcome-btn-outline welcome-header-signin">Sign in</a>
        </div>
    </header>

    <main id="home">
        <section class="welcome-hero">
            <div class="welcome-container welcome-hero-grid">
                <div>
                    <p class="welcome-section-label">Graduation project management</p>
                    <h1>A professional workspace for university projects from proposal to final submission.</h1>
                    <p class="welcome-hero-lead">
                        Projects Hub helps students request projects, submit ideas, communicate with supervisors, upload deliverables, and keep graduation work organized in one place.
                    </p>

                    <div class="welcome-hero-cta">
                        <a href="{{ route('login') }}" class="welcome-btn-primary">
                            Sign in
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>

                    <div class="welcome-trust-row">
                        <span><i class="fas fa-shield-alt" aria-hidden="true"></i> Secure access</span>
                        <span><i class="fas fa-users" aria-hidden="true"></i> Student and supervisor portals</span>
                        <span><i class="fas fa-file-upload" aria-hidden="true"></i> Submission tracking</span>
                    </div>
                </div>

                <div class="welcome-glass-card">
                    <div class="welcome-preview">
                        <div class="welcome-preview-top">
                            <div>
                                <p>Project dashboard</p>
                                <h2>Graduation Project Portal</h2>
                            </div>
                            <span class="welcome-preview-badge">Live workflow</span>
                        </div>

                        <div class="welcome-preview-body">
                            <div class="welcome-preview-highlight">
                                <div class="welcome-preview-highlight-top">
                                    <div>
                                        <p>Current milestone</p>
                                        <h3>Supervisor review</h3>
                                    </div>
                                    <span class="welcome-preview-highlight-icon"><i class="fas fa-clipboard-check" aria-hidden="true"></i></span>
                                </div>
                                <div class="welcome-progress" aria-hidden="true"><div class="welcome-progress-fill"></div></div>
                                <p class="welcome-preview-note">Ideas, requests, messages, and submissions stay connected.</p>
                            </div>

                            <div class="welcome-preview-stats">
                                <div class="welcome-preview-stat">
                                    <strong>4</strong>
                                    <span>Core project stages</span>
                                </div>
                                <div class="welcome-preview-stat">
                                    <strong>2</strong>
                                    <span>Dedicated portals</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="overview" class="welcome-section">
            <div class="welcome-container">
                <div class="welcome-grid-4">
                    <article class="welcome-soft-card">
                        <i class="fas fa-folder-open feature-icon" aria-hidden="true"></i>
                        <h3>Project Requests</h3>
                        <p>Students can request available graduation projects or propose their own ideas.</p>
                    </article>
                    <article class="welcome-soft-card">
                        <i class="fas fa-user-tie feature-icon" aria-hidden="true"></i>
                        <h3>Supervisor Review</h3>
                        <p>Supervisors review requests, approve ideas, and guide student teams.</p>
                    </article>
                    <article class="welcome-soft-card">
                        <i class="fas fa-cloud-upload-alt feature-icon" aria-hidden="true"></i>
                        <h3>Submissions</h3>
                        <p>Teams upload deliverables and receive structured supervisor feedback.</p>
                    </article>
                    <article class="welcome-soft-card">
                        <i class="fas fa-comments feature-icon" aria-hidden="true"></i>
                        <h3>Communication</h3>
                        <p>Messages keep questions, replies, and announcements easy to follow.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="workflow" class="welcome-section welcome-section--white">
            <div class="welcome-container">
                <div class="welcome-section-heading">
                    <p class="welcome-section-label">How it works</p>
                    <h2>A clear path for every graduation project.</h2>
                    <p>The platform follows the real academic workflow, making responsibilities clear for students and supervisors.</p>
                </div>

                <div class="welcome-grid-4 welcome-grid-4--steps">
                    <article class="welcome-step-card">
                        <span class="welcome-step-num">1</span>
                        <h3>Choose or propose</h3>
                        <p>Students request a listed project or submit a new idea for review.</p>
                    </article>
                    <article class="welcome-step-card">
                        <span class="welcome-step-num">2</span>
                        <h3>Review and approve</h3>
                        <p>Supervisors accept requests, reject unsuitable ideas, or provide feedback.</p>
                    </article>
                    <article class="welcome-step-card">
                        <span class="welcome-step-num">3</span>
                        <h3>Collaborate</h3>
                        <p>Messages and announcements keep project guidance visible.</p>
                    </article>
                    <article class="welcome-step-card">
                        <span class="welcome-step-num">4</span>
                        <h3>Submit work</h3>
                        <p>Teams upload files and supervisors review progress through the dashboard.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="features" class="welcome-section">
            <div class="welcome-container welcome-split">
                <div>
                    <p class="welcome-section-label">Platform focus</p>
                    <h2>Built for academic coordination, not generic project tracking.</h2>
                    <p>Every section supports graduation project work: requests, idea acceptance, supervisor feedback, team progress, and final submissions.</p>
                </div>

                <div class="welcome-grid-2">
                    <div class="welcome-soft-card">
                        <h3 class="welcome-feature-title"><i class="fas fa-lock" aria-hidden="true"></i> Role-based access</h3>
                        <p>Separate student and supervisor portals keep each dashboard focused.</p>
                    </div>
                    <div class="welcome-soft-card">
                        <h3 class="welcome-feature-title"><i class="fas fa-lightbulb" aria-hidden="true"></i> Idea review</h3>
                        <p>Students can propose original ideas and wait for supervisor approval.</p>
                    </div>
                    <div class="welcome-soft-card">
                        <h3 class="welcome-feature-title"><i class="fas fa-chart-line" aria-hidden="true"></i> Progress visibility</h3>
                        <p>Dashboards make project state easier to understand at a glance.</p>
                    </div>
                    <div class="welcome-soft-card">
                        <h3 class="welcome-feature-title"><i class="fas fa-paper-plane" aria-hidden="true"></i> Guided submissions</h3>
                        <p>Deliverables and reviews stay tied to the correct student project.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="portals" class="welcome-section welcome-portals">
            <div class="welcome-container">
                <div class="welcome-section-heading">
                    <p class="welcome-section-label">Choose your portal</p>
                    <h2>Start from the workspace built for your role.</h2>
                </div>

                <article class="welcome-portal-card">
                    <div class="welcome-portal-card-icon"><i class="fas fa-right-to-bracket" aria-hidden="true"></i></div>
                    <h3>University Portal</h3>
                    <p>Students, supervisors, and administrators sign in with their university number. New accounts are created by the administrator.</p>
                    <a href="{{ route('login') }}" class="welcome-btn-primary">Sign in</a>
                </article>
            </div>
        </section>
    </main>

    <footer class="welcome-footer">
        <div class="welcome-container welcome-footer-inner">
            <p>&copy; {{ date('Y') }} Projects Hub. University graduation project portal.</p>
            <div class="welcome-footer-links">
                <a href="#overview">Overview</a>
                <a href="#workflow">Workflow</a>
                <a href="#portals">Portals</a>
            </div>
        </div>
    </footer>
</body>
</html>
