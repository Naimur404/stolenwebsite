<nav class="d-flex justify-items-center justify-content-between">
    <div class="flex-sm-fill d-sm-flex align-items-sm-center justify-content-sm-between">
        <div class="me-2">
            @if ($paginator->total())
                <p class="small text-muted">
                    <span class="fw-semibold">{{ $paginator->firstItem() }}</span>
                    <span> - </span>
                    <span class="fw-semibold">{{ $paginator->lastItem() }}</span>
                    {{ trans('core/base::tables.in') }}
                    <span class="fw-semibold">{{ $paginator->total() }}</span>
                    {{ trans('core/base::tables.records') }}
                </p>
            @endif
        </div>

        <div>
            <ul class="pagination pagination-sm">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true" aria-label="{{ trans('pagination.previous') }}">
                        <span class="page-link" aria-hidden="true">&lsaquo;</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ trans('pagination.previous') }}">&lsaquo;</a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                            @elseif ($page > $paginator->currentPage() - 3 && $page < $paginator->currentPage() + 3)
                                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ trans('pagination.next') }}">&rsaquo;</a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true" aria-label="{{ trans('pagination.next') }}">
                        <span class="page-link" aria-hidden="true">&rsaquo;</span>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>
