<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Crea la tabla NUEVA "configuraciones" con las reglas editables del sistema.
 * Es la única tabla nueva: las 6 tablas existentes no se tocan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 60)->unique(); // nombre de la regla
            $table->string('valor', 100);          // valor actual de la regla
            $table->timestamps();
        });

        // Valores por defecto, solo si la tabla quedó vacía
        if (DB::table('configuraciones')->count() === 0) {
            $ahora = now();

            DB::table('configuraciones')->insert([
                ['clave' => 'prestamo_dias', 'valor' => '14', 'created_at' => $ahora, 'updated_at' => $ahora],
                ['clave' => 'prestamo_max_activos', 'valor' => '3', 'created_at' => $ahora, 'updated_at' => $ahora],
                ['clave' => 'reserva_max_activas', 'valor' => '3', 'created_at' => $ahora, 'updated_at' => $ahora],
                ['clave' => 'reserva_horas_vigencia', 'valor' => '48', 'created_at' => $ahora, 'updated_at' => $ahora],
                ['clave' => 'multa_por_dia', 'valor' => '0.20', 'created_at' => $ahora, 'updated_at' => $ahora],
                ['clave' => 'multa_dias_gracia', 'valor' => '0', 'created_at' => $ahora, 'updated_at' => $ahora],
                ['clave' => 'bloquear_con_multas', 'valor' => '1', 'created_at' => $ahora, 'updated_at' => $ahora],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};
