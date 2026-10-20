@extends('layouts.app')

@section('titulo', 'Configuración')

@section('contenido')
    <div class="barra">
        <h1>Configuración del sistema</h1>
    </div>

    <div class="tarjeta">
        <form action="{{ route('configuracion.update') }}" method="POST">
            @csrf
            @method('PUT')

            {{-- ===== Sección 1: Préstamos ===== --}}
            <h2>Préstamos</h2>

            <div class="fila">
                <div>
                    <label for="prestamo_dias">Días de préstamo</label>
                    <input type="number" id="prestamo_dias" name="prestamo_dias"
                           min="1" max="60"
                           value="{{ old('prestamo_dias', $config['prestamo_dias']) }}">
                    <p class="ayuda">Plazo máximo para devolver un libro.</p>
                    @error('prestamo_dias')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="prestamo_max_activos">Máximo de préstamos activos</label>
                    <input type="number" id="prestamo_max_activos" name="prestamo_max_activos"
                           min="1" max="10"
                           value="{{ old('prestamo_max_activos', $config['prestamo_max_activos']) }}">
                    <p class="ayuda">Préstamos activos o vencidos que puede tener cada usuario.</p>
                    @error('prestamo_max_activos')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- ===== Sección 2: Reservas ===== --}}
            <h2>Reservas</h2>

            <div class="fila">
                <div>
                    <label for="reserva_max_activas">Máximo de reservas activas</label>
                    <input type="number" id="reserva_max_activas" name="reserva_max_activas"
                           min="1" max="10"
                           value="{{ old('reserva_max_activas', $config['reserva_max_activas']) }}">
                    <p class="ayuda">Reservas pendientes que puede tener cada usuario.</p>
                    @error('reserva_max_activas')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="reserva_horas_vigencia">Horas de vigencia de la reserva</label>
                    <input type="number" id="reserva_horas_vigencia" name="reserva_horas_vigencia"
                           min="1" max="168"
                           value="{{ old('reserva_horas_vigencia', $config['reserva_horas_vigencia']) }}">
                    <p class="ayuda">Horas que dura la reserva antes de expirar.</p>
                    @error('reserva_horas_vigencia')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- ===== Sección 3: Multas ===== --}}
            <h2>Multas</h2>

            <div class="fila">
                <div>
                    <label for="multa_por_dia">Mora por día (S/)</label>
                    <input type="number" id="multa_por_dia" name="multa_por_dia"
                           min="0" max="100" step="0.01"
                           value="{{ old('multa_por_dia', $config['multa_por_dia']) }}">
                    <p class="ayuda">Monto cobrado por cada día de retraso.</p>
                    @error('multa_por_dia')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="multa_dias_gracia">Días de gracia</label>
                    <input type="number" id="multa_dias_gracia" name="multa_dias_gracia"
                           min="0" max="30"
                           value="{{ old('multa_dias_gracia', $config['multa_dias_gracia']) }}">
                    <p class="ayuda">Días antes de empezar a generar multa.</p>
                    @error('multa_dias_gracia')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Checkbox del bloqueo por multas --}}
            <div class="casilla">
                <input type="checkbox" id="bloquear_con_multas" name="bloquear_con_multas" value="1"
                       @checked(old('bloquear_con_multas', $config['bloquear_con_multas']) == '1')>
                <label for="bloquear_con_multas">Bloquear usuarios con multas pendientes</label>
            </div>
            <p class="ayuda">Si está activo, no podrán reservar ni pedir nuevos préstamos.</p>
            @error('bloquear_con_multas')
                <div class="error">{{ $message }}</div>
            @enderror

            {{-- Botones del formulario --}}
            <p>
                <button type="submit" class="btn">Guardar cambios</button>
                <a href="{{ route('panel') }}" class="btn btn-gris">Cancelar</a>
            </p>
        </form>
    </div>
@endsection
