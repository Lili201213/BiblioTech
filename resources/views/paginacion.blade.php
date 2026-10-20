{{-- Paginación personalizada (sin Bootstrap ni Tailwind), clase .paginacion del CSS --}}

@if ($paginator->hasPages())
    <nav class="paginacion">
        @if ($paginator->onFirstPage())
            <span>« Anterior</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}">« Anterior</a>
        @endif

        @for ($pagina = 1; $pagina <= $paginator->lastPage(); $pagina++)
            @if ($pagina == $paginator->currentPage())
                <span class="actual">{{ $pagina }}</span>
            @else
                <a href="{{ $paginator->url($pagina) }}">{{ $pagina }}</a>
            @endif
        @endfor

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}">Siguiente »</a>
        @else
            <span>Siguiente »</span>
        @endif
    </nav>
@endif
