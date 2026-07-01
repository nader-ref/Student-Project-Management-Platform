@php
    $leaderStep = $leaderStep ?? '02';
    $member1Step = $member1Step ?? '03';
    $member2Step = $member2Step ?? '04';
    $leaderNote = $leaderNote ?? 'Primary applicant — required for every request';
    $memberIdLabel = $memberIdLabel ?? 'University Number';
    $memberIdType = $memberIdType ?? 'text';
    $memberOnePlaceholder = $memberOnePlaceholder ?? 'e.g. STU-2026-001';
    $memberTwoPlaceholder = $memberTwoPlaceholder ?? 'e.g. STU-2026-002';
    $memberThreePlaceholder = $memberThreePlaceholder ?? 'e.g. STU-2026-003';
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
                <input type="text" name="nameone" required placeholder="e.g. Maria Santos" value="{{ old('nameone', auth()->user()?->name) }}">
            </div>
            <div class="form-field form-field-pro">
                <label><i class="fas fa-id-card"></i> {{ $memberIdLabel }}</label>
                <input type="{{ $memberIdType }}" name="oneid" required placeholder="{{ $memberOnePlaceholder }}" value="{{ old('oneid') }}">
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
                <label><i class="fas fa-id-card"></i> {{ $memberIdLabel }}</label>
                <input type="{{ $memberIdType }}" name="twoid" placeholder="{{ $memberTwoPlaceholder }}" value="{{ old('twoid') }}">
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
                <label><i class="fas fa-id-card"></i> {{ $memberIdLabel }}</label>
                <input type="{{ $memberIdType }}" name="threeid" placeholder="{{ $memberThreePlaceholder }}" value="{{ old('threeid') }}">
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
        <span>Minimum 1 member (you). Maximum 3 members per application. Each university number can only be used once.</span>
    </div>
</div>
