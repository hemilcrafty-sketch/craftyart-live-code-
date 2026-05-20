@if ($items->count())
    <div class="row align-items-center justify-content-between flex-wrap">
        <div class="col-auto">
            <div class="dataTables_info" role="status" aria-live="polite">
                Showing {{ $items->firstItem() }} to {{ $items->lastItem() }} of {{ $items->total() }} entries
            </div>
        </div>
        <div class="col-auto">
            <div class="dataTables_paginate paging_simple_numbers">
                {{ $items->appends(request()->input())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endif
