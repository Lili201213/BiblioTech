@extends('layouts.app')

@section('titulo', 'Reservas')

@section('contenido')
    <div class="barra">
        <h1>Reservas</h1>

        {{-- Filtro por estado --}}
        <form class="buscador" method="GET" action="{{ route('reservas.index') }}">
            <select name="estado">
                <option value="">Todos los estados</option>
                @foreach (['pendiente', 'confirmada', 'expirada', 'cancelada'] as $opcion)
                    <option value="{{ $opcion }}" @selected($estado === $opcion)>{{ $opcion }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn">Filtrar</button>
        </form>
    </div>

    <div class="tarjeta">
        @if ($reservas->isEmpty())
            <p>No se encontraron reservas.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Libro</th>
                        <th>Reserva</th>
                        <th>Expira</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reservas as $reserva)
                        @php
                            // Color de la insignia según el estado
                            $colores = [
                                'pendiente' => 'insignia-amarilla',
                                'confirmada' => 'insignia-verde',
                                'expirada' => 'insignia-roja',
                                'cancelada' => 'insignia-gris',
                            ];
                        @endphp
                        <tr>
                            <td>{{ $reserva->id }}</td>
                            <td>{{ $reserva->usuario ? $reserva->usuario->nombre : '—' }}</td>
                            <td>{{ $reserva->libro ? $reserva->libro->titulo : '—' }}</td>
                            <td>{{ $reserva->fecha_reserva->format('d/m/Y H:i') }}</td>
                            <td>{{ $reserva->fecha_expiracion->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="insignia {{ $colores[$reserva->estado] ?? 'insignia-gris' }}">
                                    {{ $reserva->estado }}
                                </span>
                            </td>
                            <td class="acciones">
                                @if ($reserva->estado === 'pendiente')
                                    {{-- Entregar el libro = convertir la reserva en préstamo --}}
                                    <a href="{{ route('prestamos.create', ['reserva' => $reserva->id]) }}"
                                       class="btn btn-chico">Entregar libro</a>
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
