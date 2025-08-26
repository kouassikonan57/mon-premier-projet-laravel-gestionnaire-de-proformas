@extends('layouts.app')

@section('content')
    <h3>{{ isset($catalogArticle) ? '✏️ Modifier un article' : '➕ Ajouter un article au catalogue' }}</h3>

    <form action="{{ isset($catalogArticle) ? route('catalog-articles.update', $catalogArticle) : route('catalog-articles.store') }}" method="POST">
        @csrf
        @if(isset($catalogArticle))
            @method('PUT')
        @endif

        @if(auth()->user()->isAdmin())
            <div class="mb-3">
                <label for="filiale_id" class="form-label">Filiale</label>
                <select name="filiale_id" class="form-control" required>
                    @foreach($filiales as $filiale)
                        <option value="{{ $filiale->id }}">{{ $filiale->nom }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="mb-3">
            <label for="name" class="form-label">Désignation *</label>
            <input type="text" name="name" id="name" class="form-control" required
                   value="{{ old('name', $catalogArticle->name ?? '') }}">
        </div>

        <div class="mb-3">
            <label for="default_price" class="form-label">Prix unitaire par défaut (FCFA)</label>
            <input type="number" name="default_price" id="default_price" class="form-control" step="0.01"
                   value="{{ old('default_price', $catalogArticle->default_price ?? '') }}">
        </div>

        <button type="submit" class="btn btn-success">
            {{ isset($catalogArticle) ? 'Mettre à jour' : 'Créer' }}
        </button>
        <a href="{{ route('catalog-articles.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
@endsection
