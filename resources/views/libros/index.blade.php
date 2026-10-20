@extends('layouts.app')

@section('titulo', 'Libros')

@section('contenido')
    <div class="barra">
        <h1>Libros</h1>
        <a href="{{ route('libros.create') }}" class="btn">Nuevo libro</a>
    </div>

    {{-- Buscador por título, autor o ISBN --}}
    <form class="buscador" method="GET" action="{{ route('libros.index') }}">
        <input type="text" name="q" placeholder="Buscar por título, autor o ISBN"
               value="{{ $busqueda }}">
        <button type="submit" class="btn">Buscar</button>
        @if ($busqueda)
            <a href="{{ route('libros.index') }}" class="btn btn-gris">Limpiar</a>
        @endif
    </form>

    <div class="tarjeta">
        @if ($libros->isEmpty())
            <p>No se encontraron libros.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Categoría</th>
                        <th>Disponibles / Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($libros as $libro)
                        <tr>
                            <td>{{ $libro->id }}</td>
                            <td>{{ $libro->titulo }}</td>
                            <td>{{ $libro->autor }}</td>
                            <td>{{ $libro->categoria ? $libro->categoria->nombre : 'Sin categoría' }}</td>
                            <td>{{ $libro->stock_disponible }} / {{ $libro->stock_total }}</td>
                            <td class="acciones">
                                <a href="{{ route('libros.edit', $libro) }}" class="btn btn-chico">Editar</a>

                                {{-- Eliminar siempre por DELETE con confirmación --}}
                                <form action="{{ route('libros.destroy', $libro) }}" method="POST"
                                      onsubmit="return confirm('¿Seguro que quieres eliminar el libro «{{ $libro->titulo }}»?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-rojo btn-chico">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
