@isset($noIndexStats)
    @php
        $noIndexTotal = ($noIndexStats['enabled'] ?? 0) + ($noIndexStats['disabled'] ?? 0);
        $noIndexSel = request('no_index_filter');
        $noIndexSel = in_array((string) $noIndexSel, ['0', '1'], true) ? (string) $noIndexSel : '';
    @endphp
    <select name="no_index_filter" class="form-control item-form-input w-100" style="min-width: 170px;"
        onchange="this.form.submit()" title="Index / No index">
        <option value="" {{ $noIndexSel === '' ? 'selected' : '' }}>All ({{ $noIndexTotal }})</option>
        <option value="0" {{ $noIndexSel === '0' ? 'selected' : '' }}>Index ({{ $noIndexStats['disabled'] ?? 0 }})</option>
        <option value="1" {{ $noIndexSel === '1' ? 'selected' : '' }}>No Index ({{ $noIndexStats['enabled'] ?? 0 }})</option>
    </select>
@endisset
