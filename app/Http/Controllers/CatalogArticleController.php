<?php

namespace App\Http\Controllers;

use App\Models\CatalogArticle;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Filiale;
use App\Events\NouvelArticleCree;

class CatalogArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = CatalogArticle::with('filiale')->forCurrentFiliale();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $user = auth()->user();

        // Filtrage par filiale uniquement pour les admins
        if ($user->isAdmin()) {
            if ($request->filled('filiale_id')) {
                $query->where('filiale_id', $request->filiale_id);
            }
        }

        $catalogArticles = $query->paginate(10);
        $filiales = $user->isAdmin() ? \App\Models\Filiale::all() : collect();

        return view('catalog-articles.index', compact('catalogArticles', 'filiales'));
    }

    public function create()
    {
        return view('catalog-articles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'default_price' => 'nullable|numeric|min:0',
        ]);

        $article = CatalogArticle::create([ // ← Ajoutez $article = 
            'name' => $validated['name'],
            'default_price' => $validated['default_price'],
            'filiale_id' => auth()->user()->filiale_id, 
        ]);

        // 🔥 Événement de mise à jour en temps réel
        event(new \App\Events\NouvelArticleCree($article, auth()->user()->filiale_id));

        return redirect()->route('catalog-articles.index')->with('success', 'Article ajouté avec succès.');
    }


    public function edit(CatalogArticle $catalogArticle)
    {
        if (!auth()->user()->isAdmin() && $catalogArticle->filiale_id !== auth()->user()->filiale_id) {
            abort(403);
        }

        return view('catalog-articles.edit', compact('catalogArticle'));
    }

    public function update(Request $request, CatalogArticle $catalogArticle)
    {
        if (!auth()->user()->isAdmin() && $catalogArticle->filiale_id !== auth()->user()->filiale_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'default_price' => 'nullable|numeric|min:0',
        ]);

        $catalogArticle->update($validated);

        return redirect()->route('catalog-articles.index')->with('success', 'Article mis à jour avec succès.');
    }

    public function destroy(CatalogArticle $catalogArticle)
    {
        if (!auth()->user()->isAdmin() && $catalogArticle->filiale_id !== auth()->user()->filiale_id) {
            abort(403);
        }

        $catalogArticle->delete();
        return redirect()->route('catalog-articles.index')->with('success', 'Article supprimé.');
    }

}
