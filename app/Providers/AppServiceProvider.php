<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Proforma;
use App\Observers\ProformaObserver;
use App\Models\Facture;
use App\Observers\FactureObserver;
use Illuminate\Pagination\Paginator;

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
        Proforma::observe(ProformaObserver::class);
        Facture::observe(FactureObserver::class);
        Paginator::useBootstrap();
    }

    public function shareFiliales()
    {
        view()->composer('layouts.app', function ($view) {
            $filiales = Filiale::all();
            $view->with('filiales', $filiales);
        });
    }
}
