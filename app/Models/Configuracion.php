<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de la tabla NUEVA "configuraciones".
 * Guarda las reglas editables del sistema (días de préstamo, multas, etc.).
 */
class Configuracion extends Model
{
    // Nombre real de la tabla en la base de datos
    protected $table = 'configuraciones';

    // Campos que se pueden guardar desde el controlador
    protected $fillable = [
        'clave',
        'valor',
    ];

    /**
     * Valores por defecto (los mismos que inserta la migración).
     * Se usan si una clave no existe en la base de datos.
     */
    public const POR_DEFECTO = [
        'prestamo_dias' => '14',
        'prestamo_max_activos' => '3',
        'reserva_max_activas' => '3',
        'reserva_horas_vigencia' => '48',
        'multa_por_dia' => '0.20',
        'multa_dias_gracia' => '0',
        'bloquear_con_multas' => '1',
    ];

    /**
     * Arreglo con las configuraciones leídas.
     * Se llena una sola vez por petición para no repetir consultas.
     */
    protected static ?array $cache = null;

    /**
     * Todas las configuraciones como arreglo clave => valor.
     * Si falta una clave en la BD, devuelve su valor por defecto.
     */
    public static function todas(): array
    {
        if (static::$cache === null) {
            $filas = static::query()->pluck('valor', 'clave')->toArray();

            // Los valores guardados tienen prioridad sobre los defectos
            static::$cache = array_merge(self::POR_DEFECTO, $filas);
        }

        return static::$cache;
    }

    /**
     * Devuelve el valor de una clave (texto).
     * Si la clave no existe, usa la lista POR_DEFECTO o el valor dado.
     */
    public static function obtener(string $clave, $porDefecto = null)
    {
        $todas = static::todas();

        if (array_key_exists($clave, $todas)) {
            return $todas[$clave];
        }

        return self::POR_DEFECTO[$clave] ?? $porDefecto;
    }

    // Devuelve el valor como número entero
    public static function entero(string $clave): int
    {
        return (int) static::obtener($clave);
    }

    // Devuelve el valor como número decimal
    public static function decimal(string $clave): float
    {
        return (float) static::obtener($clave);
    }

    // Devuelve true si el valor es 1 (sí) y false si es 0 (no)
    public static function booleano(string $clave): bool
    {
        return (bool) (int) static::obtener($clave);
    }

    /**
     * Guarda (crea o actualiza) varias configuraciones.
     * Después limpia la caché para que la próxima lectura sea fresca.
     */
    public static function guardar(array $datos): void
    {
        foreach ($datos as $clave => $valor) {
            static::updateOrCreate(
                ['clave' => $clave],
                ['valor' => (string) $valor]
            );
        }

        static::$cache = null;
    }
}
