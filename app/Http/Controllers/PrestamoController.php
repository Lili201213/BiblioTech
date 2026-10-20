<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Libro;
use App\Models\Multa;
use App\Models\Prestamo;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Préstamos: gestión (gestor) y consulta personal (estudiante).
 */
class PrestamoController extends Controller
{
    // Listado con filtro por estado y búsqueda (GET /prestamos)
    public function index(Request $request)
    {
        // Marcamos como vencidos los que se pasaron de fecha
        Prestamo::marcarVencidos();

        $estado = $request->query('estado');
        $busqueda = $request->query('q');

        $prestamos = Prestamo::with(['usuario', 'libro'])
            ->when($estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->when($busqueda, function ($query, $busqueda) {
                $query->where(function ($q) use ($busqueda) {
                    $q->whereHas('usuario', function ($usuario) use ($busqueda) {
                        $usuario->where('nombre', 'like', "%{$busqueda}%");
                    })->orWhereHas('libro', function ($libro) use ($busqueda) {
                        $libro->where('titulo', 'like', "%{$busqueda}%");
                    });
                });
            })
            ->orderByDesc('fecha_prestamo')
            ->get();

        return view('prestamos.index', compact('prestamos', 'estado', 'busqueda'));
    }

    // Formulario de préstamo (GET /prestamos/create)
    // Si viene ?reserva=ID, el formulario llega rellenado con esa reserva.
    public function create(Request $request)
    {
        // Actualizamos reservas vencidas y préstamos atrasados
        Reserva::expirarVencidas();
        Prestamo::marcarVencidos();

        $reserva = null;

        if ($request->query('reserva')) {
            $reserva = Reserva::with(['usuario', 'libro'])->find($request->query('reserva'));

            // Solo se puede entregar una reserva que sigue pendiente
            if ($reserva && $reserva->estado !== 'pendiente') {
                return redirect()->route('prestamos.create')
                    ->with('error', 'Esa reserva ya no está pendiente.');
            }
        }

        $usuarios = Usuario::where('rol', 'estudiante')
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $libros = Libro::orderBy('titulo')->get();

        // Días máximo del préstamo, según la configuración
        $maxDias = Configuracion::entero('prestamo_dias');

        return view('prestamos.create', compact('usuarios', 'libros', 'reserva', 'maxDias'));
    }

    // Registra el préstamo (POST /prestamos)
    public function store(Request $request)
    {
        // Reglas del sistema que puede cambiar el administrador
        $maxDias = Configuracion::entero('prestamo_dias');
        $maxActivos = Configuracion::entero('prestamo_max_activos');
        $bloquearConMultas = Configuracion::booleano('bloquear_con_multas');

        $datos = $request->validate([
            'id_usuario' => [
                'required',
                Rule::exists('usuarios', 'id')->where('rol', 'estudiante')->where('estado', 'activo'),
            ],
            'id_libro' => ['required', 'exists:libros,id'],
            'dias' => ['nullable', 'integer', "between:1,{$maxDias}"],
            'id_reserva' => ['nullable', 'exists:reservas,id'],
        ], [
            'id_usuario.exists' => 'El usuario debe ser un estudiante activo.',
            'id_libro.exists' => 'El libro seleccionado no existe.',
            'dias.between' => "Los días del préstamo deben estar entre 1 y {$maxDias}.",
            'id_reserva.exists' => 'La reserva indicada no existe.',
        ]);

        // Por defecto el préstamo dura los días configurados
        $dias = $datos['dias'] ?? $maxDias;
        $usuario = Usuario::findOrFail($datos['id_usuario']);

        DB::transaction(function () use ($datos, $usuario, $dias) {
            // Bloqueamos el libro (hace falta para actualizar su stock)
            $libro = Libro::whereKey($datos['id_libro'])->lockForUpdate()->first();

            // El usuario no debe tener multas pendientes (solo si el bloqueo está activo)
            $tieneMulta = Multa::whereHas('prestamo', function ($query) use ($usuario) {
                $query->where('id_usuario', $usuario->id);
            })->where('estado', 'pendiente')->exists();

            if ($bloquearConMultas && $tieneMulta) {
                throw ValidationException::withMessages([
                    'id_usuario' => 'El usuario tiene multas pendientes y no puede recibir préstamos.',
                ]);
            }

            // Máximo de préstamos activos o vencidos según la configuración
            $activos = Prestamo::where('id_usuario', $usuario->id)
                ->whereIn('estado', ['activo', 'vencido'])
                ->count();

            if ($activos >= $maxActivos) {
                throw ValidationException::withMessages([
                    'id_usuario' => "El usuario ya tiene {$maxActivos} préstamos activos o vencidos.",
                ]);
            }

            if (!empty($datos['id_reserva'])) {
                // Viniendo de una reserva: NO se resta stock (ya se restó al reservar)
                $reserva = Reserva::whereKey($datos['id_reserva'])->lockForUpdate()->first();

                if ($reserva->estado !== 'pendiente') {
                    throw ValidationException::withMessages([
                        'id_reserva' => 'La reserva ya no está pendiente.',
                    ]);
                }

                if ($reserva->id_usuario != $usuario->id || $reserva->id_libro != $libro->id) {
                    throw ValidationException::withMessages([
                        'id_reserva' => 'La reserva no coincide con el usuario o el libro elegidos.',
                    ]);
                }
            } else {
                // Préstamo directo: sí se descuenta 1 ejemplar
                if ($libro->stock_disponible <= 0) {
                    throw ValidationException::withMessages([
                        'id_libro' => 'El libro no tiene ejemplares disponibles.',
                    ]);
                }

                $libro->stock_disponible--;
                $libro->save();
            }

            // Creamos el préstamo
            Prestamo::create([
                'id_usuario' => $usuario->id,
                'id_libro' => $libro->id,
                'id_reserva' => $datos['id_reserva'] ?? null,
                'fecha_prestamo' => today(),
                'fecha_devolucion_esperada' => today()->addDays($dias),
                'estado' => 'activo',
            ]);

            // La reserva pasa de pendiente a confirmada
            if (!empty($datos['id_reserva'])) {
                Reserva::whereKey($datos['id_reserva'])->update(['estado' => 'confirmada']);
            }
        });

        return redirect()->route('prestamos.index')->with('exito', 'Préstamo registrado.');
    }

    // Devuelve un libro y genera multa si llega tarde (POST /prestamos/{prestamo}/devolver)
    public function devolver(Prestamo $prestamo)
    {
        // Solo se puede devolver si está activo o vencido
        if (!in_array($prestamo->estado, ['activo', 'vencido'], true)) {
            return back()->with('error', 'Solo se pueden devolver préstamos activos o vencidos.');
        }

        $multaGenerada = null;

        DB::transaction(function () use ($prestamo, &$multaGenerada) {
            // Bloqueamos el préstamo para no devolverlo dos veces
            $prestamo = Prestamo::whereKey($prestamo->id)->lockForUpdate()->first();

            $prestamo->fecha_devolucion_real = today();
            $prestamo->estado = 'devuelto';
            $prestamo->save();

            // Devolvemos el ejemplar al stock
            $libro = Libro::whereKey($prestamo->id_libro)->lockForUpdate()->first();
            $libro->stock_disponible++;
            $libro->save();

            // Si la entrega se retrasó, calculamos la multa según la configuración:
            // días de retraso = hoy - fecha_devolucion_esperada
            if (today()->gt($prestamo->fecha_devolucion_esperada)) {
                $diasRetraso = (int) abs(today()->diffInDays($prestamo->fecha_devolucion_esperada));

                // Días cobrables = retraso menos los días de gracia configurados
                $diasGracia = Configuracion::entero('multa_dias_gracia');
                $diasCobrables = max(0, $diasRetraso - $diasGracia);

                if ($diasCobrables > 0) {
                    // Monto = días cobrables × mora por día (redondeado a 2 decimales)
                    $monto = round($diasCobrables * Configuracion::decimal('multa_por_dia'), 2);

                    $multaGenerada = Multa::create([
                        'id_prestamo' => $prestamo->id,
                        'monto' => $monto,
                        'estado' => 'pendiente',
                        'fecha_generacion' => now(),
                    ]);
                }
            }
        });

        if ($multaGenerada) {
            return redirect()->route('prestamos.index')->with(
                'exito',
                'Préstamo devuelto. Se generó una multa de S/ ' . number_format($multaGenerada->monto, 2)
                    . ' por el retraso.'
            );
        }

        return redirect()->route('prestamos.index')->with('exito', 'Préstamo devuelto. Sin multa.');
    }

    // Préstamos y multas del estudiante (GET /mis-prestamos)
    public function misPrestamos(Request $request)
    {
        // Mantenemos actualizados los estados antes de mostrar
        Prestamo::marcarVencidos();
        Reserva::expirarVencidas();

        $usuario = $request->user();

        $prestamos = $usuario->prestamos()
            ->with('libro')
            ->orderByDesc('fecha_prestamo')
            ->get();

        $multas = Multa::whereHas('prestamo', function ($query) use ($usuario) {
            $query->where('id_usuario', $usuario->id);
        })->orderByDesc('fecha_generacion')->get();

        return view('mis-prestamos', compact('prestamos', 'multas'));
    }
}
