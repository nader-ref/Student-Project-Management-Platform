@extends('layouts.auth')

@section('portal', 'student')
@section('title', 'Projects Hub · Reset Password')

@section('brand')
    @include('auth.partials.brand-student')
@endsection

@section('content')
    <header class="auth-card-header">
        <span class="auth-portal-badge"><i class="fas fa-lock" aria-hidden="true"></i> Password Recovery</span>
        <h1>Reset Password</h1>
        <p>Enter your university number and choose a new password.</p>
    </header>

    @error('reset')
        <p class="auth-flash auth-flash--error" role="alert">{{ $message }}</p>
    @enderror

    <form method="POST" action="{{ route('password.update') }}" class="auth-form">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="auth-field">
            <label for="university_number"><i class="fas fa-id-card" aria-hidden="true"></i> University Number</label>
            <input
                type="text"
                id="university_number"
                name="university_number"
                value="{{ old('university_number') }}"
                placeholder="Your university number"
                autocomplete="username"
                required
            >
        </div>

        <div class="auth-field">
            <label for="password"><i class="fas fa-lock" aria-hidden="true"></i> New Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter a new password"
                autocomplete="new-password"
                required
            >
            @error('password')
                <span class="auth-field-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password_confirmation"><i class="fas fa-lock" aria-hidden="true"></i> Confirm Password</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                placeholder="Confirm your new password"
                autocomplete="new-password"
                required
            >
        </div>

        <button type="submit" class="auth-btn">
            <i class="fas fa-check" aria-hidden="true"></i>
            Reset Password
        </button>
    </form>

    <footer class="auth-card-footer">
        <a href="{{ route('login') }}" class="auth-link">Back to Sign In</a>
    </footer>
@endsection
