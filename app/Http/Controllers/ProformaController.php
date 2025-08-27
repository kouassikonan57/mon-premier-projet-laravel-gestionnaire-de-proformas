<?php

namespace App\Http\Controllers;

use App\Models\Proforma;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Models\Facture;
use App\Models\FactureArticle;
use App\Models\ActionLog;
use App\Models\ActivityLog; 
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\CatalogArticle;
use App\Exports\ProformaExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Filiale;
use App\Models\Article;
use Illuminate\Validation\Rule;
use App\Events\NouvelleProformaCree;



class ProformaController extends Controller
{
    public function exportExcel(Proforma $proforma)
    {
        $reference = str_replace(['/', '\\'], '_', $proforma->reference);
        return Excel::download(new ProformaExport($proforma), 'proforma_'.$reference.'.xlsx');
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $filialeId = $request->input('filiale_id');

        // Retirer le scope global temporairement si admin pour filtrer dynamiquement
        $query = Proforma::withoutGlobalScope('filiale')
            ->with(['client', 'articles', 'filiale']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%$search%")
                ->orWhereHas('client', fn($q) => $q->where('name', 'like', "%$search%"));
            });
        }

        $user = auth()->user();

        if ($user->isAdmin()) {
            // Si admin : filtrer selon choix
            if ($filialeId) {
                $query->where('filiale_id', $filialeId);
            }
        } else {
            // Si non-admin : restreindre à sa filiale
            $query->where('filiale_id', $user->filiale_id);
        }

        $proformas = $query->latest()->paginate(10);

        $filiales = $user->isAdmin() ? \App\Models\Filiale::all() : collect();

        return view('proformas.index', compact('proformas', 'filiales'));
    }
    
    public function create()
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        // Filiales accessibles
        $filiales = $isAdmin
            ? Filiale::all()
            : Filiale::where('id', $user->filiale_id)->get();

        // Clients associés à la filiale
        $clients = $isAdmin
            ? Client::all()
            : Client::where('filiale_id', $user->filiale_id)->get();

        $articles = Article::where('filiale_id', $user->filiale_id)->get();
        $catalogArticles = CatalogArticle::where('filiale_id', auth()->user()->filiale_id)->get();

        return view('proformas.create', compact('filiales', 'clients', 'articles', 'catalogArticles'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        $rules = [
            'client_id' => [
                'required',
                Rule::exists('clients', 'id')->where(function ($query) use ($user, $isAdmin) {
                    if (!$isAdmin) {
                        $query->where('filiale_id', $user->filiale_id);
                    }
                })
            ],
            'reference' => 'required|unique:proformas,reference',
            'date' => 'required|date',
            'tva_rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',        // Ajouté ici
            'remise' => 'nullable|numeric|min:0|max:100', // Ajouté ici (en pourcentage, supposé)
            'articles' => 'required|array|min:1',
            'articles.*.designation' => 'required|string',
            'articles.*.quantity' => 'required|integer|min:1',
            'articles.*.unit_price' => 'required|numeric|min:0',
        ];

        // Filiale_id seulement pour admin
        if ($isAdmin) {
            $rules['filiale_id'] = 'required|exists:filiales,id';
        }

        $validated = $request->validate($rules);

        // Pour les non-admins, forcer la filiale liée à l'utilisateur
        if (!$isAdmin) {
            $validated['filiale_id'] = $user->filiale_id;
        }

        // 🔒 Sécurité supplémentaire
        $client = Client::findOrFail($validated['client_id']);
        if ($client->filiale_id != $validated['filiale_id']) {
            return back()->withErrors(['client_id' => 'Le client ne fait pas partie de cette filiale.']);
        }

        // 💰 Calcul des montants
        $montantHT = collect($validated['articles'])->reduce(fn($carry, $a) => $carry + ($a['quantity'] * $a['unit_price']), 0);

        // Appliquer la remise si elle existe
        $remise = $validated['remise'] ?? 0;
        $montantHTApresRemise = $montantHT * (1 - $remise / 100);

        $montantTVA = $montantHTApresRemise * ($validated['tva_rate'] / 100);
        $montantTTC = $montantHTApresRemise + $montantTVA;

        $proforma = Proforma::create([
            'client_id'   => $validated['client_id'],
            'filiale_id'  => $validated['filiale_id'],
            'reference'   => $validated['reference'],
            'date'        => $validated['date'],
            'description' => $validated['description'] ?? null, // Ajouté ici
            'remise'      => $remise,                           // Ajouté ici
            'amount'      => $montantTTC,
            'tva_rate'    => $validated['tva_rate'],
            'user_id'     => $user->id,
        ]);

        foreach ($validated['articles'] as $article) {
            $proforma->articles()->create([
                'designation' => $article['designation'],
                'quantity'    => $article['quantity'],
                'unit_price'  => $article['unit_price'],
                'total'       => $article['quantity'] * $article['unit_price'],
            ]);
        }

        ActionLog::create([
            'user_id' => $user->id,
            'action' => 'Création proforma',
            'proforma_id' => $proforma->id,
            'description' => 'Proforma créée avec référence ' . $proforma->reference,
            'filiale_id' => $validated['filiale_id'],
        ]);

        ActivityLog::create([
            'action' => 'Création',
            'entity_type' => 'Proforma',
            'entity_id' => $proforma->id,
            'description' => "Proforma {$proforma->reference} créée.",
            'user_id' => $user->id,
            'filiale_id' => $validated['filiale_id'],
        ]);

        // 🔥 Événement de mise à jour en temps réel
        event(new \App\Events\NouvelleProformaCree($proforma, $validated['filiale_id']));

        return redirect()->route('proformas.index')->with('success', 'Proforma créée avec ses articles et montant TTC.');
    }

    public function show(Proforma $proforma)
    {
        if (!auth()->user()->isAdmin() && $proforma->filiale_id !== auth()->user()->filiale_id) {
            abort(403, 'Accès non autorisé à cette proforma.');
        }

        $proforma->load(['client', 'articles']);
        return view('proformas.show', compact('proforma'));
    }

    public function edit(Proforma $proforma)
    {
        if (!auth()->user()->isAdmin() && $proforma->filiale_id !== auth()->user()->filiale_id) {
            abort(403, 'Accès non autorisé à cette proforma.');
        }

        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        // Filiales et clients disponibles selon rôle
        $filiales = $isAdmin
            ? Filiale::all()
            : Filiale::where('id', $user->filiale_id)->get();

        $clients = $isAdmin
            ? Client::all()
            : Client::where('filiale_id', $user->filiale_id)->get();

        $catalogArticles = CatalogArticle::all();

        return view('proformas.edit', compact('proforma', 'filiales', 'clients', 'catalogArticles'));
    }


    public function update(Request $request, Proforma $proforma)
    {
        if (!auth()->user()->isAdmin() && $proforma->filiale_id !== auth()->user()->filiale_id) {
            abort(403, 'Accès non autorisé à cette proforma.');
        }

        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        $rules = [
            'client_id' => [
                'required',
                Rule::exists('clients', 'id')->where(function ($query) use ($user, $isAdmin) {
                    if (!$isAdmin) {
                        $query->where('filiale_id', $user->filiale_id);
                    }
                })
            ],
            'reference' => ['required', Rule::unique('proformas', 'reference')->ignore($proforma->id)],
            'date' => 'required|date',
            'tva_rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',      // Ajouté
            'remise' => 'nullable|numeric|min:0|max:100', // Ajouté
            'articles' => 'required|array|min:1',
            'articles.*.designation' => 'required|string',
            'articles.*.quantity' => 'required|integer|min:1',
            'articles.*.unit_price' => 'required|numeric|min:0',
        ];

        $validated = $request->validate($rules);

        // Sécurité : client lié à la bonne filiale
        $client = Client::findOrFail($validated['client_id']);
        if (!$isAdmin && $client->filiale_id != $user->filiale_id) {
            return back()->withErrors(['client_id' => 'Le client ne fait pas partie de votre filiale.']);
        }

        // Calcul des montants avec prise en compte de la remise
        $montantHT = collect($validated['articles'])->reduce(fn($carry, $a) => $carry + ($a['quantity'] * $a['unit_price']), 0);
        $remise = $validated['remise'] ?? 0;
        $montantHTApresRemise = $montantHT * (1 - $remise / 100);
        $montantTVA = $montantHTApresRemise * ($validated['tva_rate'] / 100);
        $montantTTC = $montantHTApresRemise + $montantTVA;

        $proforma->update([
            'client_id' => $validated['client_id'],
            'reference' => $validated['reference'],
            'date' => $validated['date'],
            'tva_rate' => $validated['tva_rate'],
            'description' => $validated['description'] ?? null,  // Mise à jour description
            'remise' => $remise,                                // Mise à jour remise
            'amount' => $montantTTC,
        ]);

        $proforma->articles()->delete();
        foreach ($validated['articles'] as $article) {
            $proforma->articles()->create([
                'designation' => $article['designation'],
                'quantity' => $article['quantity'],
                'unit_price' => $article['unit_price'],
                'total' => $article['quantity'] * $article['unit_price'],
            ]);
        }

        ActionLog::create([
            'user_id' => $user->id,
            'action' => 'Mise à jour proforma',
            'proforma_id' => $proforma->id,
            'description' => 'Proforma mise à jour avec référence ' . $proforma->reference,
            'filiale_id' => $user->filiale_id,
        ]);

        ActivityLog::create([
            'action' => 'Mise à jour',
            'entity_type' => 'Proforma',
            'entity_id' => $proforma->id,
            'description' => "Proforma {$proforma->reference} mise à jour.",
            'user_id' => $user->id,
            'filiale_id' => $user->filiale_id,
        ]);

        return redirect()->route('proformas.index')->with('success', 'Proforma mise à jour avec succès.');
    }


    public function destroy(Proforma $proforma)
    {
        if (!auth()->user()->isAdmin() && $proforma->filiale_id !== auth()->user()->filiale_id) {
            abort(403, 'Accès non autorisé à cette proforma.');
        }

        $proforma->delete();

        ActionLog::create([
            'user_id' => Auth::id(),
            'action' => 'Création proforma',
            'proforma_id' => $proforma->id,
            'description' => 'Proforma créée avec référence '.$proforma->reference,
            'filiale_id' => Auth::user()->filiale_id,
        ]);

        ActivityLog::create([
            'action' => 'Suppression',
            'entity_type' => 'Proforma',
            'entity_id' => $proforma->id,
            'description' => "Proforma {$proforma->reference} supprimée.",
            'filiale_id' => Auth::user()->filiale_id,
        ]);

        return redirect()->route('proformas.index')->with('success', 'Proforma supprimée.');
    }

    public function exportPdf(Proforma $proforma)
    {
        // Charger manuellement le client
        $client = Client::where('id', $proforma->client_id)
                        ->where('filiale_id', $proforma->filiale_id)
                        ->first();

        $proforma->load('articles', 'filiale');
        $proforma->setRelation('client', $client);

        // Chemin absolu vers le cachet numérique
        $cachetPath = public_path('images/cachet-numerique.png');

        // Vérification de l'existence
        if (!file_exists($cachetPath)) {
            $cachetPath = null;
        }

        $reference = str_replace(['/', '\\'], '_', $proforma->reference);
        $pdf = PDF::loadView('proformas.pdf', compact('proforma', 'cachetPath'));

        return $pdf->download('proforma_' . $reference . '.pdf');
    }

    public function convert(Proforma $proforma)
    {
        if (!auth()->user()->isAdmin() && $proforma->filiale_id !== auth()->user()->filiale_id) {
            abort(403, 'Vous ne pouvez pas convertir une proforma d’une autre filiale.');
        }
        // 🔁 Changer le statut AVANT de créer la facture
        $proforma->update(['status' => 'validée']); // ✅ C’EST ICI !
        
        $factureReference = 'F-' . $proforma->reference;

        // ✅ Vérifier si une facture existe déjà avec cette référence
        $existingFacture = Facture::where('reference', $factureReference)->first();

        if ($existingFacture) {
            return redirect()->route('factures.show', $existingFacture)
                            ->with('info', 'Cette proforma a déjà été convertie en facture.');
        }

        // ✅ Création de la nouvelle facture
        $facture = Facture::create([
            'client_id' => $proforma->client_id,
            'reference' => $factureReference,
            'date' => now(),
            'amount' => $proforma->amount,
            'tva_rate' => $proforma->tva_rate ?? 18,
        ]);

        // ✅ Copier les articles de la proforma vers la facture
        foreach ($proforma->articles as $article) {
            \App\Models\Article::create([
                'facture_id' => $facture->id,
                'designation' => $article->designation,
                'quantity' => $article->quantity,
                'unit_price' => $article->unit_price,
                'total' => $article->total ?? ($article->quantity * $article->unit_price),
                'proforma_id' => $article->proforma_id,
            ]);
        }

        // ✅ Journalisation de l’action
        ActionLog::create([
            'user_id' => Auth::id(),
            'action' => 'Conversion proforma',
            'proforma_id' => $proforma->id,
            'description' => 'Proforma convertie en facture avec référence ' . $facture->reference,
            'filiale_id' => Auth::user()->filiale_id,
        ]);

        ActivityLog::create([
            'action' => 'Conversion',
            'entity_type' => 'Proforma',
            'entity_id' => $proforma->id,
            'description' => "Proforma {$proforma->reference} convertie en Facture {$facture->reference}",
            'filiale_id' => Auth::user()->filiale_id,
        ]);

        return redirect()->route('factures.show', $facture)
                        ->with('success', 'Facture générée depuis la proforma.');
    }

}
