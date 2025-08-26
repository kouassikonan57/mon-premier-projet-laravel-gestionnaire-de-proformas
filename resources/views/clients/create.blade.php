@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Ajouter un client</h1>

    <!-- Affichage des erreurs de validation -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulaire d'ajout -->
    <form action="{{ route('clients.store') }}" method="POST">
        @csrf

        @php
            $user = auth()->user();
            $isAdmin = $user->role === 'admin' || (method_exists($user, 'isAdmin') && $user->isAdmin());
        @endphp

        <div class="form-group">
            <label for="filiale_id">Filiale *</label>
            
            @if($isAdmin)
                <select name="filiale_id" id="filiale_id" class="form-control" required>
                    <option value="">Sélectionnez une filiale</option>
                    @foreach($filiales as $filiale)
                        <option value="{{ $filiale->id }}" {{ old('filiale_id') == $filiale->id ? 'selected' : '' }}>
                            {{ $filiale->nom }}
                        </option>
                    @endforeach
                </select>
            @else
                <input type="hidden" name="filiale_id" value="{{ $filiales->first()->id }}">
                <input type="text" class="form-control" value="{{ $filiales->first()->nom }}" disabled>
            @endif

            @error('filiale_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Nom :</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="mb-3">
            <label for="responsable" class="form-label">Responsable :</label>
            <input type="text" name="responsable" class="form-control" value="{{ old('responsable') }}">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email :</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Téléphone :</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Adresse :</label>
            <input type="text" name="address" class="form-control" value="{{ old('address') }}">
        </div>

        <div class="mb-3">
            <label for="rccm" class="form-label">Numéro RCCM :</label>
            <input type="text" name="rccm" class="form-control" value="{{ old('rccm') }}">
        </div>

        <button type="submit" class="btn btn-success">💾 Enregistrer</button>
        <a href="{{ route('clients.index') }}" class="btn btn-secondary">↩️ Retour</a>
    </form>
</div>
@endsection
