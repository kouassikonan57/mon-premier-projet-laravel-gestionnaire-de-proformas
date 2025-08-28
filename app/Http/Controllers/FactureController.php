<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facture;
use App\Models\Proforma;

use App\Exports\FactureExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Nidrax69\YousignApiLaravel\YousignApiLaravel;
use App\Models\FactureArticle;
use App\Models\Filiale;
use App\Models\ActionLog;
use App\Events\NouvelleFactureCree;


class FactureController extends Controller
{
    public function exportExcel(Facture $facture)
    {
        return Excel::download(new FactureExport($facture), 'facture_'.$facture->reference.'.xlsx');
    }

    public function exportPdf(Facture $facture)
    {
        $facture->load(['client', 'articles', 'filiale']);

        // Chemin absolu vers le cachet numérique
        $cachetPath = public_path('cachets/DDCS-001.png');
        $cachetPath = public_path('images/YADI-002.png');
        $cachetPath = public_path('images/YDIA_CONSTRUCTION-003.png');
        $cachetPath = public_path('images/VROOM-004.png');
        $cachetPath = public_path('images/default.png');

        // Génération du PDF avec la vue
        $pdf = Pdf::loadView('factures.pdf', compact('facture', 'cachetPath'));

        // ➕ Ajout du numéro de page via le canvas
        $pdf->getDomPDF()->set_option('isPhpEnabled', true);
        $canvas = $pdf->getDomPDF()->get_canvas();
        $canvas->page_text(270, 820, "Page {PAGE_NUM} / {PAGE_COUNT}", 'Helvetica', 10, [150, 150, 150]);
        // Téléchargement du PDF
        return $pdf->download('facture_' . $facture->reference . '.pdf');
    }

    public function destroy(Facture $facture)
    {
        if (!auth()->user()->isAdmin() && $facture->filiale_id !== auth()->user()->filiale_id) {
            abort(403, 'Accès non autorisé');
        }

        $facture->delete();
        return redirect()->route('factures.index')->with('success', 'Facture supprimée avec succès.');
    }

    /**
     * Affiche la liste paginée des factures.
     */

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Facture::with(['client', 'filiale'])
            ->orderBy('date', 'desc');

