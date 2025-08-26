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
        $user = auth()->user();
        $month = $request->input('month');
        $year = $request->input('year', now()->year);

        $from = $month ? Carbon::create($year, $month)->startOfMonth() : null;
        $to = $month ? Carbon::create($year, $month)->endOfMonth() : null;

        // 🔝 Top produits
        $articlesQuery = FactureArticle::select('designation')
            ->selectRaw('SUM(quantity) as total_vendus')
            ->with('facture')
            ->whereHas('facture', function ($query) use ($user, $from, $to) {
                if (!$user->isAdmin()) {
                    $query->where('filiale_id', $user->filiale_id);
                }
                if ($from && $to) {
                    $query->whereBetween('date', [$from, $to]);
                }
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
            ->with('client')
            ->whereHas('client');

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
                return (object)[
                    'name' => $item->client->name ?? 'N/A',
                    'total_ca' => $item->total_ca,
                ];
            });

        return view('statistiques.ventes', [
            'topProducts' => $topProducts,
            'bottomProducts' => $bottomProducts,
            'totalVentes' => $totalVentes,
            'month' => $month,
            'year' => $year,
            'salesPerMonth' => $salesPerMonth,
            'bestClients' => $bestClients,
        ]);
    }

}