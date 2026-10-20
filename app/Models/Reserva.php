<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de la tabla "reservas".
 * Una reserva aparta 1 ejemplar del libro hasta que expire o se cancele.
 */
class Reserva extends Model
{
    // Nombre real de la tabla en la base de datos
    protected $table = 'reservas';

    // Campos que se pueden guardar desde el controlador
    protected $fillable = [
        'id_usuario',
        'id_libro',
        'fecha_reserva',
        'fecha_expiracion',
        'estado',
    ];

    // Fechas guardadas como fecha y hora
    protected function casts(): array
    {
        return [
            'fecha_reserva' => 'datetime',
            'fecha_expiracion' => 'datetime',
        ];
    }

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function libro()
    {
        return $this->belongsTo(Libro::class, 'id_libro');
    }

    /**
     * Pasa a "expirada" las reservas pendientes que ya vencieron
     * y devuelve 1 ejemplar al stock de cada libro.
     */
    public static function expirarVencidas(): void
    {
        DB::transaction(function () {
            $vencidas = Reserva::where('estado', 'pendiente')
                ->where('fecha_expiracion', '<', now())
                ->lockForUpdate()
                ->get();

            foreach ($vencidas as $reserva) {
                $reserva->update(['estado' => 'expirada']);

                // Bloqueamos el libro para sumar el ejemplar sin carreras
                $libro = Libro::whereKey($reserva->id_libro)->lockForUpdate()->first();

                if ($libro) {
                    $libro->stock_disponible++;
                    $libro->save();
                }
            }
        });
    }
}
