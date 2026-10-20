<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

/**
 * CRUD de categorías (solo administrador y bibliotecario).
 */
class CategoriaController extends Controller
{
    // Listado con el número de libros de cada categoría
    public function index()
    {
        $categorias = Categoria::withCount('libros')
            ->orderBy('nombre')
            ->get();

        return view('categorias.index', compact('categorias'));
    }

    // Formulario de creación
    public function create()
    {
        return view('categorias.create');
    }

    // Guarda una categoría nueva
    public function store(Request $request)
    {
        $datos = $this->validarCategoria($request);

        Categoria::create([
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
        ]);

        return redirect()->route('categorias.index')->with('exito', 'Categoría creada.');
    }

    // Formulario de edición
    public function edit(Categoria $categoria)
    {
        return view('categorias.edit', compact('categoria'));
    }

    // Guarda los cambios de una categoría
    public function update(Request $request, Categoria $categoria)
    {
        $datos = $this->validarCategoria($request, $categoria);

        $categoria->update([
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
        ]);

        return redirect()->route('categorias.index')->with('exito', 'Categoría actualizada.');
    }

    // Elimina la categoría (la base deja sus libros con id_categoria = NULL)
    public function destroy(Categoria $categoria)
    {
        $categoria->delete();

        return redirect()->route('categorias.index')->with('exito', 'Categoría eliminada.');
    }

    // Reglas de validación compartidas entre store y update
    private function validarCategoria(Request $request, Categoria $categoria = null): array
    {
        // El nombre es único pero al editar se ignora la propia categoría
        $reglasNombre = ['required', 'max:100'];
        $reglasNombre[] = $categoria ? "unique:categorias,nombre,{$categoria->id}" : 'unique:categorias,nombre';

        return $request->validate([
            'nombre' => $reglasNombre,
            'descripcion' => ['nullable', 'string'],
        ]);
    }
}
