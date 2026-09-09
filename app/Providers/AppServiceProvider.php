<?php

namespace App\Providers;

use App\Auth\UsuariosUserProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
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
        Paginator::useBootstrapFive();

        Auth::provider('usuarios-provider', function ($app, array $config) {
            return new UsuariosUserProvider(
                $app['hash'],
                $config['model']
            );
        });
    }
}
