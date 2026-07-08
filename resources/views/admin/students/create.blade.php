@extends('layouts.admin')

@section('title', 'Create Student · Admin · Projects Hub')

@section('content')
    @include('admin.partials.page-hero', [
        'title' => 'Create Student',
        'description' => 'Provision a new student account with university number login access.',
        'breadcrumb' => '<span>Admin</span><span class="sep">/</span><span>Create student</span>',
    ])

    @if (session('success'))
        <div class="form-pro-alert success form-pro-alert--spaced">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="form-pro-alert error form-pro-alert--spaced">
            <i class="fas fa-exclamation-circle"></i> Please correct the errors below and try again.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.students.store') }}" class="request-form-pro">
        @csrf
        <div class="form-pro-card">
            <div class="form-pro-card-header">
                <span class="form-step-badge"><i class="fas fa-user-graduate"></i></span>
                <div>
                    <h3>Student account details</h3>
                    <p>Enter the student's information to create their login credentials.</p>
                </div>
            </div>
            <div class="form-pro-card-body">
                <div @class(['form-field-pro', 'has-error' => $errors->has('name')])>
                    <label for="name">Full name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name">
                    @error('name')<div class="error-text">{{ $message }}</div>@enderror
                </div>

                <div @class(['form-field-pro', 'form-field-pro--spaced', 'has-error' => $errors->has('university_number')])>
                    <label for="university_number">University number</label>
                    <input id="university_number" name="university_number" type="text" value="{{ old('university_number') }}" required autocomplete="off">
                    @error('university_number')<div class="error-text">{{ $message }}</div>@enderror
                </div>

                <div @class(['form-field-pro', 'form-field-pro--spaced', 'has-error' => $errors->has('email')])>
                    <label for="email">Email <span class="optional-hint">(optional)</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email">
                    @error('email')<div class="error-text">{{ $message }}</div>@enderror
                </div>

                <div @class(['form-field-pro', 'form-field-pro--spaced', 'has-error' => $errors->has('password')])>
                    <label for="password">Password</label>
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
                <button type="submit" class="btn-primary"><i class="fas fa-user-graduate"></i> Create student account</button>
                <a href="{{ route('admin.users') }}" class="btn-link">Back to users</a>
                <a href="{{ route('admin.dashboard') }}" class="btn-link">Dashboard</a>
            </div>
        </div>
    </form>
@endsection
