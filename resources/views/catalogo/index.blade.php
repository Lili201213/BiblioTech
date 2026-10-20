@extends('layouts.app')

@section('titulo', 'Catálogo')

@section('contenido')
    <div class="barra">
        <h1>Catálogo de libros</h1>
    </div>

    {{-- Buscador y filtro por categoría --}}
    <form class="buscador" method="GET" action="{{ route('catalogo') }}">
        <input type="text" name="q" placeholder="Buscar por título, autor o ISBN"
               value="{{ $busqueda }}">
        <select name="categoria">
            <option value="">Todas las categorías</option>
            @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id }}"
                    @selected($idCategoria == $categoria->id)>
                    {{ $categoria->nombre }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn">Buscar</button>
    </form>

    @if ($libros->isEmpty())
        <div class="tarjeta">
            <p>No se encontraron libros.</p>
        </div>
    @else
        {{-- Rejilla de tarjetas --}}
        <div class="rejilla">
            @foreach ($libros as $libro)
                <div class="tarjeta-libro">
                    @if ($libro->portada_url)
                        <img class="portada" src="{{ $libro->portada_url }}" alt="Portada de {{ $libro->titulo }}">
                    @else
                        {{-- Portada de relleno: color según id % 5 y lomo a la izquierda --}}
                        <div class="sin-portada portada-{{ ($libro->id % 5) + 1 }}">
                            <span class="relleno-titulo">{{ $libro->titulo }}</span>
                        </div>
                    @endif
                    <div class="contenido">
                        <h3>{{ $libro->titulo }}</h3>
                        <p class="texto-suave">{{ $libro->autor }}</p>

                        {{-- Chip de categoría --}}
                        <span class="chip">
                            {{ $libro->categoria ? $libro->categoria->nombre : 'Sin categoría' }}
                        </span>

                        {{-- Etiqueta de disponibilidad --}}
                        <span class="insignia {{ $libro->stock_disponible > 0 ? 'insignia-verde' : 'insignia-gris' }}">
                            {{ $libro->stock_disponible > 0 ? 'Disponible' : 'Agotado' }}
                        </span>

                        <a href="{{ route('catalogo.detalle', $libro) }}" class="btn btn-chico">Ver detalle</a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Paginación (9 por página) --}}
        {{ $libros->links() }}
    @endif
@endsection
