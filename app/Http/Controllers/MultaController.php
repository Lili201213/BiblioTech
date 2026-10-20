<?php

namespace App\Http\Controllers;

use App\Models\Multa;
use Illuminate\Http\Request;

/**
 * Multas (solo administrador y bibliotecario).
 */
class MultaController extends Controller
{
    // Listado con filtro por estado (GET /multas)
    public function index(Request $request)
    {
        $estado = $request->query('estado');

        $multas = Multa::with('prestamo.usuario', 'prestamo.libro')
            ->when($estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->orderByDesc('fecha_generacion')
            ->get();

        return view('multas.index', compact('multas', 'estado'));
    }

    // Marca una multa pendiente como pagada (POST)
    public function pagar(Multa $multa)
    {
        if ($multa->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden pagar multas pendientes.');
        }

        $multa->update([
            'estado' => 'pagada',
            'fecha_pago' => now(),
        ]);

        return back()->with('exito', 'Multa marcada como pagada.');
    }

    // Condonar una multa pendiente (POST)
    public function condonar(Multa $multa)
    {
        if ($multa->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden condonar multas pendientes.');
        }

        $multa->update([
            'estado' => 'condonada',
            'fecha_pago' => now(),
        ]);

        return back()->with('exito', 'Multa condonada.');
    }
}
