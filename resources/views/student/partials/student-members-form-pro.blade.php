@php
    $leaderStep = $leaderStep ?? '02';
    $member1Step = $member1Step ?? '03';
    $member2Step = $member2Step ?? '04';
    $leaderNote = $leaderNote ?? 'Primary applicant — required for every request';
@endphp

<div class="form-pro-card">
    <div class="form-pro-card-header">
        <span class="form-step-badge">{{ $leaderStep }}</span>
        <div>
            <h3>Team Leader</h3>
            <p>{{ $leaderNote }}</p>
        </div>
        <span class="form-badge required-badge">Required</span>
    </div>
    <div class="form-pro-card-body">
        <div class="form-grid">
            <div class="form-field form-field-pro">
                <label><i class="fas fa-user"></i> Full Name</label>
                <input type="text" name="nameone" required placeholder="e.g. Maria Santos" value="{{ old('nameone', Session::get('name')) }}">
            </div>
            <div class="form-field form-field-pro">
                <label><i class="fas fa-id-card"></i> Student ID</label>
                <input type="number" name="oneid" required placeholder="e.g. 2200123" value="{{ old('oneid') }}">
            </div>
        </div>
    </div>
</div>

<div class="member-cards-grid">
    <div class="form-pro-card member-card optional">
        <div class="form-pro-card-header compact">
            <span class="form-step-badge muted">{{ $member1Step }}</span>
            <div>
                <h3>Team Member 1</h3>
                <p>Optional teammate</p>
            </div>
            <span class="form-badge optional-badge">Optional</span>
        </div>
        <div class="form-pro-card-body">
            <div class="form-field form-field-pro">
                <label><i class="fas fa-user"></i> Full Name</label>
                <input type="text" name="nametwo" placeholder="e.g. João Pereira" value="{{ old('nametwo') }}">
            </div>
            <div class="form-field form-field-pro">
                <label><i class="fas fa-id-card"></i> Student ID</label>
                <input type="number" name="twoid" placeholder="e.g. 2200456" value="{{ old('twoid') }}">
            </div>
            @error('twoid')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="form-pro-card member-card optional">
        <div class="form-pro-card-header compact">
            <span class="form-step-badge muted">{{ $member2Step }}</span>
            <div>
                <h3>Team Member 2</h3>
                <p>Optional teammate</p>
            </div>
            <span class="form-badge optional-badge">Optional</span>
        </div>
        <div class="form-pro-card-body">
            <div class="form-field form-field-pro">
                <label><i class="fas fa-user"></i> Full Name</label>
                <input type="text" name="namethree" placeholder="e.g. Clara Lee" value="{{ old('namethree') }}">
            </div>
            <div class="form-field form-field-pro">
                <label><i class="fas fa-id-card"></i> Student ID</label>
                <input type="number" name="threeid" placeholder="e.g. 2200789" value="{{ old('threeid') }}">
            </div>
            @error('threeid')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="form-pro-notice">
    <i class="fas fa-info-circle"></i>
    <div>
        <strong>Team size rules</strong>
        <span>Minimum 1 member (you). Maximum 3 members per application. Each student ID can only be used once.</span>
    </div>
</div>
