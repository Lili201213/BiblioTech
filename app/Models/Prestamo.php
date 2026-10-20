<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de la tabla "prestamos".
 */
class Prestamo extends Model
{
    // Nombre real de la tabla en la base de datos
    protected $table = 'prestamos';

    // Campos que se pueden guardar desde el controlador
    protected $fillable = [
        'id_usuario',
        'id_libro',
        'id_reserva',
        'fecha_prestamo',
        'fecha_devolucion_esperada',
        'fecha_devolucion_real',
        'estado',
    ];

    // Fechas guardadas como fecha (sin hora)
    protected function casts(): array
    {
        return [
            'fecha_prestamo' => 'date',
            'fecha_devolucion_esperada' => 'date',
            'fecha_devolucion_real' => 'date',
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

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'id_reserva');
    }

    public function multas()
    {
        return $this->hasMany(Multa::class, 'id_prestamo');
    }

    /**
     * Marca como "vencido" los préstamos activos cuya fecha de
     * devolución esperada ya pasó.
     */
    public static function marcarVencidos(): void
    {
        DB::transaction(function () {
            $vencidos = Prestamo::where('estado', 'activo')
                ->where('fecha_devolucion_esperada', '<', today())
                ->lockForUpdate()
                ->get();

            foreach ($vencidos as $prestamo) {
                $prestamo->update(['estado' => 'vencido']);
            }
        });
    }
}
