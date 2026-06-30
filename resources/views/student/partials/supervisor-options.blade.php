<option value="" disabled {{ old('supname') ? '' : 'selected' }}>Select a supervisor</option>
@foreach($supervisors as $supervisor)
<option value="{{ $supervisor->name }}" {{ old('supname') == $supervisor->name ? 'selected' : '' }}>{{ $supervisor->name }}</option>
@endforeach
