@extends('layouts.auth')

@section('portal', 'student')
@section('title', 'Projects Hub · Forgot Password')

@section('brand')
    @include('auth.partials.brand-student')
@endsection

@section('content')
    <header class="auth-card-header">
        <span class="auth-portal-badge"><i class="fas fa-key" aria-hidden="true"></i> Password Recovery</span>
        <h1>Forgot Password</h1>
        <p>Enter your university number and we will send a reset link to the email linked to your account.</p>
    </header>

    @if (session('status'))
        <p class="auth-flash auth-flash--success" role="status">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf

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
            @error('university_number')
                <span class="auth-field-error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="auth-btn">
            <i class="fas fa-paper-plane" aria-hidden="true"></i>
            Send Reset Link
        </button>
    </form>

    <footer class="auth-card-footer">
        <a href="{{ route('login') }}" class="auth-link">Back to Sign In</a>
    </footer>
@endsection
