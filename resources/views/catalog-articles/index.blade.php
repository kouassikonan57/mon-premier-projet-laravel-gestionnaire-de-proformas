@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Catalogue des articles</h1>

    <!-- Formulaire de recherche -->
    <form method="GET" action="{{ route('catalog-articles.index') }}" class="mb-4">
        <div class="row g-2 align-items-center">
            <div class="col-md-3 col-12">
                <input type="text" name="search" class="form-control" placeholder="Rechercher un article..." value="{{ request('search') }}">
            </div>

            @if(auth()->user()->isAdmin())
                <div class="col-md-3 col-12">
                    <select name="filiale_id" class="form-select">
                        <option value="">— Toutes les filiales —</option>
                        @foreach($filiales as $filiale)
                            <option value="{{ $filiale->id }}" {{ request('filiale_id') == $filiale->id ? 'selected' : '' }}>
                                {{ $filiale->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Rechercher
                </button>
            </div>
        </div>
    </form>

    <!-- Bouton d'ajout -->
    <a href="{{ route('catalog-articles.create') }}" class="btn btn-primary mb-4">
        <i class="fas fa-plus"></i> Ajouter un article
    </a>

    <!-- Messages -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Tableau -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Désignation</th>
                <th>Prix unitaire par défaut</th>
                @if(auth()->user()->isAdmin())
                    <th>Filiale</th>
                @endif
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($catalogArticles as $article)
                <tr>
                    <td>{{ $article->name }}</td>
                    <td>{{ number_format($article->default_price, 2, ',', ' ') }} FCFA</td>
                    @if(auth()->user()->isAdmin())
                        <td>{{ $article->filiale->nom ?? '—' }}</td>
                    @endif
                    <td>
                        <a href="{{ route('catalog-articles.edit', $article) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <form action="{{ route('catalog-articles.destroy', $article) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet article ?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ auth()->user()->isAdmin() ? 4 : 3 }}" class="text-center">Aucun article enregistré.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $catalogArticles->appends(request()->query())->links() }}
    </div>
</div>
@endsection
