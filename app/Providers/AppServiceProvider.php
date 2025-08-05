<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Routing\UrlGenerator;

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
    public function boot(UrlGenerator $url): void
    {
        // Forzar HTTPS solo en producción (Render)
        if (env('APP_ENV') === 'production') {
            $url->forceScheme('https');
        }

        // Registrar las políticas manualmente (opcional, Laravel las auto-descubre)
        Gate::policy(\App\Models\Cita::class, \App\Policies\CitaPolicy::class);

        // Registrar los listeners de eventos
        Event::listen(
            \App\Events\CitaAgendada::class,
            \App\Listeners\EnviarEmailConfirmacionCita::class
        );

        Event::listen(
            \App\Events\ComprobanteSubido::class,
            \App\Listeners\NotificarDueñoSobreComprobante::class
        );
    }
}
