@php
    $fmtDate = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('Y-m-d') : '';
@endphp

<details class="project-edit-panel">
    <summary><i class="fas fa-edit"></i> Edit Project</summary>
    <form method="POST" action="{{ url('/updateproject') }}" class="request-form-pro" style="margin-top: 1rem;">
        @csrf
        <input type="hidden" name="project_id" value="{{ $project->id }}">
        <div class="form-grid">
            <div class="form-field form-field-pro">
                <label>Project Name</label>
                <input type="text" name="project_name" required value="{{ $project->name }}">
            </div>
            <div class="form-field form-field-pro">
                <label>Department</label>
                <select name="department" required>
                    <option value="software" {{ $project->department == 'software' ? 'selected' : '' }}>Software Engineering</option>
                    <option value="ai" {{ $project->department == 'ai' ? 'selected' : '' }}>Artificial Intelligence</option>
                    <option value="network" {{ $project->department == 'network' ? 'selected' : '' }}>Network & Cybersecurity</option>
                </select>
            </div>
            <div class="form-field form-field-pro">
                <label>Taken?</label>
                <select name="taken" required>
                    <option value="No" {{ $project->taken == 0 ? 'selected' : '' }}>No — Available</option>
                    <option value="Yes" {{ $project->taken == 1 ? 'selected' : '' }}>Yes — Assigned</option>
                </select>
            </div>
        </div>
        <div class="form-field form-field-pro" style="margin-top: 0.75rem;">
            <label>Description</label>
            <textarea name="description" rows="3" required>{{ $project->description }}</textarea>
        </div>
        <div class="form-grid" style="margin-top: 0.75rem;">
            <div class="form-field form-field-pro">
                <label>Student 1 Name</label>
                <input type="text" name="student_one_name" value="{{ $project->student_one_name }}">
            </div>
            <div class="form-field form-field-pro">
                <label>Student 1 ID</label>
                <input type="text" name="student_one_id" value="{{ $project->student_one_id }}">
            </div>
            <div class="form-field form-field-pro">
                <label>Student 2 Name</label>
                <input type="text" name="student_two_name" value="{{ $project->student_two_name }}">
            </div>
            <div class="form-field form-field-pro">
                <label>Student 2 ID</label>
                <input type="text" name="student_two_id" value="{{ $project->student_two_id }}">
            </div>
            <div class="form-field form-field-pro">
                <label>Student 3 Name</label>
                <input type="text" name="student_three_name" value="{{ $project->student_three_name }}">
            </div>
            <div class="form-field form-field-pro">
                <label>Student 3 ID</label>
                <input type="text" name="student_three_id" value="{{ $project->student_three_id }}">
            </div>
        </div>
        <div class="form-field form-field-pro" style="margin-top: 0.75rem; max-width: 200px;">
            <label>Team Size</label>
            <input type="number" name="students_number" min="0" max="3" value="{{ $project->student_count }}">
        </div>
        <div class="form-grid" style="margin-top: 0.75rem;">
            <div class="form-field form-field-pro">
                <label>Seminar 1</label>
                <input type="date" name="seminar1_date" required value="{{ $fmtDate($project->seminar_1) }}">
            </div>
            <div class="form-field form-field-pro">
                <label>Seminar 2</label>
                <input type="date" name="seminar2_date" required value="{{ $fmtDate($project->seminar_2) }}">
            </div>
            <div class="form-field form-field-pro">
                <label>Seminar 3</label>
                <input type="date" name="seminar3_date" required value="{{ $fmtDate($project->seminar_3) }}">
            </div>
            <div class="form-field form-field-pro">
                <label>Final</label>
                <input type="date" name="final_date" required value="{{ $fmtDate($project->final) }}">
            </div>
        </div>
        <div class="form-pro-actions" style="padding: 0; margin-top: 1rem;">
            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Changes</button>
        </div>
    </form>
</details>
