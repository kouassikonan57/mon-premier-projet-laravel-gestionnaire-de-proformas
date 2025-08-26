@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Modifier la filiale</h2>

    <form action="{{ route('filiales.update', $filiale->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="nom" class="form-control" value="{{ old('nom', $filiale->nom) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Code</label>
            <input type="text" name="code" class="form-control" value="{{ old('code', $filiale->code) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Logo actuel</label><br>
            @if($filiale->logo_path)
                <img src="{{ asset($filiale->logo_path) }}" alt="Logo" style="max-height: 80px;" class="mb-2">
            @else
                <span class="text-muted">Aucun logo</span>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label">Nouveau logo (facultatif)</label>
            <input type="file" name="logo_path" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Texte du pied de page (PDF)</label>
            <textarea name="footer_text" class="form-control" rows="4">{{ old('footer_text', $filiale->footer_text) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control">{{ old('description', $filiale->description) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Mettre à jour</button>
        <a href="{{ route('filiales.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
