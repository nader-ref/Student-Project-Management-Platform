@extends('layouts.auth')

@section('portal', 'supervisor')
@section('title', 'Projects Hub · Supervisor Login')

@section('brand')
    @include('auth.partials.brand-supervisor')
@endsection

@section('content')
    <header class="auth-card-header">
        <span class="auth-portal-badge"><i class="fas fa-chalkboard-teacher" aria-hidden="true"></i> Supervisor</span>
        <h1>Supervisor sign in</h1>
        <p>Access your dashboard to manage projects and student requests.</p>
    </header>

    <form action="{{ url('/supervisorSignup') }}" method="POST" class="auth-form">
        @csrf

        <div class="auth-field">
            <label for="name"><i class="fas fa-id-badge" aria-hidden="true"></i> Supervisor name</label>
            <select id="name" name="name" required>
                <option value="" disabled {{ old('name') ? '' : 'selected' }}>Select your name</option>
                @forelse ($supervisorNames as $name)
                    <option value="{{ $name }}" @selected(old('name') === $name)>{{ $name }}</option>
                @empty
                    <option value="" disabled>No supervisors registered yet</option>
                @endforelse
            </select>
            @error('name')
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
                placeholder="Enter your password"
                autocomplete="current-password"
                required
            >
            @error('password')
                <span class="auth-field-error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="auth-btn">
            <i class="fas fa-arrow-right-to-bracket" aria-hidden="true"></i>
            Sign in
        </button>
    </form>

    <div class="auth-switch-portal">
        Student?
        <a href="{{ route('login') }}">Sign in to student portal</a>
    </div>

    <p class="auth-secure-note">
        <i class="fas fa-shield-alt" aria-hidden="true"></i>
        Authorized supervisor access only
    </p>
@endsection
