@if (method_exists($paginator, 'total'))
    {{-- Handle paginated results --}}
    @if ($paginator->total() > 0)
        <div id="tableInfo" class="table-info-showing">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} entries
        </div>
    @else
        <div id="tableInfo">
            No entries found.
        </div>
    @endif
@else
    {{-- Handle collections --}}
    @if ($paginator->count() > 0)
        <div id="tableInfo" class="table-info-showing">
            Showing {{ $paginator->count() }} entries
        </div>
    @else
        <div id="tableInfo">
            No entries found.
        </div>
    @endif
@endif