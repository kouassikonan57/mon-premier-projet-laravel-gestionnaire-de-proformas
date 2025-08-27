<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Ajouter le middleware d'authentification
        Broadcast::routes(['middleware' => ['auth:web']]);
        
        require base_path('routes/channels.php');
    }
}