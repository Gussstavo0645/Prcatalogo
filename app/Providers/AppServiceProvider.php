<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        Paginator::useBootstrapFive();

        // Compartir categorías en todas las vistas
        View::share('categories', Category::orderBy('name')->get());

        // LOG TEMPORAL: detectar qué ruta consulta admin_ml
        DB::listen(function ($query) {
            $connection = $query->connectionName ?? null;

            if ($connection === 'admin_ml') {
                Log::info('CONSULTA ADMIN_ML', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time_ms' => $query->time,
                    'url' => request()?->fullUrl(),
                    'method' => request()?->method(),
                    'route' => optional(request()->route())->getName(),
                    'ip' => request()?->ip(),
                    'user_agent' => request()?->userAgent(),
                ]);
            }
        });
    }
}