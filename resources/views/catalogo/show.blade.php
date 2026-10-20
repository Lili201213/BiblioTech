@extends('layouts.app')

@section('titulo', $libro->titulo)

@section('contenido')
    <div class="tarjeta">
        <div class="fila">
            <div>
                @if ($libro->portada_url)
                    <img class="portada-detalle" src="{{ $libro->portada_url }}" alt="Portada de {{ $libro->titulo }}">
                @else
                    {{-- Portada de relleno: se decora en estilos.css (sin estilos en línea) --}}
                    <div class="portada-detalle-sin portada-{{ ($libro->id % 5) + 1 }}">
                        <span class="relleno-titulo">{{ $libro->titulo }}</span>
                    </div>
                @endif
            </div>
            <div>
                <h1>{{ $libro->titulo }}</h1>
                <p><strong>Autor:</strong> {{ $libro->autor }}</p>
                <p><strong>Categoría:</strong> {{ $libro->categoria ? $libro->categoria->nombre : 'Sin categoría' }}</p>

                @if ($libro->editorial)
                    <p><strong>Editorial:</strong> {{ $libro->editorial }}</p>
                @endif
                @if ($libro->anio)
                    <p><strong>Año:</strong> {{ $libro->anio }}</p>
                @endif
                @if ($libro->isbn)
                    <p><strong>ISBN:</strong> {{ $libro->isbn }}</p>
                @endif

                <p>
                    <span class="insignia {{ $libro->stock_disponible > 0 ? 'insignia-verde' : 'insignia-roja' }}">
                        {{ $libro->stock_disponible }} de {{ $libro->stock_total }} disponibles
                    </span>
                </p>

                {{-- Reservar solo lo ve un estudiante con ejemplares disponibles --}}
                @auth
                    @if (!auth()->user()->esGestor())
                        @if ($libro->stock_disponible > 0)
                            <form action="{{ route('reservas.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id_libro" value="{{ $libro->id }}">
                                <button type="submit" class="btn">Reservar</button>
                                {{-- Aviso con la vigencia configurada por el administrador --}}
                                <p class="ayuda">La reserva vence en {{ $horasReserva }} horas</p>
                            </form>
                        @else
                            <p><strong>No disponible</strong></p>
                        @endif
                    @endif
                @endauth
            </div>
        </div>

        @if ($libro->sinopsis)
            <h2>Sinopsis</h2>
            <p>{!! nl2br(e($libro->sinopsis)) !!}</p>
        @endif
    </div>
@endsection
