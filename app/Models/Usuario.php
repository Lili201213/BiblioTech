<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Modelo de la tabla "usuarios".
 * Extiende Authenticatable para poder iniciar sesión con Auth::attempt().
 */
class Usuario extends Authenticatable
{
    // Nombre real de la tabla en la base de datos
    protected $table = 'usuarios';

    // Campos que se pueden guardar desde el controlador
    protected $fillable = [
        'nombre',
        'email',
        'password_hash',
        'rol',
        'estado',
    ];

    // El hash de la contraseña nunca se devuelve en las respuestas
    protected $hidden = [
        'password_hash',
    ];

    /**
     * Laravel compara la contraseña con esta columna.
     * Aquí se llama password_hash (no "password"), por eso la sobrescribimos.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Laravel re-encripta la clave al iniciar sesión si cambia el costo del hash
     * y necesita saber en qué columna escribir el hash nuevo.
     */
    public function getAuthPasswordName()
    {
        return 'password_hash';
    }

    // Devuelve true si el usuario puede gestionar la biblioteca
    public function esGestor(): bool
    {
        return in_array($this->rol, ['administrador', 'bibliotecario'], true);
    }

    // Relaciones
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'id_usuario');
    }

    public function prestamos()
    {
        return $this->hasMany(Prestamo::class, 'id_usuario');
    }
}
