<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;

/**
 * Configuración del sistema (solo administrador).
 * Muestra y guarda las reglas editables: préstamos, reservas y multas.
 */
class ConfiguracionController extends Controller
{
    // Muestra el formulario (GET /configuracion)
    public function edit()
    {
        // Configuraciones actuales para rellenar el formulario
        $config = Configuracion::todas();

        return view('configuracion.edit', compact('config'));
    }

    // Guarda los valores (PUT /configuracion)
    public function update(Request $request)
    {
        $datos = $request->validate([
            'prestamo_dias' => ['required', 'integer', 'between:1,60'],
            'prestamo_max_activos' => ['required', 'integer', 'between:1,10'],
            'reserva_max_activas' => ['required', 'integer', 'between:1,10'],
            'reserva_horas_vigencia' => ['required', 'integer', 'between:1,168'],
            'multa_por_dia' => ['required', 'numeric', 'between:0,100', 'decimal:0,2'],
            'multa_dias_gracia' => ['required', 'integer', 'between:0,30'],
            'bloquear_con_multas' => ['nullable', 'boolean'],
        ], [
            'prestamo_dias.required' => 'Debes indicar los días de préstamo.',
            'prestamo_dias.integer' => 'Los días de préstamo deben ser un número entero.',
            'prestamo_dias.between' => 'Los días de préstamo deben estar entre 1 y 60.',
            'prestamo_max_activos.required' => 'Debes indicar el máximo de préstamos activos.',
            'prestamo_max_activos.integer' => 'El máximo de préstamos activos debe ser un número entero.',
            'prestamo_max_activos.between' => 'El máximo de préstamos activos debe estar entre 1 y 10.',
            'reserva_max_activas.required' => 'Debes indicar el máximo de reservas activas.',
            'reserva_max_activas.integer' => 'El máximo de reservas activas debe ser un número entero.',
            'reserva_max_activas.between' => 'El máximo de reservas activas debe estar entre 1 y 10.',
            'reserva_horas_vigencia.required' => 'Debes indicar las horas de vigencia de la reserva.',
            'reserva_horas_vigencia.integer' => 'Las horas de vigencia deben ser un número entero.',
            'reserva_horas_vigencia.between' => 'Las horas de vigencia deben estar entre 1 y 168.',
            'multa_por_dia.required' => 'Debes indicar la mora por día.',
            'multa_por_dia.numeric' => 'La mora por día debe ser un número.',
            'multa_por_dia.between' => 'La mora por día debe estar entre 0 y 100.',
            'multa_por_dia.decimal' => 'La mora por día puede tener como máximo 2 decimales.',
            'multa_dias_gracia.required' => 'Debes indicar los días de gracia.',
            'multa_dias_gracia.integer' => 'Los días de gracia deben ser un número entero.',
            'multa_dias_gracia.between' => 'Los días de gracia deben estar entre 0 y 30.',
            'bloquear_con_multas.boolean' => 'El bloqueo por multas solo puede ser 0 o 1.',
        ]);

        // El checkbox sin marcar no viaja en la petición: se guarda como 0
        $datos['bloquear_con_multas'] = $request->boolean('bloquear_con_multas') ? 1 : 0;

        Configuracion::guardar($datos);

        return redirect()->route('configuracion.edit')
            ->with('exito', 'Configuración guardada correctamente.');
    }
}
