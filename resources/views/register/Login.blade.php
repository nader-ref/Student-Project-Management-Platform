@extends('layouts.auth')

@section('portal', 'student')
@section('title', 'Projects Hub · Login')

@section('brand')
    @include('auth.partials.brand-student')
@endsection

@section('content')
    <header class="auth-card-header">
        <span class="auth-portal-badge"><i class="fas fa-right-to-bracket" aria-hidden="true"></i> Shared Access</span>
        <h1>Welcome back</h1>
        <p>Sign in once and we will open the right portal for your role.</p>
    </header>

    <form action="{{ url('/Login') }}" method="POST" class="auth-form">
        @csrf

        <div class="auth-field">
            <label for="university_number"><i class="fas fa-id-card" aria-hidden="true"></i> University number</label>
            <input
                type="text"
                id="university_number"
                name="university_number"
                value="{{ old('university_number') }}"
                placeholder="Your university number"
                autocomplete="username"
                required
            >
            @error('university_number')
                <span class="auth-field-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password"><i class="fas fa-lock" aria-hidden="true"></i> Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                autocomplete="current-password"
                required
            >
            @error('password')
                <span class="auth-field-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-form-row">
            <label class="auth-checkbox">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <span>Remember me</span>
            </label>
            <a href="{{ route('password.request') }}" class="auth-link">Forgot password?</a>
        </div>

        <button type="submit" class="auth-btn">
            <i class="fas fa-arrow-right-to-bracket" aria-hidden="true"></i>
            Sign in
        </button>
    </form>

    <footer class="auth-card-footer">
        Don't have an account?
        <a href="{{ route('register') }}" class="auth-link">Create account</a>
    </footer>

    <div class="auth-switch-portal">
        Students, supervisors, and admins use this same sign-in page.
    </div>

    <p class="auth-secure-note">
        <i class="fas fa-shield-alt" aria-hidden="true"></i>
        Secure role-based access
    </p>
@endsection
