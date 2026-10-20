@extends('layouts.app')

@section('titulo', 'Multas')

@section('contenido')
    <div class="barra">
        <h1>Multas</h1>

        {{-- Filtro por estado --}}
        <form class="buscador" method="GET" action="{{ route('multas.index') }}">
            <select name="estado">
                <option value="">Todos los estados</option>
                @foreach (['pendiente', 'pagada', 'condonada'] as $opcion)
                    <option value="{{ $opcion }}" @selected($estado === $opcion)>{{ $opcion }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn">Filtrar</button>
        </form>
    </div>

    <div class="tarjeta">
        @if ($multas->isEmpty())
            <p>No se encontraron multas.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Libro</th>
                        <th>Monto</th>
                        <th>Generada</th>
                        <th>Pagada</th>
                        <th>Estado</th>
                        <th>Acciones</th>
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
                            <td>{{ $multa->id }}</td>
                            <td>{{ $multa->prestamo && $multa->prestamo->usuario ? $multa->prestamo->usuario->nombre : '—' }}</td>
                            <td>{{ $multa->prestamo && $multa->prestamo->libro ? $multa->prestamo->libro->titulo : '—' }}</td>
                            <td>S/ {{ number_format($multa->monto, 2) }}</td>
                            <td>{{ $multa->fecha_generacion->format('d/m/Y H:i') }}</td>
                            <td>{{ $multa->fecha_pago ? $multa->fecha_pago->format('d/m/Y H:i') : '—' }}</td>
                            <td>
                                <span class="insignia {{ $colores[$multa->estado] ?? 'insignia-gris' }}">
                                    {{ $multa->estado }}
                                </span>
                            </td>
                            <td class="acciones">
                                @if ($multa->estado === 'pendiente')
                                    {{-- Acciones solo sobre multas pendientes --}}
                                    <form action="{{ route('multas.pagar', $multa) }}" method="POST"
                                          onsubmit="return confirm('¿Marcar esta multa como pagada?');">
                                        @csrf
                                        <button type="submit" class="btn btn-chico">Marcar pagada</button>
                                    </form>

                                    <form action="{{ route('multas.condonar', $multa) }}" method="POST"
                                          onsubmit="return confirm('¿Condonar esta multa?');">
                                        @csrf
                                        <button type="submit" class="btn btn-gris btn-chico">Condonar</button>
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
