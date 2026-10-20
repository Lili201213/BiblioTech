@extends('layouts.app')

@section('titulo', 'Nueva categoría')

@section('contenido')
    <div class="tarjeta">
        <h1>Nueva categoría</h1>

        <form action="{{ route('categorias.store') }}" method="POST">
            @csrf

            @include('categorias._form')

            <p>
                <button type="submit" class="btn">Guardar</button>
                <a href="{{ route('categorias.index') }}" class="btn btn-gris">Cancelar</a>
            </p>
        </form>
    </div>
@endsection
