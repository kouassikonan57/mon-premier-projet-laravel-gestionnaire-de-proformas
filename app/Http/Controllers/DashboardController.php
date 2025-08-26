<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Proforma;
use App\Models\Facture;
use App\Models\CatalogArticle;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            // Données globales
            $proformasCount = Proforma::count();
            $facturesCount = Facture::count();
            $clientsCount = Client::count();
            $articlesCount = CatalogArticle::count();

            $recentProformas = Proforma::with('client')
                ->latest()
                ->take(5)
                ->get();

            $recentActivities = ActivityLog::latest()
                ->take(5)
                ->get();

        } else {
            // Données filtrées par filiale
            $filialeId = $user->filiale_id;

            $proformasCount = Proforma::where('filiale_id', $filialeId)->count();
            $facturesCount = Facture::where('filiale_id', $filialeId)->count();
            $clientsCount = Client::where('filiale_id', $filialeId)->count();
            $articlesCount = CatalogArticle::where('filiale_id', $filialeId)->count();

            $recentProformas = Proforma::with('client')
                ->where('filiale_id', $filialeId)
                ->latest()
                ->take(5)
                ->get();

            $recentActivities = ActivityLog::where('filiale_id', $filialeId)
                ->latest()
                ->take(5)
                ->get();
        }

        return view('dashboard', compact(
            'proformasCount', 
            'facturesCount', 
            'clientsCount', 
            'articlesCount',
            'recentProformas',
            'recentActivities'
        ));
    }
}