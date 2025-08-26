@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Créer une nouvelle filiale</h2>
    <form action="{{ route('filiales.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Nom de la filiale</label>
            <input type="text" name="nom" class="form-control" value="{{ old('nom') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Code</label>
            <input type="text" name="code" class="form-control" value="{{ old('code') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Logo (fichier image)</label>
            <input type="file" name="logo_path" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Texte du pied de page (PDF)</label>
            <textarea name="footer_text" class="form-control" rows="4">{{ old('footer_text') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control">{{ old('description') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Créer</button>
        <a href="{{ route('filiales.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
