@extends('layouts.app')

@section('titulo', 'Nuevo libro')

@section('contenido')
    <div class="tarjeta">
        <h1>Nuevo libro</h1>

        <form action="{{ route('libros.store') }}" method="POST">
            @csrf

            @include('libros._form')

            <p>
                <button type="submit" class="btn">Guardar</button>
                <a href="{{ route('libros.index') }}" class="btn btn-gris">Cancelar</a>
            </p>
        </form>
    </div>
@endsection
