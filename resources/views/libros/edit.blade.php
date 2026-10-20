@extends('layouts.app')

@section('titulo', 'Editar libro')

@section('contenido')
    <div class="tarjeta">
        <h1>Editar libro: {{ $libro->titulo }}</h1>

        <form action="{{ route('libros.update', $libro) }}" method="POST">
            @csrf
            @method('PUT')

            @include('libros._form')

            <p>
                <button type="submit" class="btn">Guardar cambios</button>
                <a href="{{ route('libros.index') }}" class="btn btn-gris">Cancelar</a>
            </p>
        </form>
    </div>
@endsection
