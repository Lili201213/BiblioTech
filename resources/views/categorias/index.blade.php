@extends('layouts.app')

@section('titulo', 'Categorías')

@section('contenido')
    <div class="barra">
        <h1>Categorías</h1>
        <a href="{{ route('categorias.create') }}" class="btn">Nueva categoría</a>
    </div>

    <div class="tarjeta">
        @if ($categorias->isEmpty())
            <p>No se encontraron categorías.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Libros</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categorias as $categoria)
                        <tr>
                            <td>{{ $categoria->id }}</td>
                            <td>{{ $categoria->nombre }}</td>
                            <td>{{ $categoria->descripcion ?: '—' }}</td>
                            <td>{{ $categoria->libros_count }}</td>
                            <td class="acciones">
                                <a href="{{ route('categorias.edit', $categoria) }}" class="btn btn-chico">Editar</a>

                                {{-- La confirmación avisa cuántos libros quedarán sin categoría --}}
                                <form action="{{ route('categorias.destroy', $categoria) }}" method="POST"
                                      onsubmit="return confirm('¿Eliminar la categoría «{{ $categoria->nombre }}»? {{ $categoria->libros_count }} libro(s) quedarán sin categoría.');">
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
