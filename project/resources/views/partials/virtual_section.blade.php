<div id="conditions">
    <div class="row condition-row mb-3">
        <div class="col-md-6 mb-2">
            <label for="column" class="form-label">Column</label>
            <select id="column" class="column form-control">
                <option value="" disabled selected>Select Column</option>
                @foreach (config(($configFile ?? 'virtualcolumns') . '.columns', []) as $col)
                    @if(isset($col['column']))
                        <option value="{{ $col['column'] }}" data-dependent="{{ $col['is_dependent'] ?? false }}"
                            data-table="{{ $col['table_name'] ?? '' }}"
                            data-column-name="{{ $col['dependent_column_name'] ?? '' }}"
                            data-query-column="{{ $col['column_name'] ?? '' }}" data-ismultiple="{{ $col['isMultiple'] ?? '' }}"
                            data-column-type="{{ $col['type'] ?? '' }}" data-column-id="{{ $col['dependent_column_id'] ?? '' }}"
                            data-database="{{ $col['database'] ?? '' }}">
                            {{ $col['column'] }}
                        </option>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-2">
            <label for="operator" class="form-label">Operator</label>
            <select id="operator" class="operator form-control">
                <option value="" disabled selected>Select Operator</option>
            </select>
        </div>
        <div class="col-md-6 mb-2">
            <label class="form-label" for="value-container">Value</label>
            <div class="value-container" id="value-container">
                <div class="dropdown-container form-group">
                    <select class="custom-select2 form-control value-dropdown" multiple="multiple">

                    </select>
                </div>
                <input type="text" class="form-control value-input" placeholder="Enter Value" style="display: none;">
                <input type="text" class="form-control from-value" placeholder="From Value" style="display: none;">
                <input type="text" class="form-control to-value" placeholder="To Value" style="display: none;">
            </div>
        </div>
        <div class="col-md-6" style="margin-top: 28px;">
            <button type="button" class="save-condition btn btn-success">Save</button>
        </div>
    </div>
</div>
<!-- <input type="hidden" id="virtualCondition" value="{{ htmlentities(json_encode($datas['virtualCondition'] ?? []), ENT_QUOTES, 'UTF-8') }}"> -->
<input type="hidden" id="virtualConditionQuery" value="{{$virtualCondition}}">
<input type="hidden" id="configFile" value="{{$configFile ?? 'virtualcolumns'}}">
<div style="margin-bottom:20px">
    <div class="row sorting-row align-items-end mb-3">
        <div class="col-md-4 mb-2">
            <label for="sorting" class="form-label">Sorting</label>
            <select id="sorting" class="sorting form-control">
                <option value="" disabled selected>Select Sorting Column</option>
                @foreach (config(($configFile ?? 'virtualcolumns') . '.sorting', []) as $sort)
                    @if(isset($sort['column']))
                        <option value="{{ $sort['column'] }}" data-query-column="{{ $sort['column_name'] ?? '' }}">
                            {{ $sort['column'] }}
                        </option>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-2">
            <label for="sorting-order" class="form-label">Sorting Order</label>
            <select id="sorting-order" class="sorting-order form-control">
                <option value="asc" data-sort-name="Ascending"> Ascending </option>
                <option value="desc" data-sort-name="Descending"> Descending </option>
            </select>
        </div>
        <div class="col-md-4 mb-2">
            <button type="button" class="add-sorting btn btn-primary ms-2">Add Sording</button>
        </div>
    </div>
    @if($limitSet)
        <div class="row sorting-row align-items-end mb-3">
            <div class="col-md-8 mb-2">
                <input type="number" class="form-control" id="limit-value" placeholder="Add Limit">
            </div>
            <div class="col-md-4 mb-2">
                <button type="button" class="add-limit btn btn-primary ms-2">Add Limit</button>
            </div>
        </div>
    @endif
    <h4>Conditions Table:</h4>
    <table id="conditionsTable" class="table table-bordered">
        <thead>
            <th>Column</th>
            <td style="display:none">Dependent Column Name</td>
            <th>Operator</th>
            <td style="display:none">Dependent Value</td>
            <th>Value</th>
            <th>Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <input class="form-control" type="hidden" @if($nameset == 1) name="generatedQuery" @endif id="generatedQuery">
</div>