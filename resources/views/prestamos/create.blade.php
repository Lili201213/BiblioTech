@extends('layouts.app')

@section('titulo', 'Nuevo préstamo')

@section('contenido')
    <div class="tarjeta">
        <h1>Nuevo préstamo</h1>

        {{-- Si venimos de una reserva, la entrega ya está vinculada --}}
        @if ($reserva)
            <div class="ok">
                Entregando la reserva #{{ $reserva->id }} de
                <strong>{{ $reserva->usuario ? $reserva->usuario->nombre : '—' }}</strong> por el libro
                <strong>{{ $reserva->libro ? $reserva->libro->titulo : '—' }}</strong>.
            </div>
        @endif

        <form action="{{ route('prestamos.store') }}" method="POST">
            @csrf

            @if ($reserva)
                <input type="hidden" name="id_reserva" value="{{ $reserva->id }}">
            @endif

            <label for="id_usuario">Estudiante *</label>
            <select id="id_usuario" name="id_usuario" required>
                <option value="">Seleccione un estudiante</option>
                @foreach ($usuarios as $usuario)
                    <option value="{{ $usuario->id }}"
                        @selected(old('id_usuario', $reserva?->id_usuario) == $usuario->id)>
                        {{ $usuario->nombre }} ({{ $usuario->email }})
                    </option>
                @endforeach
            </select>
            @error('id_usuario')
                <div class="error">{{ $message }}</div>
            @enderror

            <label for="id_libro">Libro *</label>
            <select id="id_libro" name="id_libro" required>
                <option value="">Seleccione un libro</option>
                @foreach ($libros as $libro)
                    <option value="{{ $libro->id }}"
                        @selected(old('id_libro', $reserva?->id_libro) == $libro->id)>
                        {{ $libro->titulo }} ({{ $libro->stock_disponible }} disponibles)
                    </option>
                @endforeach
            </select>
            @error('id_libro')
                <div class="error">{{ $message }}</div>
            @enderror

            <label for="dias">Días del préstamo (1 a {{ $maxDias }})</label>
            <input type="number" id="dias" name="dias" min="1" max="{{ $maxDias }}"
                   value="{{ old('dias', $maxDias) }}">
            @error('dias')
                <div class="error">{{ $message }}</div>
            @enderror

            @error('id_reserva')
                <div class="error">{{ $message }}</div>
            @enderror

            <p>
                <button type="submit" class="btn">Registrar préstamo</button>
                <a href="{{ route('prestamos.index') }}" class="btn btn-gris">Cancelar</a>
            </p>
        </form>
    </div>
@endsection
