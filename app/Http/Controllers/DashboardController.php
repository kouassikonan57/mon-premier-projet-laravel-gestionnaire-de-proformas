<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Proforma;
use App\Models\Facture;
use App\Models\CatalogArticle;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Cette vue va seulement charger le template et le JavaScript
        return view('dashboard');
    }

    public function dashboardData(Request $request)
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
                ->get()
                ->map(function ($proforma) {
                    return [
                        'reference' => $proforma->reference,
                        'client_name' => $proforma->client->name ?? 'N/A',
                        'amount' => $proforma->amount,
                        'created_at' => $proforma->created_at->toISOString()
                    ];
                });

            $recentActivities = ActivityLog::latest()
                ->take(5)
                ->get()
                ->map(function ($activity) {
                    return [
                        'description' => $activity->description,
                        'action' => $activity->action,
                        'entity_type' => $activity->entity_type,
                        'created_at' => $activity->created_at->toISOString()
                    ];
                });

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
                ->get()
                ->map(function ($proforma) {
                    return [
                        'reference' => $proforma->reference,
                        'client_name' => $proforma->client->name ?? 'N/A',
                        'amount' => $proforma->amount,
                        'created_at' => $proforma->created_at->toISOString()
                    ];
                });

            $recentActivities = ActivityLog::where('filiale_id', $filialeId)
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($activity) {
                    return [
                        'description' => $activity->description,
                        'action' => $activity->action,
                        'entity_type' => $activity->entity_type,
                        'created_at' => $activity->created_at->toISOString()
                    ];
                });
        }

        // Statistiques mensuelles pour le graphique
        $monthlyStats = Proforma::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->when(!$user->isAdmin(), function ($query) use ($user) {
                $query->where('filiale_id', $user->filiale_id);
            })
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->get();

        $monthlyData = array_fill(0, 12, 0);
        foreach ($monthlyStats as $stat) {
            $monthlyData[$stat->month - 1] = $stat->count;
        }

        return response()->json([
            'proformasCount' => $proformasCount,
            'facturesCount' => $facturesCount,
            'clientsCount' => $clientsCount,
            'articlesCount' => $articlesCount,
            'recentProformas' => $recentProformas,
            'recentActivities' => $recentActivities,
            'monthlyData' => $monthlyData,
            'lastUpdate' => now()->format('H:i:s')
        ]);
    }
}