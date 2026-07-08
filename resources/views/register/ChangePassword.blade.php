@extends('layouts.student')

@section('title', 'Projects Hub · Change Password')

@section('content')
    @php
        $userName = $user?->name ?? 'Student';
        $userInitial = strtoupper(substr($userName, 0, 1));
    @endphp

    <div class="dashboard">
        @include('student.partials.navbar')

        <div class="content-panel">
            <section class="acceptance-hero">
                <div class="acceptance-hero-inner">
                    <div>
                        <nav class="breadcrumb" aria-label="Breadcrumb">
                            <a href="{{ url('/StudentDashboard') }}">Dashboard</a>
                            <span class="sep">/</span>
                            <span>Account Security</span>
                        </nav>
                        <h1>Change Password</h1>
                        <p>Enter your current password, then choose a new one with at least 8 characters. Your session will stay active after updating.</p>
                    </div>
                    <div class="hero-actions">
                        <a href="{{ url('/StudentDashboard') }}" class="btn-hero-outline">
                            <i class="fas fa-arrow-left"></i> Dashboard
                        </a>
                        <a href="{{ url('/StudentDashboard?tab=settings') }}" class="btn-hero-solid">
                            <i class="fas fa-sliders-h"></i> Settings
                        </a>
                    </div>
                </div>
            </section>

            @if (session('success'))
                <div class="form-pro-alert success form-pro-alert--spaced">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <div class="form-pro-layout form-pro-layout--centered">
                <div class="form-pro-main">
                    <div class="password-profile-card">
                        <span class="password-profile-avatar">{{ $userInitial }}</span>
                        <div>
                            <strong>{{ $userName }}</strong>
                            <span>{{ $user?->email ?? 'Student account' }}</span>
                        </div>
                    </div>

                    <form action="/change" method="POST" class="request-form-pro">
                        @csrf

                        <div class="form-pro-card">
                            <div class="form-pro-card-header">
                                <span class="form-step-badge">01</span>
                                <div>
                                    <h3>Verify Current Password</h3>
                                    <p>Enter your existing password to confirm your identity</p>
                                </div>
                                <span class="form-badge required-badge">Required</span>
                            </div>
                            <div class="form-pro-card-body">
                                @if ($user?->email)
                                    <div class="form-field form-field-pro">
                                        <label><i class="fas fa-envelope"></i> Email</label>
                                        <div class="form-readonly-value">{{ $user->email }}</div>
                                    </div>
                                @endif
                                <div class="form-field form-field-pro {{ $user?->email ? 'form-field-pro--spaced' : '' }}">
                                    <label><i class="fas fa-lock"></i> Current Password</label>
                                    <input type="password" name="old" placeholder="Enter your current password" required autocomplete="current-password">
                                </div>
                                @error('old')
                                    <span class="error-text">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-pro-card">
                            <div class="form-pro-card-header">
                                <span class="form-step-badge">02</span>
                                <div>
                                    <h3>New Password</h3>
                                    <p>Choose a strong password and confirm it below</p>
                                </div>
                                <span class="form-badge required-badge">Required</span>
                            </div>
                            <div class="form-pro-card-body">
                                <div class="form-grid">
                                    <div class="form-field form-field-pro">
                                        <label><i class="fas fa-key"></i> New Password</label>
                                        <input type="password" name="new" placeholder="Minimum 8 characters" required autocomplete="new-password">
                                    </div>
                                    <div class="form-field form-field-pro">
                                        <label><i class="fas fa-check-double"></i> Confirm Password</label>
                                        <input type="password" name="password_confirmation" placeholder="Re-enter new password" required autocomplete="new-password">
                                    </div>
                                </div>
                                @error('new')
                                    <span class="error-text">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-pro-notice">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <strong>Security tip</strong>
                                <span>Never share your password. After updating, you will stay signed in on this device.</span>
                            </div>
                        </div>

                        <div class="form-pro-actions">
                            <button type="submit" class="btn-primary" data-loading-label="Updating password...">
                                <i class="fas fa-save"></i> Update Password
                            </button>
                            <a href="{{ url('/StudentDashboard?tab=settings') }}" class="btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>

                <aside class="form-pro-sidebar">
                    <div class="sidebar-card">
                        <h4><i class="fas fa-list-check"></i> Password Guidelines</h4>
                        <ul class="sidebar-checklist">
                            <li><i class="fas fa-check"></i> Use at least 8 characters</li>
                            <li><i class="fas fa-check"></i> Mix letters, numbers, and symbols</li>
                            <li><i class="fas fa-check"></i> Avoid reusing old passwords</li>
                            <li><i class="fas fa-check"></i> Do not use your university ID as a password</li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>

        <div class="dashboard-footer-accent"></div>
    </div>
@endsection
