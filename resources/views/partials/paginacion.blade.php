@if ($paginator->hasPages())
    <nav class="paginacion" aria-label="Paginación">
        @if ($paginator->onFirstPage())
            <span class="off">« Anterior</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}">« Anterior</a>
        @endif

        <span>Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}">Siguiente »</a>
        @else
            <span class="off">Siguiente »</span>
        @endif
    </nav>
@endif
