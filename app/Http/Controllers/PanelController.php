<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Libro;
use App\Models\Multa;
use App\Models\Prestamo;
use App\Models\Reserva;
use Illuminate\Http\Request;

/**
 * Muestra el panel de bienvenida con los contadores según el rol.
 */
class PanelController extends Controller
{
    public function show(Request $request)
    {
        $usuario = $request->user();

        // Actualiza las reservas vencidas para que los contadores sean correctos
        Reserva::expirarVencidas();

        if ($usuario->esGestor()) {
            // Le pasamos el usuario porque el método lo usa en el compact()
            return $this->panelGestor($usuario);
        }

        return $this->panelEstudiante($usuario);
    }

    /**
     * Panel de administrador y bibliotecario: banner, estadísticas
     * y cuatro paneles con las listas más importantes.
     */
    private function panelGestor($usuario)
    {
        // Contadores de la biblioteca
        $contadores = [
            'libros' => Libro::count(),
            'categorias' => Categoria::count(),
            'prestamos_activos' => Prestamo::where('estado', 'activo')->count(),
            'prestamos_vencidos' => Prestamo::where('estado', 'vencido')->count(),
            'multas_pendientes' => Multa::where('estado', 'pendiente')->count(),
            'reservas_pendientes' => Reserva::where('estado', 'pendiente')->count(),
        ];

        // Reservas pendientes (máximo 5), con usuario y libro cargados
        $reservasPendientes = Reserva::where('estado', 'pendiente')
            ->with(['usuario', 'libro'])
            ->orderBy('fecha_expiracion')
            ->take(5)
            ->get();

        // Préstamos activos o vencidos que más apuran (máximo 5)
        $prestamosPorVencer = Prestamo::whereIn('estado', ['activo', 'vencido'])
            ->with(['usuario', 'libro'])
            ->orderBy('fecha_devolucion_esperada')
            ->take(5)
            ->get();

        // Libros con poco stock (máximo 5)
        $librosPocoStock = Libro::where('stock_disponible', '<=', 2)
            ->orderBy('stock_disponible')
            ->take(5)
            ->get();

        // Libros más prestados (máximo 5, solo los que tienen al menos 1 préstamo)
        $librosMasPrestados = Libro::withCount('prestamos')
            ->orderByDesc('prestamos_count')
            ->having('prestamos_count', '>', 0)
            ->take(5)
            ->get();

        return view('panel', compact(
            'usuario',
            'contadores',
            'reservasPendientes',
            'prestamosPorVencer',
            'librosPocoStock',
            'librosMasPrestados'
        ));
    }

    /**
     * Panel del estudiante: banner, sus estadísticas, sus reservas,
     * sus próximos vencimientos y las novedades del catálogo.
     */
    private function panelEstudiante($usuario)
    {
        // Monto total de sus multas pendientes (para la tarjeta)
        $montoMultas = Multa::whereHas('prestamo', function ($query) use ($usuario) {
            $query->where('id_usuario', $usuario->id);
        })->where('estado', 'pendiente')->sum('monto');

        $contadores = [
            'reservas_activas' => $usuario->reservas()
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->count(),
            'prestamos_activos' => $usuario->prestamos()
                ->where('estado', 'activo')
                ->count(),
            'multas_pendientes' => Multa::whereHas('prestamo', function ($query) use ($usuario) {
                $query->where('id_usuario', $usuario->id);
            })->where('estado', 'pendiente')->count(),
            'monto_multas' => $montoMultas,
        ];

        // Sus reservas activas (máximo 5)
        $misReservasActivas = $usuario->reservas()
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->with('libro')
            ->orderBy('fecha_expiracion')
            ->take(5)
            ->get();

        // Sus próximos vencimientos (máximo 5)
        $misVencimientos = $usuario->prestamos()
            ->whereIn('estado', ['activo', 'vencido'])
            ->with('libro')
            ->orderBy('fecha_devolucion_esperada')
            ->take(5)
            ->get();

        // Últimos libros agregados al catálogo (los 6 más recientes)
        $novedades = Libro::with('categoria')
            ->orderByDesc('id')
            ->take(6)
            ->get();

        return view('panel', compact(
            'usuario',
            'contadores',
            'misReservasActivas',
            'misVencimientos',
            'novedades'
        ));
    }
}
