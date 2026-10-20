<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Libro;
use App\Models\Multa;
use App\Models\Prestamo;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Reservas del estudiante: crear, listar y cancelar.
 */
class ReservaController extends Controller
{
    // Crea una reserva (POST /reservas)
    public function store(Request $request)
    {
        $datos = $request->validate([
            'id_libro' => ['required', 'exists:libros,id'],
        ], [
            'id_libro.required' => 'Debes indicar el libro que quieres reservar.',
            'id_libro.exists' => 'El libro seleccionado no existe.',
        ]);

        $usuario = $request->user();

        // Reglas del sistema que puede cambiar el administrador
        $maxReservas = Configuracion::entero('reserva_max_activas');
        $horasVigencia = Configuracion::entero('reserva_horas_vigencia');
        $bloquearConMultas = Configuracion::booleano('bloquear_con_multas');

        DB::transaction(function () use ($datos, $usuario, $maxReservas, $horasVigencia, $bloquearConMultas) {
            // Bloqueamos el libro para evitar reservar el "último" ejemplar dos veces
            $libro = Libro::whereKey($datos['id_libro'])->lockForUpdate()->first();

            if ($libro->stock_disponible <= 0) {
                throw ValidationException::withMessages([
                    'id_libro' => 'Este libro no tiene ejemplares disponibles.',
                ]);
            }

            // No se puede reservar el mismo libro dos veces
            $reservaDuplicada = Reserva::where('id_usuario', $usuario->id)
                ->where('id_libro', $libro->id)
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->exists();

            if ($reservaDuplicada) {
                throw ValidationException::withMessages([
                    'id_libro' => 'Ya tienes una reserva de este libro.',
                ]);
            }

            // Tampoco si ya lo tiene prestado
            $prestamoActivo = Prestamo::where('id_usuario', $usuario->id)
                ->where('id_libro', $libro->id)
                ->whereIn('estado', ['activo', 'vencido'])
                ->exists();

            if ($prestamoActivo) {
                throw ValidationException::withMessages([
                    'id_libro' => 'Ya tienes un préstamo activo de este libro.',
                ]);
            }

            // Máximo de reservas pendientes según la configuración
            $pendientes = Reserva::where('id_usuario', $usuario->id)
                ->where('estado', 'pendiente')
                ->count();

            if ($pendientes >= $maxReservas) {
                throw ValidationException::withMessages([
                    'id_libro' => "Ya tienes {$maxReservas} reservas pendientes; cancela una antes de reservar otro libro.",
                ]);
            }

            // No puede reservar si tiene multas pendientes (solo si el bloqueo está activo)
            $tieneMulta = Multa::whereHas('prestamo', function ($query) use ($usuario) {
                $query->where('id_usuario', $usuario->id);
            })->where('estado', 'pendiente')->exists();

            if ($bloquearConMultas && $tieneMulta) {
                throw ValidationException::withMessages([
                    'id_libro' => 'Tienes multas pendientes; no puedes reservar hasta saldarlas.',
                ]);
            }

            // Todo bien: se crea la reserva y se aparta 1 ejemplar
            Reserva::create([
                'id_usuario' => $usuario->id,
                'id_libro' => $libro->id,
                'fecha_reserva' => now(),
                'fecha_expiracion' => now()->addHours($horasVigencia),
                'estado' => 'pendiente',
            ]);

            $libro->stock_disponible--;
            $libro->save();
        });

        return redirect()->route('mis-reservas')
            ->with('exito', "Reserva registrada. Tienes {$horasVigencia} horas para retirar el libro.");
    }

    // Lista todas las reservas para gestión (GET /reservas, solo gestores)
    public function index(Request $request)
    {
        // Expiramos las vencidas antes de mostrar la lista
        Reserva::expirarVencidas();

        $estado = $request->query('estado');

        $reservas = Reserva::with(['usuario', 'libro'])
            ->when($estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->orderByDesc('fecha_reserva')
            ->get();

        return view('reservas.index', compact('reservas', 'estado'));
    }

    // Lista las reservas del estudiante (GET /mis-reservas)
    public function misReservas(Request $request)
    {
        // Primero expiramos las que ya vencieron
        Reserva::expirarVencidas();

        $reservas = $request->user()
            ->reservas()
            ->with('libro')
            ->orderByDesc('fecha_reserva')
            ->get();

        return view('mis-reservas', compact('reservas'));
    }

    // Cancela una reserva pendiente y devuelve el ejemplar (POST)
    public function cancelar(Request $request, Reserva $reserva)
    {
        // Solo el dueño puede cancelar y solo si está pendiente
        if ($reserva->id_usuario !== $request->user()->id || $reserva->estado !== 'pendiente') {
            return back()->with('error', 'Solo puedes cancelar tus reservas pendientes.');
        }

        DB::transaction(function () use ($reserva) {
            // Bloqueamos la reserva y el libro para cancelar de forma segura
            $reserva = Reserva::whereKey($reserva->id)->lockForUpdate()->first();

            if ($reserva->estado !== 'pendiente') {
                return;
            }

            $reserva->update(['estado' => 'cancelada']);

            $libro = Libro::whereKey($reserva->id_libro)->lockForUpdate()->first();
            $libro->stock_disponible++;
            $libro->save();
        });

        return redirect()->route('mis-reservas')
            ->with('exito', 'Reserva cancelada. El ejemplar volvió a estar disponible.');
    }
}
