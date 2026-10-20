<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\Libro;
use App\Models\Reserva;
use Illuminate\Http\Request;

/**
 * Catálogo de libros para todos los usuarios con sesión.
 */
class CatalogoController extends Controller
{
    // Lista los libros con búsqueda (?q=) y filtro por categoría (?categoria=)
    public function index(Request $request)
    {
        // Las reservas vencidas devuelven sus ejemplares antes de mostrar el catálogo
        Reserva::expirarVencidas();

        $busqueda = $request->query('q');
        $idCategoria = $request->query('categoria');
        $categorias = Categoria::orderBy('nombre')->get();

        $libros = Libro::with('categoria')
            ->when($busqueda, function ($query, $busqueda) {
                $query->where(function ($q) use ($busqueda) {
                    $q->where('titulo', 'like', "%{$busqueda}%")
                        ->orWhere('autor', 'like', "%{$busqueda}%")
                        ->orWhere('isbn', 'like', "%{$busqueda}%");
                });
            })
            ->when($idCategoria, function ($query, $idCategoria) {
                $query->where('id_categoria', $idCategoria);
            })
            ->orderBy('titulo')
            ->paginate(9)
            ->withQueryString();

        return view('catalogo.index', compact('libros', 'categorias', 'busqueda', 'idCategoria'));
    }

    // Página de detalle de un libro
    public function show(Libro $libro)
    {
        $libro->load('categoria');

        // Vigencia de la reserva según la configuración (para el aviso de "Reservar")
        $horasReserva = Configuracion::entero('reserva_horas_vigencia');

        return view('catalogo.show', compact('libro', 'horasReserva'));
    }
}
