@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@php
    $priorityBase = $priority ?? 0.90;
    $priorityDisplay = number_format((float) old('priority', $priorityBase), 2, '.', '');
    $freqBase = $frequency ?? 'daily';
    $freqSelected = old('frequency', $freqBase);
    if (! in_array($freqSelected, ['daily', 'weekly', 'monthly'], true)) {
        $freqSelected = 'daily';
    }
@endphp
@if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <h6>Priority</h6>
                <input type="number" class="form-control" id="priority" name="priority"
                    placeholder="0.90" step="0.01" min="0" max="1"
                    value="{{ $priorityDisplay }}">
                <small class="text-muted">Value between 0.00 and 1.00</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <h6>Frequency</h6>
                <select id="frequency" class="form-control" name="frequency">
                    <option value="daily" {{ $freqSelected == 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ $freqSelected == 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ $freqSelected == 'monthly' ? 'selected' : '' }}>Monthly</option>
                </select>
            </div>
        </div>
    </div>
@endif
 