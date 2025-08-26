@extends('layouts.app')

@section('content')
    <h3>✏️ Modifier un article</h3>

    <form action="{{ route('catalog-articles.update', $catalogArticle) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Désignation *</label>
            <input type="text" name="name" id="name" class="form-control" required value="{{ old('name', $catalogArticle->name) }}">
        </div>

        <div class="mb-3">
            <label for="default_price" class="form-label">Prix unitaire par défaut (FCFA)</label>
            <input type="number" name="default_price" id="default_price" class="form-control" step="0.01" value="{{ old('default_price', $catalogArticle->default_price) }}">
        </div>

        <button type="submit" class="btn btn-success">
            Mettre à jour
        </button>
        <a href="{{ route('catalog-articles.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
@endsection
