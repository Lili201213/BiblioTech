<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de la tabla "multas".
 */
class Multa extends Model
{
    // El monto de la multa se calcula con Configuracion::decimal('multa_por_dia')
    // (antes había aquí una constante MONTO_POR_DIA que ya no existe)

    // Nombre real de la tabla en la base de datos
    protected $table = 'multas';

    // Campos que se pueden guardar desde el controlador
    protected $fillable = [
        'id_prestamo',
        'monto',
        'estado',
        'fecha_generacion',
        'fecha_pago',
    ];

    // Tipos de datos de los campos
    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha_generacion' => 'datetime',
            'fecha_pago' => 'datetime',
        ];
    }

    // Relaciones
    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class, 'id_prestamo');
    }
}
