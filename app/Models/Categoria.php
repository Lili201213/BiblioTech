<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de la tabla "categorias".
 */
class Categoria extends Model
{
    // Nombre real de la tabla en la base de datos
    protected $table = 'categorias';

    // Campos que se pueden guardar desde el controlador
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Relaciones
    // Al eliminar una categoría, la base deja sus libros con id_categoria = NULL
    public function libros()
    {
        return $this->hasMany(Libro::class, 'id_categoria');
    }
}
