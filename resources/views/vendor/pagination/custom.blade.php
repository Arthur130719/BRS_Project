@if ($paginator->hasPages())
    <nav aria-label="Pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" aria-label="&laquo; Previous">
                <i class="fas fa-chevron-left" style="font-size: 10px;"></i>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="&laquo; Previous">
                <i class="fas fa-chevron-left" style="font-size: 10px;"></i>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span aria-disabled="true">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next &raquo;">
                <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
            </a>
        @else
            <span aria-disabled="true" aria-label="Next &raquo;">
                <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
            </span>
        @endif
    </nav>
@endif
