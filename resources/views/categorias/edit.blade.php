@extends('layouts.app')

@section('titulo', 'Editar categoría')

@section('contenido')
    <div class="tarjeta">
        <h1>Editar categoría: {{ $categoria->nombre }}</h1>

        <form action="{{ route('categorias.update', $categoria) }}" method="POST">
            @csrf
            @method('PUT')

            @include('categorias._form')

            <p>
                <button type="submit" class="btn">Guardar cambios</button>
                <a href="{{ route('categorias.index') }}" class="btn btn-gris">Cancelar</a>
            </p>
        </form>
    </div>
@endsection
