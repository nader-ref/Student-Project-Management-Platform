@extends('layouts.auth')

@section('portal', 'student')
@section('title', 'Projects Hub · Student Login')

@section('brand')
    @include('auth.partials.brand-student')
@endsection

@section('content')
    <header class="auth-card-header">
        <span class="auth-portal-badge"><i class="fas fa-user-graduate" aria-hidden="true"></i> Student</span>
        <h1>Welcome back</h1>
        <p>Sign in to access your dashboard, requests, and messages.</p>
    </header>

    <form action="{{ url('/Login') }}" method="POST" class="auth-form">
        @csrf
        <input type="hidden" name="role" value="student">

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
        Supervisor?
        <a href="{{ route('supervisor.login') }}">Sign in to supervisor portal</a>
    </div>

    <p class="auth-secure-note">
        <i class="fas fa-shield-alt" aria-hidden="true"></i>
        Secure student access
    </p>
@endsection
