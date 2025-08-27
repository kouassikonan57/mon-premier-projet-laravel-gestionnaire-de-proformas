<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use App\Models\Filiale;
use Illuminate\Validation\Rule;
use App\Events\NouveauClientCree;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Client::with('filiale')->forCurrentFiliale();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
            });
        }

        $user = auth()->user();

        if ($user->isAdmin() && $request->filled('filiale_id')) {
            $query->where('filiale_id', $request->filiale_id);
        }

        $clients = $query->orderBy('name')->paginate(10);
        $filiales = $user->isAdmin() ? \App\Models\Filiale::all() : collect();

        return view('clients.index', compact('clients', 'filiales'));
    }


    public function create()
    {
        $user = auth()->user();
        $isAdmin = $user->role === 'admin' || (method_exists($user, 'isAdmin') && $user->isAdmin());

        $filiales = $isAdmin
            ? Filiale::all()
            : Filiale::where('id', $user->filiale_id)->get();
        return view('clients.create', compact('filiales'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->role === 'admin' || (method_exists($user, 'isAdmin') && $user->isAdmin());

        // Validation de base
        $validated = $request->validate([
            'filiale_id' => 'required|exists:filiales,id',
            'name' => 'required|string|max:255',
            'responsable' => 'nullable|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'rccm' => 'nullable|string|max:255',
        ]);

        // Vérification de sécurité : empêcher les non-admins de changer la filiale
        if (!$isAdmin && $user->filiale_id != $validated['filiale_id']) {
            abort(403, 'Action non autorisée : vous ne pouvez pas créer un client dans une autre filiale.');
        }

        // Création du client
        $client = Client::create($validated);

        // 🔥 Événement de mise à jour en temps réel
        event(new \App\Events\NouveauClientCree($client, $validated['filiale_id']));

        return redirect()->route('clients.index')->with('success', 'Client créé avec succès.');
    }

    public function show(Client $client)
    {
        // Vérification que l'utilisateur a le droit de voir ce client
        if (!auth()->user()->isAdmin() && $client->filiale_id !== auth()->user()->filiale_id) {
            abort(403, 'Accès non autorisé à ce client');
        }

        // Chargement des relations en une seule requête
        $client->load(['proformas', 'factures']);

        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        // Vérification des permissions
        if (!auth()->user()->isAdmin() && $client->filiale_id !== auth()->user()->filiale_id) {
            abort(403, 'Accès non autorisé');
        }

        $filiales = auth()->user()->isAdmin() 
            ? Filiale::all() 
            : Filiale::where('id', auth()->user()->filiale_id)->get();

        return view('clients.edit', compact('client', 'filiales'));
    }

    public function update(Request $request, Client $client)
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        // Vérification des permissions : un non-admin ne peut modifier qu’un client de sa filiale
        if (!$isAdmin && $client->filiale_id !== $user->filiale_id) {
            abort(403, 'Vous ne pouvez modifier que les clients de votre filiale.');
        }

        // Validation des données
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'responsable' => 'nullable|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('clients')->ignore($client->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'rccm' => 'nullable|string|max:255',
            'filiale_id' => $isAdmin ? 'required|exists:filiales,id' : 'prohibited',
        ]);

        // Forcer la filiale pour les non-admins, même si filiale_id est "prohibited"
        if (!$isAdmin) {
            $validated['filiale_id'] = $user->filiale_id;
        }

        $client->update($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client mis à jour avec succès');
    }


    public function destroy(Client $client)
    {
        // Vérification des permissions
        if (!auth()->user()->isAdmin() && $client->filiale_id !== auth()->user()->filiale_id) {
            abort(403, 'Action non autorisée');
        }

        // Vérification qu'aucune proforma/facture n'est liée
        if ($client->proformas()->exists() || $client->factures()->exists()) {
            return back()->with('error', 'Impossible de supprimer : client associé à des documents');
        }

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client supprimé avec succès');
    }
    
}
