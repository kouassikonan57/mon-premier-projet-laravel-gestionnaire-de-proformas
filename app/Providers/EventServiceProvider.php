<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Les événements à écouter pour votre application.
     *
     * @var array
     */
    protected $listen = [
        // Exemple :
        // 'App\Events\SomeEvent' => [
        //     'App\Listeners\EventListener',
        // ],
    ];

    /**
     * Enregistre les services liés aux événements.
     */
    public function boot(): void
    {
        //
    }
}
