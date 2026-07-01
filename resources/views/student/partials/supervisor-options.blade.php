<option value="" disabled {{ old('supervisor_id') ? '' : 'selected' }}>Select a supervisor</option>
@foreach($supervisors as $supervisor)
<option value="{{ $supervisor->id }}" {{ (string) old('supervisor_id') === (string) $supervisor->id ? 'selected' : '' }}>{{ $supervisor->name }}</option>
@endforeach
