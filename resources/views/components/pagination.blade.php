@if ($paginator->hasPages())
    <div id="pagination" class="pagination-container">
        <div>
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <h2 class="text-secondary pagination-link cursor-pointer" disabled>
                    <x-svg-icon name="arrow-left" size="16" color="#DFDFDF" /> Previous
                </h2>
            @else
                <h2 class="pagination-link cursor-pointer" onclick="window.location='{{ $paginator->previousPageUrl() }}'">
                    <x-svg-icon name="arrow-left" size="16" color="#DFDFDF" /> Previous
                </h2>
            @endif
        </div>

        {{-- Page Numbers --}}
        <div class="page-numbers">
            @php
                $start = max(1, $paginator->currentPage() - 2);
                $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
            @endphp

            <div class="d-flex align-items-center gap-1">
                @if($start > 1)
                    <h2 class="pagination-link-number" onclick="window.location='{{ $paginator->url(1) }}'">1</h2>
                    @if($start > 2)
                        <span class="pagination-ellipsis">...</span>
                    @endif
                @endif

                @for($page = $start; $page <= $end; $page++)
                    @if($page == $paginator->currentPage())
                        <h2 class="pagination-link-number active">{{ $page }}</h2>
                    @else
                        <h2 class="pagination-link-number" onclick="window.location='{{ $paginator->url($page) }}'">{{ $page }}</h2>
                    @endif
                @endfor

                @if($end < $paginator->lastPage())
                    @if($end < $paginator->lastPage() - 1)
                        <span class="pagination-ellipsis">...</span>
                    @endif
                    <h2 class="pagination-link" onclick="window.location='{{ $paginator->url($paginator->lastPage()) }}'">
                        {{ $paginator->lastPage() }}
                    </h2>
                @endif
            </div>
        </div>

        <div>
            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <h2 class=" pagination-link cursor-pointer" onclick="window.location='{{ $paginator->nextPageUrl() }}'">
                    Next <x-svg-icon name="arrow-right" size="16" color="#DFDFDF" />
                </h2>
            @else
                <h2 class="text-secondary pagination-link cursor-pointer" disabled>
                    Next <x-svg-icon name="arrow-right" size="16" color="#DFDFDF" />
                </h2>
            @endif
        </div>
    </div>
@endif