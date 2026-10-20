@extends('layouts.app')

@section('titulo', 'Mis reservas')

@section('contenido')
    <div class="barra">
        <h1>Mis reservas</h1>
    </div>

    <div class="tarjeta">
        @if ($reservas->isEmpty())
            <p>No tienes reservas registradas.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Libro</th>
                        <th>Fecha de reserva</th>
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
                            <td>{{ $reserva->libro ? $reserva->libro->titulo : 'Libro eliminado' }}</td>
                            <td>{{ $reserva->fecha_reserva->format('d/m/Y H:i') }}</td>
                            <td>{{ $reserva->fecha_expiracion->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="insignia {{ $colores[$reserva->estado] ?? 'insignia-gris' }}">
                                    {{ $reserva->estado }}
                                </span>
                            </td>
                            <td class="acciones">
                                @if ($reserva->estado === 'pendiente')
                                    {{-- Cancelar siempre por POST con confirmación --}}
                                    <form action="{{ route('reservas.cancelar', $reserva) }}" method="POST"
                                          onsubmit="return confirm('¿Cancelar la reserva de este libro? El ejemplar volverá a estar disponible.');">
                                        @csrf
                                        <button type="submit" class="btn btn-rojo btn-chico">Cancelar</button>
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
