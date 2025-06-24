@if ($paginator->hasPages())
    <div id="pagination" class="pagination-container">
        <div class="gap-3 d-flex justify-content-between align-items-center w-100">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <h2 class="pagination-link pagination-prev disabled">
                    Previous
                </h2>
            @else
                <h2 class="pagination-link pagination-prev"
                    onclick="window.location='{{ $paginator->appends(request()->query())->previousPageUrl() }}'">
                    Previous
                </h2>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <h2 class="pagination-link pagination-next"
                    onclick="window.location='{{ $paginator->appends(request()->query())->nextPageUrl() }}'">
                    Next
                </h2>
            @else
                <h2 class="pagination-link pagination-next disabled">
                    Next
                </h2>
            @endif
        </div>
    </div>
@endif