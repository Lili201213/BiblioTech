<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de la tabla "libros".
 */
class Libro extends Model
{
    // Nombre real de la tabla en la base de datos
    protected $table = 'libros';

    // Campos que se pueden guardar desde el controlador
    protected $fillable = [
        'titulo',
        'autor',
        'isbn',
        'id_categoria',
        'editorial',
        'anio',
        'portada_url',
        'sinopsis',
        'stock_total',
        'stock_disponible',
    ];

    // Relaciones
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'id_libro');
    }

    public function prestamos()
    {
        return $this->hasMany(Prestamo::class, 'id_libro');
    }
}
