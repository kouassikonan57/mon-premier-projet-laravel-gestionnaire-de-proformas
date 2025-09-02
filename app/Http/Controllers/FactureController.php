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
use App\Models\Paiement;
use Illuminate\Support\Facades\Storage;


class FactureController extends Controller
{
    public function exportExcel(Facture $facture)
    {
        return Excel::download(new FactureExport($facture), 'facture_'.$facture->reference.'.xlsx');
    }

    public function exportPdf(Facture $facture)
    {
        // Charger les paiements avec la facture
        $facture->load(['client', 'articles', 'paiements', 'filiale']);

        // Déterminer le bon chemin du cachet selon la filiale
        $filialeCode = $facture->filiale->code ?? 'default';
        $cachetFilename = '';
        
        switch($filialeCode) {
            case 'DDCS-001':
                $cachetFilename = 'DDCS-001.png';
                break;
            case 'YADI-002':
                $cachetFilename = 'YADI-002.png';
                break;
            case 'YDIA_CONSTRUCTION-003':
                $cachetFilename = 'YDIA_CONSTRUCTION-003.png';
                break;
            case 'VROOM-004':
                $cachetFilename = 'VROOM-004.png';
                break;
            default:
                $cachetFilename = 'default.png';
        }
        
        $cachetPath = public_path("cachets/{$cachetFilename}");

        // Vérifier si le fichier existe, sinon utiliser le cachet par défaut
        if (!file_exists($cachetPath)) {
            $cachetPath = public_path("cachets/default.png");
            
            // Si le cachet par défaut n'existe pas non plus, on utilise une image vide
            if (!file_exists($cachetPath)) {
                $cachetPath = null;
            }
        }

        // Génération du PDF avec la vue
        $pdf = Pdf::loadView('factures.pdf', [
            'facture' => $facture,
            'cachetPath' => $cachetPath,
            'pourcentageTotalPaye' => $facture->acompte_pourcentage,
            'pourcentageAcompteInitial' => $facture->paiements()->orderBy('date_paiement')->first()?->pourcentage ?? 0,
        ]);



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

        // ⚠️ CORRECTION : Charger les paiements avec la facture
        $facture->load(['client', 'articles', 'paiements']);
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

    // Ajoutez cette méthode pour enregistrer les paiements
    public function enregistrerPaiement(Request $request, Facture $facture)
    {
        // Vérification des droits
        if (!auth()->user()->isAdmin() && $facture->filiale_id !== auth()->user()->filiale_id) {
            abort(403, 'Accès non autorisé');
        }

        // Calculer le montant TTC pour les pourcentages - CORRECTION
        $montantHT = $facture->amount;
        $remise = $facture->remise ?? 0;
        $montantHTApresRemise = $montantHT * (1 - $remise / 100);
        $montantTVA = $montantHTApresRemise * ($facture->tva_rate / 100);
        $montantTTC = $montantHTApresRemise + $montantTVA;

        // Éviter la division par zéro
        if ($montantTTC <= 0) {
            return back()->with('error', 'Erreur de calcul : le montant TTC ne peut pas être zéro.');
        }

        $validated = $request->validate([
            'pourcentage_paiement' => 'required|numeric|min:0.01|max:100',
            'montant' => 'required|numeric|min:0.01|max:' . $facture->reste_a_payer,
            'date_paiement' => 'required|date',
            'mode_paiement' => 'required|in:espèce,virement,chèque,carte',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        // Calculer le pourcentage réel par rapport au total TTC
        $pourcentageReel = ($validated['montant'] / $montantTTC) * 100;

        // Vérifier la cohérence avec le pourcentage saisi (tolérance de 2%)
        $pourcentageSaisi = $validated['pourcentage_paiement'];
        $ecart = abs($pourcentageReel - $pourcentageSaisi);

        if ($ecart > 2) {
            return back()->with('error', 'Incohérence détectée. Le pourcentage calculé ('.number_format($pourcentageReel, 2).'%) ne correspond pas au pourcentage saisi ('.number_format($pourcentageSaisi, 2).'%). Veuillez vérifier le montant.');
        }
        
        // Mettre à jour le pourcentage total payé
        $nouveauPourcentageTotal = $facture->acompte_pourcentage + $pourcentageReel;
        $facture->acompte_pourcentage = min($nouveauPourcentageTotal, 100);
        
        // Créer le paiement
        $paiement = Paiement::create([
            'facture_id' => $facture->id,
            'montant' => $validated['montant'],
            'date_paiement' => $validated['date_paiement'],
            'mode_paiement' => $validated['mode_paiement'],
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'pourcentage' => $pourcentageReel
        ]);

        // Déterminer le chemin du cachet
        $filialeCode = $facture->filiale->code ?? 'default';
        $cachetFilename = '';
        
        switch($filialeCode) {
            case 'DDCS-001':
                $cachetFilename = 'DDCS-001.png';
                break;
            case 'YADI-002':
                $cachetFilename = 'YADI-002.png';
                break;
            case 'YDIA_CONSTRUCTION-003':
                $cachetFilename = 'YDIA_CONSTRUCTION-003.png';
                break;
            case 'VROOM-004':
                $cachetFilename = 'VROOM-004.png';
                break;
            default:
                $cachetFilename = 'default.png';
        }
        
        $cachetPath = public_path("cachets/{$cachetFilename}");

        // Vérifier si le fichier existe, sinon utiliser le cachet par défaut
        if (!file_exists($cachetPath)) {
            $cachetPath = public_path("cachets/default.png");
            
            if (!file_exists($cachetPath)) {
                $cachetPath = null;
            }
        }

        // Générer et sauvegarder le PDF
        $pdf = Pdf::loadView('factures.pdf', [
            'facture' => $facture,
            'cachetPath' => $cachetPath,
            'pourcentageTotalPaye' => $facture->acompte_pourcentage,
            'pourcentageAcompteInitial' => $facture->paiements()->orderBy('date_paiement')->first()?->pourcentage ?? 0,
        ]);


        $pdfPath = 'paiements/facture_' . $facture->reference . '_paiement_' . $paiement->id . '.pdf';
        Storage::put($pdfPath, $pdf->output());

        // Sauvegarder le chemin du PDF
        $paiement->update(['pdf_path' => $pdfPath]);

        // Mettre à jour les totaux de la facture
        $facture->montant_paye += $validated['montant'];
        $facture->reste_a_payer -= $validated['montant'];
        
        // Mettre à jour le statut si complètement payé
        if ($facture->reste_a_payer <= 0) {
            $facture->status = 'payée';
            $facture->acompte_pourcentage = 100;
        }
        
        $facture->save();

        // Log de l'action
        ActionLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'Paiement enregistré',
            'facture_id'  => $facture->id,
            'description' => 'Paiement de ' . number_format($pourcentageReel, 2) . '% (' . number_format($validated['montant'], 2, ',', ' ') . ' F CFA) enregistré',
        ]);

        return back()->with('success', 'Paiement de ' . number_format($pourcentageReel, 2) . '% enregistré avec succès.');
    }

    public function telechargerPaiement(Facture $facture, $paiement_id)
    {
        // Charger le paiement spécifique
        $paiement = Paiement::findOrFail($paiement_id);
        
        // Recréer l'état de la facture à ce moment-là
        $facture->load(['client', 'articles', 'filiale']);
        
        // Pour un vrai système d'archivage, vous devriez sauvegarder chaque PDF
        // Pour l'instant, on ne peut générer que l'état actuel
        
        return $this->exportPdf($facture);
    }

    public function telechargerPaiementPdf(Facture $facture, $paiement)
    {
        // Trouver le paiement spécifique
        $paiement = Paiement::where('facture_id', $facture->id)->findOrFail($paiement);
        
        // Charger les données nécessaires
        $facture->load(['client', 'articles', 'filiale']);
        
        // Charger tous les paiements jusqu'à ce paiement
        $paiementsJusquici = Paiement::where('facture_id', $facture->id)
                                    ->where('id', '<=', $paiement->id)
                                    ->get();
        
        // Calculer le montant total payé jusqu'à ce paiement
        $montantPayeJusquici = $paiementsJusquici->sum('montant');
        
        // Passer les données à la vue
        $data = [
            'facture' => $facture,
            'paiement' => $paiement,
            'paiements' => $paiementsJusquici,
            'montantPayeJusquici' => $montantPayeJusquici
        ];
        
        $pdf = Pdf::loadView('factures.pdf_archive', $data);
        
        return $pdf->download('facture_' . $facture->reference . '_paiement_' . $paiement->id . '.pdf');
    }

    /**
     * Enregistre une facture à partir d'une proforma.
     */
    public function store(Request $request)
    {
        // ✅ Étape 1 : Valider la requête avec les nouveaux champs
        $validated = $request->validate([
            'proforma_id' => 'required|exists:proformas,id',
            'date' => 'required|date',
            'reference' => 'required|string|max:255|unique:factures,reference',
            'bon_commande' => 'nullable|string|max:255',
            'acompte_pourcentage' => 'nullable|numeric|min:0|max:100',
            'acompte_montant' => 'nullable|numeric|min:0',
            'montant_a_payer' => 'nullable|numeric|min:0',
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

        // Calculer les valeurs d'acompte si fournies
        $acomptePourcentage = $request->acompte_pourcentage ?? 0;
        $acompteMontant = $request->acompte_montant ?? 0;
        $montantAPayer = $request->montant_a_payer ?? $montantTTC;

        // Si un pourcentage d'acompte est fourni mais pas le montant, calculer le montant
        if ($acomptePourcentage > 0 && $acompteMontant == 0) {
            $acompteMontant = ($montantTTC * $acomptePourcentage) / 100;
            $montantAPayer = $montantTTC - $acompteMontant;
        }

        // ✅ Étape 5 : Création de la facture avec les nouveaux champs
        $facture = Facture::create([
            'client_id'          => $proforma->client_id,
            'proforma_id'        => $proforma->id,
            'reference'          => $validated['reference'],
            'date'               => $validated['date'],
            'amount'             => $montantHT,
            'tva_rate'           => $proforma->tva_rate,
            'description'        => $proforma->description,
            'remise'             => $proforma->remise,
            'user_id'            => auth()->id(),
            'filiale_id'         => $proforma->filiale_id,
            'bon_commande'       => $validated['bon_commande'] ?? null,
            'acompte_pourcentage'=> $acomptePourcentage,
            'acompte_montant'    => $acompteMontant,
            'montant_a_payer'    => $montantAPayer,
            'montant_paye'       => $acompteMontant, // Nouveau champ
            'reste_a_payer'      => $montantAPayer,  // Nouveau champ
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

        // ✅ Étape 7 : Si acompte, créer un paiement
        if ($acompteMontant > 0) {
            Paiement::create([
                'facture_id' => $facture->id,
                'montant' => $acompteMontant,
                'date_paiement' => now(),
                'mode_paiement' => 'acompte',
                'notes' => 'Acompte initial de ' . $acomptePourcentage . '%'
            ]);
        }

        // ✅ Étape 8 : Mettre à jour le statut de la proforma
        $proforma->status = 'validée';
        $proforma->save();

        // ✅ Étape 9 : Log de l'action
        ActionLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'Création facture',
            'facture_id'  => $facture->id,
            'description' => 'Facture créée depuis la proforma ' . $proforma->reference,
        ]);

        // 🔥 Événement de mise à jour en temps réel
        event(new \App\Events\NouvelleFactureCree($facture, $proforma->filiale_id));

        // ✅ Étape 10 : Redirection
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
