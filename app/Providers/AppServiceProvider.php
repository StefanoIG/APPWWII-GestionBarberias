<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;

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
