<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\LibroController;
use App\Http\Controllers\MultaController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\ReservaController;
use Illuminate\Support\Facades\Route;

// La raíz siempre manda al login
Route::get('/', function () {
    return redirect()->route('login');
});

// Inicio y cierre de sesión
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Panel de bienvenida (solo usuarios con sesión)
Route::get('/panel', [PanelController::class, 'show'])
    ->middleware('auth')
    ->name('panel');

// CRUD de libros: solo administrador y bibliotecario
Route::middleware(['auth', 'rol:administrador,bibliotecario'])->group(function () {
    Route::resource('libros', LibroController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    Route::resource('categorias', CategoriaController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
});

// Catálogo: cualquier usuario con sesión (estudiante o gestor)
Route::middleware('auth')->group(function () {
    Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo');
    Route::get('/catalogo/{libro}', [CatalogoController::class, 'show'])->name('catalogo.detalle');
});

// Reservas: solo estudiantes
Route::middleware(['auth', 'rol:estudiante'])->group(function () {
    Route::post('/reservas', [ReservaController::class, 'store'])->name('reservas.store');
    Route::get('/mis-reservas', [ReservaController::class, 'misReservas'])->name('mis-reservas');
    Route::post('/mis-reservas/{reserva}/cancelar', [ReservaController::class, 'cancelar'])
        ->name('reservas.cancelar');

    // Préstamos y multas propios del estudiante
    Route::get('/mis-prestamos', [PrestamoController::class, 'misPrestamos'])->name('mis-prestamos');
});

// Gestión: reservas, préstamos y multas (solo administrador y bibliotecario)
Route::middleware(['auth', 'rol:administrador,bibliotecario'])->group(function () {
    // Reservas de todos los usuarios
    Route::get('/reservas', [ReservaController::class, 'index'])->name('reservas.index');

    // Préstamos
    Route::get('/prestamos', [PrestamoController::class, 'index'])->name('prestamos.index');
    Route::get('/prestamos/create', [PrestamoController::class, 'create'])->name('prestamos.create');
    Route::post('/prestamos', [PrestamoController::class, 'store'])->name('prestamos.store');
    Route::post('/prestamos/{prestamo}/devolver', [PrestamoController::class, 'devolver'])
        ->name('prestamos.devolver');

    // Multas
    Route::get('/multas', [MultaController::class, 'index'])->name('multas.index');
    Route::post('/multas/{multa}/pagar', [MultaController::class, 'pagar'])->name('multas.pagar');
    Route::post('/multas/{multa}/condonar', [MultaController::class, 'condonar'])->name('multas.condonar');
});

// Configuración del sistema: SOLO administrador (el bibliotecario recibe 403)
Route::middleware(['auth', 'rol:administrador'])->group(function () {
    Route::get('/configuracion', [ConfiguracionController::class, 'edit'])
        ->name('configuracion.edit');
    Route::put('/configuracion', [ConfiguracionController::class, 'update'])
        ->name('configuracion.update');
});

