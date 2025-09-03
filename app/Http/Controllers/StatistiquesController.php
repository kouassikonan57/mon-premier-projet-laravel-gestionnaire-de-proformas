<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Facture;
use App\Models\FactureArticle;

class StatistiquesController extends Controller
{
    public function ventes(Request $request)
    {
        // Cette vue va seulement charger le template et le JavaScript
        return view('statistiques.ventes', [
            'month' => $request->input('month'),
            'year' => $request->input('year', now()->year),
        ]);
    }

    public function ventesData(Request $request)
    {
        $user = auth()->user();
        $month = $request->input('month');
        $year = $request->input('year', now()->year);

        $from = $month ? Carbon::create($year, $month)->startOfMonth() : null;
        $to = $month ? Carbon::create($year, $month)->endOfMonth() : null;

        // 🔝 Top produits
        $articlesQuery = FactureArticle::select('designation')
            ->selectRaw('SUM(quantity) as total_vendus')
            ->join('factures', 'facture_articles.facture_id', '=', 'factures.id')
            ->when(!$user->isAdmin(), function ($query) use ($user) {
                $query->where('factures.filiale_id', $user->filiale_id);
            })
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('factures.date', [$from, $to]);
            })
            ->groupBy('designation');

        $topProducts = (clone $articlesQuery)->orderByDesc('total_vendus')->limit(5)->get();
        $bottomProducts = (clone $articlesQuery)->orderBy('total_vendus')->limit(5)->get();

        // 💰 Total des ventes
        $facturesQuery = Facture::query();

        if (!$user->isAdmin()) {
            $facturesQuery->where('filiale_id', $user->filiale_id);
        }

        if ($from && $to) {
            $facturesQuery->whereBetween('date', [$from, $to]);
        }

        $totalVentes = $facturesQuery->sum(DB::raw('amount * 1.18'));

        // 📈 Ventes mensuelles
        $monthlySales = Facture::selectRaw('MONTH(date) as month, SUM(amount * 1.18) as total')
            ->whereYear('date', $year);

        if (!$user->isAdmin()) {
            $monthlySales->where('filiale_id', $user->filiale_id);
        }

        $monthlySales = $monthlySales->groupBy('month')->get();

        $salesPerMonth = [];
        for ($m = 1; $m <= 12; $m++) {
            $salesPerMonth[$m] = 0;
        }

        foreach ($monthlySales as $sale) {
            $salesPerMonth[$sale->month] = $sale->total;
        }

        // 👥 Meilleurs clients
        $clientsQuery = Facture::select('client_id')
            ->selectRaw('SUM(amount * 1.18) as total_ca')
            ->with('client');

        if (!$user->isAdmin()) {
            $clientsQuery->where('filiale_id', $user->filiale_id);
        }

        if ($from && $to) {
            $clientsQuery->whereBetween('date', [$from, $to]);
        }

        $bestClients = $clientsQuery
            ->groupBy('client_id')
            ->orderByDesc('total_ca')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->client->name ?? 'N/A',
                    'total_ca' => $item->total_ca,
                ];
            });

        return response()->json([
            'topProducts' => $topProducts,
            'bottomProducts' => $bottomProducts,
            'totalVentes' => $totalVentes,
            'month' => $month,
            'year' => $year,
            'salesPerMonth' => $salesPerMonth,
            'bestClients' => $bestClients,
            'lastUpdate' => now()->format('H:i:s'),
        ]);
    }
}