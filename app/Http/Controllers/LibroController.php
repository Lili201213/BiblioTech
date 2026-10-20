<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Libro;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * CRUD de libros (solo administrador y bibliotecario).
 */
class LibroController extends Controller
{
    // Listado con buscador ?q= (título, autor o ISBN)
    public function index(Request $request)
    {
        $busqueda = $request->query('q');

        $libros = Libro::with('categoria')
            ->when($busqueda, function ($query, $busqueda) {
                $query->where(function ($q) use ($busqueda) {
                    $q->where('titulo', 'like', "%{$busqueda}%")
                        ->orWhere('autor', 'like', "%{$busqueda}%")
                        ->orWhere('isbn', 'like', "%{$busqueda}%");
                });
            })
            ->orderBy('titulo')
            ->get();

        return view('libros.index', compact('libros', 'busqueda'));
    }

    // Formulario de creación
    public function create()
    {
        $categorias = Categoria::orderBy('nombre')->get();

        return view('libros.create', compact('categorias'));
    }

    // Guarda un libro nuevo
    public function store(Request $request)
    {
        $datos = $this->validarLibro($request);

        DB::transaction(function () use ($datos) {
            Libro::create([
                'titulo' => $datos['titulo'],
                'autor' => $datos['autor'],
                'isbn' => $datos['isbn'] ?? null,
                'id_categoria' => $datos['id_categoria'] ?? null,
                'editorial' => $datos['editorial'] ?? null,
                'anio' => $datos['anio'] ?? null,
                'portada_url' => $datos['portada_url'] ?? null,
                'sinopsis' => $datos['sinopsis'] ?? null,
                'stock_total' => $datos['stock_total'],
                // Al crear, todos los ejemplares están disponibles
                'stock_disponible' => $datos['stock_total'],
            ]);
        });

        return redirect()->route('libros.index')->with('exito', 'Libro creado.');
    }

    // Formulario de edición
    public function edit(Libro $libro)
    {
        $categorias = Categoria::orderBy('nombre')->get();

        return view('libros.edit', compact('libro', 'categorias'));
    }

    // Guarda los cambios de un libro
    public function update(Request $request, Libro $libro)
    {
        $datos = $this->validarLibro($request, $libro);

        DB::transaction(function () use ($libro, $datos) {
            // Bloqueamos la fila para que nadie edite el libro al mismo tiempo
            $libro = Libro::whereKey($libro->id)->lockForUpdate()->first();

            $nuevoTotal = $datos['stock_total'];
            $prestados = $libro->stock_total - $libro->stock_disponible;
            $nuevoDisponible = $libro->stock_disponible + ($nuevoTotal - $libro->stock_total);

            // Si al bajar el stock quedarían ejemplares "prestados" sin existir, no se puede
            if ($nuevoDisponible < 0) {
                $plural = $prestados === 1 ? '' : 'es';
                throw ValidationException::withMessages([
                    'stock_total' => "No puedes bajar el stock a {$nuevoTotal}: "
                        . "hay {$prestados} ejemplar{$plural} prestado{$plural}.",
                ]);
            }

            $libro->update([
                'titulo' => $datos['titulo'],
                'autor' => $datos['autor'],
                'isbn' => $datos['isbn'] ?? null,
                'id_categoria' => $datos['id_categoria'] ?? null,
                'editorial' => $datos['editorial'] ?? null,
                'anio' => $datos['anio'] ?? null,
                'portada_url' => $datos['portada_url'] ?? null,
                'sinopsis' => $datos['sinopsis'] ?? null,
                'stock_total' => $nuevoTotal,
                'stock_disponible' => $nuevoDisponible,
            ]);
        });

        return redirect()->route('libros.index')->with('exito', 'Libro actualizado.');
    }

    // Elimina un libro (la base lo impide si tiene préstamos)
    public function destroy(Libro $libro)
    {
        try {
            $libro->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'No se puede eliminar: el libro tiene préstamos asociados.');
        }

        return redirect()->route('libros.index')->with('exito', 'Libro eliminado.');
    }

    // Reglas de validación compartidas entre store y update
    private function validarLibro(Request $request, Libro $libro = null): array
    {
        // El ISBN es único pero al editar se ignora el propio libro
        $reglasIsbn = ['nullable', 'max:20'];
        $reglasIsbn[] = $libro ? "unique:libros,isbn,{$libro->id}" : 'unique:libros,isbn';

        return $request->validate([
            'titulo' => ['required', 'max:255'],
            'autor' => ['required', 'max:180'],
            'isbn' => $reglasIsbn,
            'id_categoria' => ['nullable', 'exists:categorias,id'],
            'editorial' => ['nullable', 'max:150'],
            'anio' => ['nullable', 'integer', 'between:1000,' . (date('Y') + 1)],
            'portada_url' => ['nullable', 'max:500'],
            'sinopsis' => ['nullable', 'string'],
            'stock_total' => ['required', 'integer', 'min:0'],
        ]);
    }
}
