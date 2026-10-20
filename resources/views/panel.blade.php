@extends('layouts.app')

@section('titulo', 'Panel')

@section('contenido')
    @if ($usuario->esGestor())
        {{-- ============================================================
             PANEL DE ADMINISTRADOR Y BIBLIOTECARIO
             ============================================================ --}}

        {{-- Subtítulo del banner: reserva(s) pendiente(s) y préstamo(s) vencido(s) --}}
        @php
            $nReservas = $contadores['reservas_pendientes'];
            $nPrestamos = $contadores['prestamos_vencidos'];

            if ($nReservas === 0 && $nPrestamos === 0) {
                $subtituloBanner = 'Todo al día en la biblioteca.';
            } else {
                $partes = [];
                if ($nReservas > 0) {
                    $partes[] = $nReservas . ' reserva' . ($nReservas === 1 ? '' : 's')
                        . ' pendiente' . ($nReservas === 1 ? '' : 's');
                }
                if ($nPrestamos > 0) {
                    $partes[] = $nPrestamos . ' préstamo' . ($nPrestamos === 1 ? '' : 's')
                        . ' vencido' . ($nPrestamos === 1 ? '' : 's');
                }
                $subtituloBanner = 'Hoy tienes ' . implode(' y ', $partes) . '.';
            }
        @endphp

        <x-banner titulo="Hola, {{ $usuario->nombre }}" :subtitulo="$subtituloBanner">
            <a href="{{ route('prestamos.create') }}" class="btn">Registrar préstamo</a>
            <a href="{{ route('catalogo') }}" class="btn btn-morado">Ver catálogo</a>
        </x-banner>

        {{-- Ficha de estadísticas: cada tarjeta abre su sección --}}
        <div class="contadores">
            <a href="{{ route('libros.index') }}" class="contador">
                <span class="numero">{{ $contadores['libros'] }}</span>
                📚 Libros
            </a>
            <a href="{{ route('categorias.index') }}" class="contador">
                <span class="numero">{{ $contadores['categorias'] }}</span>
                🏷️ Categorías
            </a>
            <a href="{{ route('reservas.index', ['estado' => 'pendiente']) }}" class="contador">
                <span class="numero">{{ $contadores['reservas_pendientes'] }}</span>
                📌 Reservas pendientes
            </a>
            <a href="{{ route('prestamos.index', ['estado' => 'activo']) }}" class="contador">
                <span class="numero">{{ $contadores['prestamos_activos'] }}</span>
                📖 Préstamos activos
            </a>
            <a href="{{ route('prestamos.index', ['estado' => 'vencido']) }}" class="contador">
                <span class="numero">{{ $contadores['prestamos_vencidos'] }}</span>
                ⏰ Vencidos
            </a>
            <a href="{{ route('multas.index', ['estado' => 'pendiente']) }}" class="contador">
                <span class="numero">{{ $contadores['multas_pendientes'] }}</span>
                💰 Multas pendientes
            </a>
        </div>
{{-- PANEL_GESTOR_CONTINUA --}}
    @else
{{-- PANEL_ESTUDIANTE --}}
    @endif
@endsection
