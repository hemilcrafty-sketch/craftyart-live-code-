<form action="{{ $action }}" method="GET" class="w-100">
    {{-- <div class="form-row justify-content-end justify-content-evntly filter-access"> --}}
    <div class="form-row row gx-2 gy-2 filter-access justify-content-end mr-2">
        <!-- Search Box -->
        <div class="form-group col-md-2 col-3 mb-0">
            {{-- <label class="text-secondary" style="font-size: 12px;" for="query">Search</label> --}}
            <input type="text" name="query" id="query" class="form-control form-input-width item-form-input"
                placeholder="Search..." value="{{ request()->input('query') }}">
        </div>


        @if ($searchableFields)
            <!-- Filter Field Dropdown -->
            <div class="form-group col-md-2 col-3 mb-0">
                {{-- <label class="text-secondary" style="font-size: 12px;" for="sort_by">Sorting Field</label> --}}
                <select name="sort_by" id="sort_by" class="form-control form-input-width item-form-input">
                    <option value="" disabled {{ request('sort_by') ? '' : 'selected' }}>Select Field</option>
                    @foreach ($searchableFields as $field)
                        <option value="{{ $field['id'] }}" {{ request('sort_by') == $field['id'] ? 'selected' : '' }}>
                            {{ $field['value'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Sort Order Dropdown -->
            <div class="form-group col-md-2 col-3 mb-0">
                {{-- <label class="text-secondary" style="font-size: 12px;" for="sort_order">Sort Order</label> --}}
                <select name="sort_order" id="sort_order" class="form-control form-input-width item-form-input">
                    <option selected disabled>Sorting Order</option>
                    <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Descending</option>
                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                </select>
            </div>
        @endif


        <!-- Per Page Dropdown -->
        <div class="form-group col-md-2 col-3 mb-0">
            {{-- <label class="text-secondary" style="font-size: 12px;" for="per_page">Per Page</label> --}}
            <select name="per_page" id="per_page" class="form-control form-input-width item-form-input">
                <option selected disabled>Per Page</option>
                @foreach ([10, 20, 50, 100, 500, 'all'] as $count)
                    <option value="{{ $count }}" {{ request('per_page') == (string) $count ? 'selected' : '' }}>
                        {{ $count }}
                    </option>
                @endforeach
            </select>
        </div>

        @if (!empty($filterExtraNoIndex) && isset($noIndexStats))
            @php
                $noIndexTotal = ($noIndexStats['enabled'] ?? 0) + ($noIndexStats['disabled'] ?? 0);
                $noIndexSel = request('no_index_filter');
                $noIndexSel = in_array((string) $noIndexSel, ['0', '1'], true) ? (string) $noIndexSel : '';
            @endphp
            <div class="form-group col-md-2 col-3 mb-0">
                <select name="no_index_filter" id="no_index_filter"
                    class="form-control form-input-width item-form-input"
                    title="Index / No index">
                    <option value="" {{ $noIndexSel === '' ? 'selected' : '' }}>
                        All ({{ $noIndexTotal }})
                    </option>
                    <option value="0" {{ $noIndexSel === '0' ? 'selected' : '' }}>
                        Index ({{ $noIndexStats['disabled'] ?? 0 }})
                    </option>
                    <option value="1" {{ $noIndexSel === '1' ? 'selected' : '' }}>
                        No Index ({{ $noIndexStats['enabled'] ?? 0 }})
                    </option>
                </select>
            </div>
        @endif

        <!-- Apply Button -->
        <div class="form-group col-md-1 mb-0 d-flex align-items-end">
            <button type="submit" class="btn btn-success form-input w-100 item-form-input">
                Apply
            </button>
        </div>

    </div>
</form>
