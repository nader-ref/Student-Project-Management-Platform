@extends('layouts.admin')

@section('title', 'Create Supervisor · Admin · Projects Hub')

@section('content')
    <div class="admin-page-header">
        <h1>Create Supervisor</h1>
        <p>Provision a new supervisor account with login access and a linked supervisor profile.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            Please correct the errors below and try again.
        </div>
    @endif

    <section class="form-card">
        <form method="POST" action="{{ route('admin.supervisors.store') }}" class="form-grid">
            @csrf

            <div @class(['form-field', 'has-error' => $errors->has('name')])>
                <label for="name">Full name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name">
                @error('name')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div @class(['form-field', 'has-error' => $errors->has('university_number')])>
                <label for="university_number">University number</label>
                <input id="university_number" name="university_number" type="text" value="{{ old('university_number') }}" required autocomplete="off">
                @error('university_number')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div @class(['form-field', 'has-error' => $errors->has('email')])>
                <label for="email">Email <span style="color:var(--admin-muted);font-weight:400;">(optional)</span></label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email">
                @error('email')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div @class(['form-field', 'has-error' => $errors->has('password')])>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required autocomplete="new-password">
                @error('password')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div @class(['form-field', 'has-error' => $errors->has('password_confirmation')])>
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                @error('password_confirmation')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Create supervisor account</button>
                <a href="{{ route('admin.users') }}" class="btn-link">Back to users</a>
                <a href="{{ route('admin.dashboard') }}" class="btn-link">Dashboard</a>
            </div>
        </form>
    </section>
@endsection
