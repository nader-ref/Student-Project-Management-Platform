@extends('layouts.auth')

@section('portal', 'student')
@section('title', 'Projects Hub · Complete Email')

@section('brand')
    @include('auth.partials.brand-student')
@endsection

@section('content')
    <header class="auth-card-header">
        <span class="auth-portal-badge"><i class="fas fa-envelope" aria-hidden="true"></i> Account Setup</span>
        <h1>Add your email</h1>
        <p>Your account needs an email address before you can access your portal.</p>
    </header>

    <form action="{{ route('profile.complete-email.store') }}" method="POST" class="auth-form">
        @csrf

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

        <button type="submit" class="auth-btn">
            <i class="fas fa-check" aria-hidden="true"></i>
            Save and continue
        </button>
    </form>

    <footer class="auth-card-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="auth-link" style="background:none;border:none;padding:0;cursor:pointer;font:inherit;">
                Sign out
            </button>
        </form>
    </footer>
@endsection
