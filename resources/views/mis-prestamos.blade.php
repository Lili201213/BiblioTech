@extends('layouts.app')

@section('titulo', 'Mis préstamos')

@section('contenido')
    <div class="barra">
        <h1>Mis préstamos</h1>
    </div>

    {{-- Préstamos del estudiante --}}
    <div class="tarjeta">
        <h2>Préstamos</h2>

        @if ($prestamos->isEmpty())
            <p>No tienes préstamos registrados.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Libro</th>
                        <th>Préstamo</th>
                        <th>Devolver antes de</th>
                        <th>Devuelto</th>
                        <th>Estado</th>
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
                            <td>{{ $prestamo->libro ? $prestamo->libro->titulo : 'Libro eliminado' }}</td>
                            <td>{{ $prestamo->fecha_prestamo->format('d/m/Y') }}</td>
                            <td>{{ $prestamo->fecha_devolucion_esperada->format('d/m/Y') }}</td>
                            <td>{{ $prestamo->fecha_devolucion_real ? $prestamo->fecha_devolucion_real->format('d/m/Y') : '—' }}</td>
                            <td>
                                <span class="insignia {{ $colores[$prestamo->estado] ?? 'insignia-gris' }}">
                                    {{ $prestamo->estado }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Multas del estudiante --}}
    <div class="tarjeta" id="multas">
        <h2>Mis multas</h2>

        @if ($multas->isEmpty())
            <p>No tienes multas.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Libro</th>
                        <th>Monto</th>
                        <th>Generada</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($multas as $multa)
                        @php
                            $colores = [
                                'pendiente' => 'insignia-amarilla',
                                'pagada' => 'insignia-verde',
                                'condonada' => 'insignia-gris',
                            ];
                        @endphp
                        <tr>
                            <td>{{ $multa->prestamo && $multa->prestamo->libro ? $multa->prestamo->libro->titulo : '—' }}</td>
                            <td>S/ {{ number_format($multa->monto, 2) }}</td>
                            <td>{{ $multa->fecha_generacion->format('d/m/Y') }}</td>
                            <td>
                                <span class="insignia {{ $colores[$multa->estado] ?? 'insignia-gris' }}">
                                    {{ $multa->estado }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
