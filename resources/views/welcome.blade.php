<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects Hub | University Graduation Projects</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f6f9fc;
            color: #0a2942;
        }

        .hero-pattern {
            background:
                radial-gradient(circle at top left, rgba(79, 195, 247, 0.20), transparent 32rem),
                radial-gradient(circle at 82% 12%, rgba(10, 41, 66, 0.12), transparent 28rem),
                linear-gradient(135deg, #f8fbff 0%, #eef6fb 48%, #ffffff 100%);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(214, 226, 239, 0.95);
            box-shadow: 0 24px 70px rgba(10, 41, 66, 0.10);
            backdrop-filter: blur(16px);
        }

        .soft-card {
            background: #ffffff;
            border: 1px solid #dce6f3;
            box-shadow: 0 18px 45px rgba(10, 41, 66, 0.08);
        }

        .section-label {
            color: #2d7ead;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .brand-gradient {
            background: linear-gradient(135deg, #0a2942 0%, #1f6f9f 55%, #4fc3f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="min-h-screen antialiased">
    <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
            <a href="#home" class="flex items-center gap-3" aria-label="Projects Hub home">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#0a2942] text-white shadow-lg shadow-slate-900/10">
                    <i class="fas fa-cubes" aria-hidden="true"></i>
                </span>
                <span>
                    <span class="block text-lg font-extrabold tracking-tight text-slate-950">Projects Hub</span>
                    <span class="block text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">University Portal</span>
                </span>
            </a>

            <nav class="hidden items-center gap-8 text-sm font-semibold text-slate-600 md:flex" aria-label="Primary navigation">
                <a href="#overview" class="transition hover:text-[#0a2942]">Overview</a>
                <a href="#workflow" class="transition hover:text-[#0a2942]">How it works</a>
                <a href="#features" class="transition hover:text-[#0a2942]">Features</a>
                <a href="#portals" class="transition hover:text-[#0a2942]">Portals</a>
            </nav>

            <a href="{{ route('login') }}" class="hidden rounded-full border border-slate-300 px-5 py-2.5 text-sm font-bold text-[#0a2942] transition hover:border-[#0a2942] hover:bg-slate-50 sm:inline-flex">
                Sign in
            </a>
        </div>
    </header>

    <main id="home">
        <section class="hero-pattern relative overflow-hidden">
            <div class="mx-auto grid max-w-7xl items-center gap-12 px-6 py-20 lg:grid-cols-[1.08fr_0.92fr] lg:px-8 lg:py-28">
                <div>
                    <p class="section-label mb-4">Graduation project management</p>
                    <h1 class="max-w-4xl text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">
                        A professional workspace for university projects from proposal to final submission.
                    </h1>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
                        Projects Hub helps students request projects, submit ideas, communicate with supervisors, upload deliverables, and keep graduation work organized in one place.
                    </p>

                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-full bg-[#0a2942] px-7 py-3.5 text-sm font-bold text-white shadow-xl shadow-slate-900/15 transition hover:-translate-y-0.5 hover:bg-[#123d5c]">
                            Create student account
                            <i class="fas fa-arrow-right ml-2 text-xs" aria-hidden="true"></i>
                        </a>
                        <a href="{{ route('supervisor.login') }}" class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white px-7 py-3.5 text-sm font-bold text-[#0a2942] transition hover:-translate-y-0.5 hover:border-[#0a2942]">
                            Supervisor portal
                        </a>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm font-semibold text-slate-500">
                        <span class="inline-flex items-center gap-2"><i class="fas fa-shield-alt text-[#2d7ead]" aria-hidden="true"></i> Secure access</span>
                        <span class="inline-flex items-center gap-2"><i class="fas fa-users text-[#2d7ead]" aria-hidden="true"></i> Student and supervisor portals</span>
                        <span class="inline-flex items-center gap-2"><i class="fas fa-file-upload text-[#2d7ead]" aria-hidden="true"></i> Submission tracking</span>
                    </div>
                </div>

                <div class="glass-card rounded-[2rem] p-6 sm:p-8">
                    <div class="rounded-[1.5rem] bg-[#0a2942] p-6 text-white shadow-2xl shadow-slate-900/20">
                        <div class="flex items-center justify-between border-b border-white/15 pb-5">
                            <div>
                                <p class="text-sm font-semibold text-sky-200">Project dashboard</p>
                                <h2 class="mt-1 text-2xl font-bold">Graduation Project Portal</h2>
                            </div>
                            <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-sky-100">Live workflow</span>
                        </div>

                        <div class="mt-6 space-y-4">
                            <div class="rounded-2xl bg-white p-5 text-slate-900">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-bold text-slate-500">Current milestone</p>
                                        <h3 class="mt-1 text-xl font-extrabold">Supervisor review</h3>
                                    </div>
                                    <i class="fas fa-clipboard-check rounded-xl bg-sky-100 p-3 text-[#2d7ead]" aria-hidden="true"></i>
                                </div>
                                <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full w-3/4 rounded-full bg-gradient-to-r from-[#0a2942] to-[#4fc3f7]"></div>
                                </div>
                                <p class="mt-3 text-sm font-medium text-slate-500">Ideas, requests, messages, and submissions stay connected.</p>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                                    <p class="text-3xl font-extrabold">4</p>
                                    <p class="mt-1 text-sm text-sky-100">Core project stages</p>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                                    <p class="text-3xl font-extrabold">2</p>
                                    <p class="mt-1 text-sm text-sky-100">Dedicated portals</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="overview" class="px-6 py-16 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                    <article class="soft-card rounded-3xl p-6">
                        <i class="fas fa-folder-open mb-5 rounded-2xl bg-sky-50 p-4 text-xl text-[#2d7ead]" aria-hidden="true"></i>
                        <h3 class="text-lg font-extrabold text-slate-950">Project Requests</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Students can request available graduation projects or propose their own ideas.</p>
                    </article>
                    <article class="soft-card rounded-3xl p-6">
                        <i class="fas fa-user-tie mb-5 rounded-2xl bg-sky-50 p-4 text-xl text-[#2d7ead]" aria-hidden="true"></i>
                        <h3 class="text-lg font-extrabold text-slate-950">Supervisor Review</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Supervisors review requests, approve ideas, and guide student teams.</p>
                    </article>
                    <article class="soft-card rounded-3xl p-6">
                        <i class="fas fa-cloud-upload-alt mb-5 rounded-2xl bg-sky-50 p-4 text-xl text-[#2d7ead]" aria-hidden="true"></i>
                        <h3 class="text-lg font-extrabold text-slate-950">Submissions</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Teams upload deliverables and receive structured supervisor feedback.</p>
                    </article>
                    <article class="soft-card rounded-3xl p-6">
                        <i class="fas fa-comments mb-5 rounded-2xl bg-sky-50 p-4 text-xl text-[#2d7ead]" aria-hidden="true"></i>
                        <h3 class="text-lg font-extrabold text-slate-950">Communication</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Messages keep questions, replies, and announcements easy to follow.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="workflow" class="bg-white px-6 py-20 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="section-label">How it works</p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">A clear path for every graduation project.</h2>
                    <p class="mt-4 text-lg leading-8 text-slate-600">The platform follows the real academic workflow, making responsibilities clear for students and supervisors.</p>
                </div>

                <div class="mt-14 grid gap-6 lg:grid-cols-4">
                    <article class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#0a2942] text-lg font-extrabold text-white">1</span>
                        <h3 class="mt-6 text-xl font-extrabold text-slate-950">Choose or propose</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Students request a listed project or submit a new idea for review.</p>
                    </article>
                    <article class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#0a2942] text-lg font-extrabold text-white">2</span>
                        <h3 class="mt-6 text-xl font-extrabold text-slate-950">Review and approve</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Supervisors accept requests, reject unsuitable ideas, or provide feedback.</p>
                    </article>
                    <article class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#0a2942] text-lg font-extrabold text-white">3</span>
                        <h3 class="mt-6 text-xl font-extrabold text-slate-950">Collaborate</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Messages and announcements keep project guidance visible.</p>
                    </article>
                    <article class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#0a2942] text-lg font-extrabold text-white">4</span>
                        <h3 class="mt-6 text-xl font-extrabold text-slate-950">Submit work</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Teams upload files and supervisors review progress through the dashboard.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="features" class="px-6 py-20 lg:px-8">
            <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
                <div>
                    <p class="section-label">Platform focus</p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">Built for academic coordination, not generic project tracking.</h2>
                    <p class="mt-5 text-lg leading-8 text-slate-600">Every section supports graduation project work: requests, idea acceptance, supervisor feedback, team progress, and final submissions.</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="soft-card rounded-3xl p-6">
                        <h3 class="flex items-center gap-3 text-lg font-extrabold text-slate-950"><i class="fas fa-lock text-[#2d7ead]" aria-hidden="true"></i> Role-based access</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Separate student and supervisor portals keep each dashboard focused.</p>
                    </div>
                    <div class="soft-card rounded-3xl p-6">
                        <h3 class="flex items-center gap-3 text-lg font-extrabold text-slate-950"><i class="fas fa-lightbulb text-[#2d7ead]" aria-hidden="true"></i> Idea review</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Students can propose original ideas and wait for supervisor approval.</p>
                    </div>
                    <div class="soft-card rounded-3xl p-6">
                        <h3 class="flex items-center gap-3 text-lg font-extrabold text-slate-950"><i class="fas fa-chart-line text-[#2d7ead]" aria-hidden="true"></i> Progress visibility</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Dashboards make project state easier to understand at a glance.</p>
                    </div>
                    <div class="soft-card rounded-3xl p-6">
                        <h3 class="flex items-center gap-3 text-lg font-extrabold text-slate-950"><i class="fas fa-paper-plane text-[#2d7ead]" aria-hidden="true"></i> Guided submissions</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Deliverables and reviews stay tied to the correct student project.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="portals" class="bg-[#0a2942] px-6 py-20 text-white lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="text-sm font-extrabold uppercase tracking-[0.18em] text-sky-200">Choose your portal</p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Start from the workspace built for your role.</h2>
                </div>

                <div class="mt-12 grid gap-6 lg:grid-cols-2">
                    <article class="rounded-[2rem] border border-white/10 bg-white p-8 text-slate-950 shadow-2xl shadow-slate-950/20">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-50 text-2xl text-[#2d7ead]">
                            <i class="fas fa-user-graduate" aria-hidden="true"></i>
                        </div>
                        <h3 class="mt-6 text-2xl font-extrabold">Student Portal</h3>
                        <p class="mt-3 leading-7 text-slate-600">Create an account, browse available projects, send requests, submit ideas, message supervisors, and upload deliverables.</p>
                        <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-full bg-[#0a2942] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#123d5c]">Create account</a>
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full border border-slate-300 px-6 py-3 text-sm font-bold text-[#0a2942] transition hover:border-[#0a2942]">Student sign in</a>
                        </div>
                    </article>

                    <article class="rounded-[2rem] border border-white/10 bg-white/10 p-8 text-white shadow-2xl shadow-slate-950/20">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-2xl text-sky-200">
                            <i class="fas fa-chalkboard-teacher" aria-hidden="true"></i>
                        </div>
                        <h3 class="mt-6 text-2xl font-extrabold">Supervisor Portal</h3>
                        <p class="mt-3 leading-7 text-sky-50/85">Manage project offerings, review student requests, approve ideas, respond to messages, and evaluate submitted work.</p>
                        <div class="mt-7">
                            <a href="{{ route('supervisor.login') }}" class="inline-flex items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-bold text-[#0a2942] transition hover:bg-sky-50">Supervisor sign in</a>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200 bg-white px-6 py-8 lg:px-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} Projects Hub. University graduation project portal.</p>
            <div class="flex gap-5 font-semibold">
                <a href="#overview" class="transition hover:text-[#0a2942]">Overview</a>
                <a href="#workflow" class="transition hover:text-[#0a2942]">Workflow</a>
                <a href="#portals" class="transition hover:text-[#0a2942]">Portals</a>
            </div>
        </div>
    </footer>
</body>
</html>