        // 🔍 Recherche par référence ou nom client
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                ->orWhereHas('client', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                });
            });
        }

        // 🏢 Filtrage selon rôle
        if ($user->isAdmin()) {
            // Admin → peut filtrer par filiale
            if ($request->filled('filiale_id')) {
                $query->where('filiale_id', $request->filiale_id);
            }
        } else {
            // Non-admin → restriction à sa filiale
            $query->where('filiale_id', $user->filiale_id);
        }

        // 📄 Pagination avec conservation des filtres
        $factures = $query->paginate(10)->withQueryString();

        // 🧠 Chargement des filiales (admin uniquement)
        $filiales = $user->isAdmin() ? Filiale::orderBy('nom')->get() : collect();

        return view('factures.index', compact('factures', 'filiales'));
    }

    /**
     * Affiche le détail d'une facture.
     */
    public function show(Facture $facture)
    {
        if (!auth()->user()->isAdmin() && $facture->filiale_id !== auth()->user()->filiale_id) {
            abort(403, 'Accès non autorisé');
        }

        $facture->load(['client', 'articles']);
        return view('factures.show', compact('facture'));
    }

    /**
     * Formulaire de création d'une facture à partir d'une proforma.
     */
    public function create(Request $request)
    {
        $proformaId = $request->query('proforma_id');

        if (!$proformaId) {
            // Si pas de proforma spécifiée, afficher une liste de choix
            $proformas = Proforma::with('client')->orderBy('created_at', 'desc')->get();
            return view('factures.select_proforma', compact('proformas'));
        }

        $proforma = Proforma::with('client')->find($proformaId);

        if (!$proforma) {
            abort(404, 'Proforma introuvable.');
        }

        // Générer la prochaine référence de facture
        $nextReference = $this->generateNextReference();

        return view('factures.create', compact('proforma', 'nextReference'));
    }

    /**
     * Génère la prochaine référence de facture
     */
    private function generateNextReference()
    {
        $lastFacture = Facture::orderBy('created_at', 'desc')->first();
        
        if ($lastFacture && preg_match('/FAC-(\d{4})-(\d+)/', $lastFacture->reference, $matches)) {
            $year = $matches[1];
            $number = (int)$matches[2];
            
            if ($year == date('Y')) {
                return 'FAC-' . date('Y') . '-' . str_pad($number + 1, 4, '0', STR_PAD_LEFT);
            }
        }
        
        return 'FAC-' . date('Y') . '-0001';
    }

    /**
     * Enregistre une facture à partir d'une proforma.
     */
    public function store(Request $request)
    {
        // ✅ Étape 1 : Valider la requête
        $validated = $request->validate([
            'proforma_id' => 'required|exists:proformas,id',
            'date' => 'required|date',
        ]);

        // ✅ Étape 2 : Récupérer la proforma
        $proforma = Proforma::with('articles')->findOrFail($validated['proforma_id']);

        // ✅ Étape 3 : Vérification des droits
        if (!auth()->user()->isAdmin() && $proforma->filiale_id !== auth()->user()->filiale_id) {
            abort(403, 'Accès non autorisé à cette proforma.');
        }

        // ✅ Étape 4 : Calculer les montants avec remise
        $montantHT = $proforma->articles->sum(fn($article) => $article->quantity * $article->unit_price);
        $remise = $proforma->remise ?? 0;
        $montantHTApresRemise = $montantHT * (1 - $remise / 100);
        $montantTVA = $montantHTApresRemise * ($proforma->tva_rate / 100);
        $montantTTC = $montantHTApresRemise + $montantTVA;

        // ✅ Étape 5 : Création de la facture avec les nouveaux champs
        $facture = Facture::create([
            'client_id'   => $proforma->client_id,
            'proforma_id' => $proforma->id,
            'reference'   => 'FAC-' . strtoupper(uniqid()),
            'date'        => $validated['date'],
            'amount'      => $montantTTC, // Utiliser le montant TTC calculé avec remise
            'tva_rate'    => $proforma->tva_rate,
            'description' => $proforma->description,
            'remise'      => $proforma->remise,
            'user_id'     => auth()->id(),
            'filiale_id'  => $proforma->filiale_id,
        ]);

        // ✅ Étape 6 : Copier les articles
        foreach ($proforma->articles as $article) {
            FactureArticle::create([
                'facture_id'  => $facture->id,
                'designation' => $article->designation,
                'quantity'    => $article->quantity,
                'unit_price'  => $article->unit_price,
                'total'       => $article->quantity * $article->unit_price,
            ]);
        }

        // ✅ Étape 7 : Mettre à jour le statut de la proforma
        $proforma->status = 'validée';
        $proforma->save();

        // ✅ Étape 8 : Log de l'action
        ActionLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'Création facture',
            'facture_id'  => $facture->id,
            'description' => 'Facture créée depuis la proforma ' . $proforma->reference,
        ]);

        // 🔥 Événement de mise à jour en temps réel
        event(new \App\Events\NouvelleFactureCree($facture, $proforma->filiale_id));

        // ✅ Étape 9 : Redirection
        return redirect()->route('factures.index')
                        ->with('success', 'Facture créée avec succès depuis la proforma.');
    }


    public function changerStatut(Facture $facture, $status)
    {
        if (!auth()->user()->isAdmin() && $facture->filiale_id !== auth()->user()->filiale_id) {
            abort(403, 'Accès non autorisé');
        }

        $statutsAutorises = ['brouillon', 'envoyée', 'payée', 'annulée'];

        if (!in_array($status, $statutsAutorises)) {
            return back()->with('error', 'Statut invalide.');
        }

        $facture->update(['status' => $status]);

        return back()->with('success', "Statut de la facture mis à jour en « $status ».");
    }

}
