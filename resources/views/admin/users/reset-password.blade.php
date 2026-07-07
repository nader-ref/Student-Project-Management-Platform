@extends('layouts.admin')

@section('title', 'Reset Password · Admin · Projects Hub')

@section('content')
    <div class="admin-page-header">
        <h1>Reset Password</h1>
        <p>Set a temporary password for {{ $user['name'] }}.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            Please correct the errors below and try again.
        </div>
    @endif

    <section class="summary-card">
        <h2>User summary</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <span>Name</span>
                <strong>{{ $user['name'] }}</strong>
            </div>
            <div class="summary-item">
                <span>University number</span>
                <strong>{{ $user['university_number'] }}</strong>
            </div>
            <div class="summary-item">
                <span>Role</span>
                <strong><span class="badge badge-neutral">{{ $user['role'] }}</span></strong>
            </div>
            <div class="summary-item">
                <span>Account status</span>
                <strong>
                    <span @class([
                        'badge',
                        'badge-success' => $user['is_active'],
                        'badge-danger' => ! $user['is_active'],
                    ])>{{ $user['status'] }}</span>
                </strong>
            </div>
            <div class="summary-item">
                <span>Email status</span>
                <strong>
                    <span @class([
                        'badge',
                        'badge-success' => $user['email_status'] === 'Complete',
                        'badge-pending' => $user['email_status'] === 'Pending',
                    ])>{{ $user['email_status'] }}</span>
                </strong>
            </div>
        </div>
    </section>

    <p class="note">Set a temporary password and share it securely with the user.</p>

    @if (! $user['is_active'])
        <p class="note note--inactive">This account is inactive and cannot log in until activated.</p>
    @endif

    <section class="form-card">
        <form method="POST" action="{{ route('admin.users.reset-password.store', $user['id']) }}" class="form-grid">
            @csrf

            <div @class(['form-field', 'has-error' => $errors->has('password')])>
                <label for="password">New password</label>
                <input id="password" name="password" type="password" required autocomplete="new-password">
                @error('password')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div @class(['form-field', 'has-error' => $errors->has('password_confirmation')])>
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                @error('password_confirmation')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Reset password</button>
                <a href="{{ route('admin.users') }}" class="btn-link">Back to users</a>
            </div>
        </form>
    </section>
@endsection
