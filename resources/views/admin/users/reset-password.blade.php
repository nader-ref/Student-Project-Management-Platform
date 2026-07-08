@extends('layouts.admin')

@section('title', 'Reset Password · Admin · Projects Hub')

@section('content')
    @include('admin.partials.page-hero', [
        'title' => 'Reset Password',
        'description' => 'Set a temporary password for ' . $user['name'] . '.',
        'breadcrumb' => '<span>Admin</span><span class="sep">/</span><span>Users</span><span class="sep">/</span><span>Reset password</span>',
    ])

    @if ($errors->any())
        <div class="form-pro-alert error form-pro-alert--spaced">
            <i class="fas fa-exclamation-circle"></i> Please correct the errors below and try again.
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

    <form method="POST" action="{{ route('admin.users.reset-password.store', $user['id']) }}" class="request-form-pro">
        @csrf
        <div class="form-pro-card">
            <div class="form-pro-card-header">
                <span class="form-step-badge"><i class="fas fa-key"></i></span>
                <div>
                    <h3>New password</h3>
                    <p>Enter a secure temporary password for this account.</p>
                </div>
            </div>
            <div class="form-pro-card-body">
                <div @class(['form-field-pro', 'has-error' => $errors->has('password')])>
                    <label for="password">New password</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password">
                    @error('password')<div class="error-text">{{ $message }}</div>@enderror
                </div>

                <div @class(['form-field-pro', 'form-field-pro--spaced', 'has-error' => $errors->has('password_confirmation')])>
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                    @error('password_confirmation')<div class="error-text">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-pro-actions">
                <button type="submit" class="btn-primary"><i class="fas fa-key"></i> Reset password</button>
                <a href="{{ route('admin.users') }}" class="btn-link">Back to users</a>
            </div>
        </div>
    </form>
@endsection
