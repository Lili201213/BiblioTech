@extends('layouts.app')

@section('titulo', 'Préstamos')

@section('contenido')
    <div class="barra">
        <h1>Préstamos</h1>
        <a href="{{ route('prestamos.create') }}" class="btn">Nuevo préstamo</a>
    </div>

    {{-- Búsqueda por nombre de usuario o título del libro, y filtro por estado --}}
    <form class="buscador" method="GET" action="{{ route('prestamos.index') }}">
        <input type="text" name="q" placeholder="Buscar por usuario o libro"
               value="{{ $busqueda }}">
        <select name="estado">
            <option value="">Todos los estados</option>
            @foreach (['activo', 'devuelto', 'vencido', 'cancelado'] as $opcion)
                <option value="{{ $opcion }}" @selected($estado === $opcion)>{{ $opcion }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn">Buscar</button>
    </form>

    <div class="tarjeta">
        @if ($prestamos->isEmpty())
            <p>No se encontraron préstamos.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Libro</th>
                        <th>Préstamo</th>
                        <th>Devolver antes de</th>
                        <th>Devuelto</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($prestamos as $prestamo)
                        @php
                            // Color de la insignia según el estado
                            $colores = [
                                'activo' => 'insignia-verde',
                                'devuelto' => 'insignia-morado',
                                'vencido' => 'insignia-roja',
                                'cancelado' => 'insignia-gris',
                            ];
                        @endphp
                        <tr>
                            <td>{{ $prestamo->id }}</td>
                            <td>{{ $prestamo->usuario ? $prestamo->usuario->nombre : '—' }}</td>
                            <td>{{ $prestamo->libro ? $prestamo->libro->titulo : '—' }}</td>
                            <td>{{ $prestamo->fecha_prestamo->format('d/m/Y') }}</td>
                            <td>{{ $prestamo->fecha_devolucion_esperada->format('d/m/Y') }}</td>
                            <td>{{ $prestamo->fecha_devolucion_real ? $prestamo->fecha_devolucion_real->format('d/m/Y') : '—' }}</td>
                            <td>
                                <span class="insignia {{ $colores[$prestamo->estado] ?? 'insignia-gris' }}">
                                    {{ $prestamo->estado }}
                                </span>
                            </td>
                            <td class="acciones">
                                @if (in_array($prestamo->estado, ['activo', 'vencido'], true))
                                    {{-- Devolver siempre por POST con confirmación --}}
                                    <form action="{{ route('prestamos.devolver', $prestamo) }}" method="POST"
                                          onsubmit="return confirm('¿Registrar la devolución de este libro?');">
                                        @csrf
                                        <button type="submit" class="btn btn-chico">Devolver</button>
                                    </form>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
