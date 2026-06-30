@extends('layouts.auth')

@section('portal', 'student')
@section('title', 'Projects Hub · Student Sign Up')

@section('brand')
    @include('auth.partials.brand-student')
@endsection

@section('content')
    <header class="auth-card-header">
        <span class="auth-portal-badge"><i class="fas fa-user-graduate" aria-hidden="true"></i> Student</span>
        <h1>Create your account</h1>
        <p>Register to explore projects and start your graduation journey.</p>
    </header>

    <form action="{{ url('/signup') }}" method="POST" class="auth-form">
        @csrf

        <div class="auth-field">
            <label for="name"><i class="fas fa-user" aria-hidden="true"></i> Full name</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                placeholder="Your full name"
                autocomplete="name"
                required
            >
            @error('name')
                <span class="auth-field-error">{{ $message }}</span>
            @enderror
        </div>

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
            <label for="email"><i class="fas fa-envelope" aria-hidden="true"></i> Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="you@university.edu"
                autocomplete="email"
                required
            >
            @error('email')
                <span class="auth-field-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password"><i class="fas fa-lock" aria-hidden="true"></i> Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="At least 8 characters"
                autocomplete="new-password"
                required
            >
            @error('password')
                <span class="auth-field-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password_confirmation"><i class="fas fa-lock" aria-hidden="true"></i> Confirm password</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                placeholder="Repeat your password"
                autocomplete="new-password"
                required
            >
        </div>

        <div class="auth-form-row">
            <label class="auth-checkbox">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <span>Remember me</span>
            </label>
        </div>

        <button type="submit" class="auth-btn">
            <i class="fas fa-user-plus" aria-hidden="true"></i>
            Create account
        </button>
    </form>

    <footer class="auth-card-footer">
        Already have an account?
        <a href="{{ route('login') }}" class="auth-link">Sign in</a>
    </footer>

    <div class="auth-switch-portal">
        Supervisor?
        <a href="{{ route('supervisor.login') }}">Sign in to supervisor portal</a>
    </div>
@endsection
