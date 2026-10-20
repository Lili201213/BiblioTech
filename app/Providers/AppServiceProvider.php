<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Usa la vista de paginación personalizada (resources/views/paginacion.blade.php)
        \Illuminate\Pagination\Paginator::defaultView('paginacion');
    }
}
