<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Filiale;

class FilialeController extends AdminController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Filiale::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $filiales = $query->orderBy('nom')->paginate(10); // Pagination

        return view('filiales.index', compact('filiales'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('filiales.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:filiales',
            'description' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'logo_path' => 'nullable|image|max:2048', // max 2Mo
        ]);

        // Traitement du logo
        if ($request->hasFile('logo_path')) {
            $path = $request->file('logo_path')->store('logos', 'public');
            $validated['logo_path'] = 'storage/' . $path;
        }

        Filiale::create($validated);

        return redirect()->route('filiales.index')
            ->with('success', 'Filiale créée avec succès');
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $filiale = \App\Models\Filiale::findOrFail($id);
        return view('filiales.edit', compact('filiale'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $filiale = Filiale::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:filiales,code,' . $filiale->id,
            'description' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'logo_path' => 'nullable|image|max:2048',
        ]);

        // Nouveau logo ?
        if ($request->hasFile('logo_path')) {
            $path = $request->file('logo_path')->store('logos', 'public');
            $validated['logo_path'] = 'storage/' . $path;
        }

        $filiale->update($validated);

        return redirect()->route('filiales.index')->with('success', 'Filiale mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $filiale = \App\Models\Filiale::findOrFail($id);
        $filiale->delete();

        return redirect()->route('filiales.index')->with('success', 'Filiale supprimée avec succès.');
    }


    public function __construct()
    {
        // Vérification manuelle pour toutes les méthodes sauf index
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->isAdmin()) {
                abort(403, 'Accès non autorisé');
            }
            return $next($request);
        })->except(['index']);
    }
}
