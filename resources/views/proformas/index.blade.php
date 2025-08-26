@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Liste des Proformas</h1>

    <!-- Formulaire de recherche et filtre filiale -->
    <form method="GET" action="{{ route('proformas.index') }}" class="mb-4">
        <div class="row g-2 align-items-center">
            <div class="col-md-4 col-8">
                <input type="text" name="search" class="form-control" placeholder="Rechercher une proforma..." value="{{ request('search') }}">
            </div>

            @if(auth()->user()->isAdmin())
                <div class="col-md-3 col-6">
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

    <a href="{{ route('proformas.create') }}" class="btn btn-primary mb-4">
        <i class="fas fa-plus"></i> Ajouter une proforma
    </a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Table visible sur desktop -->
    <div class="d-none d-md-block">
        <table class="table table-bordered align-middle table-hover">
            <thead class="table-light">
                <tr>
                    <th>Référence</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Description</th> <!-- Ajouté -->
                    <th>Remise</th> <!-- Ajouté -->
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Articles</th>
                    @if(auth()->user()->isAdmin())
                        <th>Filiale</th>
                    @endif
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($proformas as $proforma)
                    <tr>
                        <td>{{ $proforma->reference }}</td>
                        <td>{{ optional($proforma->client)->name ?? 'Client inconnu' }}</td>
                        <td>{{ $proforma->date->format('d/m/Y') }}</td>
                        <td>{{ $proforma->description ?? '-' }}</td> <!-- Affichage description -->
                        <td>{{ $proforma->remise ? $proforma->remise . '%' : '-' }}</td> <!-- Affichage remise -->
                        <td>
                            {{ number_format($proforma->amount, 0, ',', ' ') }} F CFA<br>
                            <small class="text-muted">TVA : {{ $proforma->tva_rate }}%</small>
                        </td>
                        <td>
                            <span class="badge 
                                {{ $proforma->status === 'validée' ? 'bg-success' : ($proforma->status === 'rejetée' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                {{ ucfirst($proforma->status) }}
                            </span>
                        </td>
                        <td>
                            <ul class="list-unstyled mb-0">
                                @foreach($proforma->articles as $article)
                                    <li>{{ $article->designation }} ({{ $article->quantity }} × {{ number_format($article->unit_price, 2, ',', ' ') }} F CFA)</li>
                                @endforeach
                            </ul>
                        </td>
                        @if(auth()->user()->isAdmin())
                            <td>{{ $proforma->filiale->nom ?? '—' }}</td>
                        @endif
                        <td class="text-nowrap">
                            <a href="{{ route('proformas.show', $proforma) }}" class="btn btn-info btn-sm mb-1"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('proformas.edit', $proforma) }}" class="btn btn-warning btn-sm mb-1"><i class="fas fa-edit"></i></a>
                            <a href="{{ route('proformas.pdf', $proforma) }}" class="btn btn-secondary btn-sm mb-1"><i class="fas fa-file-pdf"></i></a>
                            <a href="{{ route('proformas.export.excel', $proforma) }}" class="btn btn-success btn-sm mb-1"><i class="fas fa-file-excel"></i></a>
                            <form action="{{ route('proformas.destroy', $proforma) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm mb-1" onclick="return confirm('Supprimer cette proforma ?')"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ auth()->user()->isAdmin() ? 9 : 8 }}" class="text-center">Aucune proforma trouvée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Version mobile -->
    <div class="d-md-none">
        @forelse($proformas as $proforma)
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Réf : {{ $proforma->reference }}</h5>
                    <p class="card-text mb-1"><strong>Client:</strong> {{ optional($proforma->client)->name ?? 'Client inconnu' }}</p>
                    <p class="card-text mb-1"><strong>Date:</strong> {{ $proforma->date->format('d/m/Y') }}</p>
                    <p class="card-text mb-1"><strong>Description:</strong> {{ $proforma->description ?? '-' }}</p> <!-- Ajouté -->
                    <p class="card-text mb-1"><strong>Remise:</strong> {{ $proforma->remise ? $proforma->remise . '%' : '-' }}</p> <!-- Ajouté -->
                    <p class="card-text mb-1"><strong>Montant:</strong> {{ number_format($proforma->amount, 0, ',', ' ') }} F CFA</p>
                    <p class="card-text mb-1"><strong>TVA:</strong> {{ $proforma->tva_rate }}%</p>
                    <p class="card-text mb-1"><strong>Statut:</strong>
                        <span class="badge 
                            {{ $proforma->status === 'validée' ? 'bg-success' : ($proforma->status === 'rejetée' ? 'bg-danger' : 'bg-warning text-dark') }}">
                            {{ ucfirst($proforma->status) }}
                        </span>
                    </p>
                    <p class="card-text"><strong>Articles:</strong></p>
                    <ul class="ps-3">
                        @foreach($proforma->articles as $article)
                            <li>{{ $article->designation }} ({{ $article->quantity }} × {{ number_format($article->unit_price, 2, ',', ' ') }} F)</li>
                        @endforeach
                    </ul>
                    @if(auth()->user()->isAdmin())
                        <p class="card-text mt-2"><strong>Filiale:</strong> {{ $proforma->filiale->nom ?? '—' }}</p>
                    @endif
                    <div class="mt-2">
                        <a href="{{ route('proformas.show', $proforma) }}" class="btn btn-info btn-sm mb-1"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('proformas.edit', $proforma) }}" class="btn btn-warning btn-sm mb-1"><i class="fas fa-edit"></i></a>
                        <a href="{{ route('proformas.pdf', $proforma) }}" class="btn btn-secondary btn-sm mb-1"><i class="fas fa-file-pdf"></i></a>
                        <a href="{{ route('proformas.export.excel', $proforma) }}" class="btn btn-success btn-sm mb-1"><i class="fas fa-file-excel"></i></a>
                        <form action="{{ route('proformas.destroy', $proforma) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm mb-1" onclick="return confirm('Supprimer cette proforma ?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center">Aucune proforma trouvée.</p>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $proformas->appends(request()->query())->links() }}
    </div>
</div>
@endsection
