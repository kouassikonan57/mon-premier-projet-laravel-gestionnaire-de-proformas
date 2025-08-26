@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Liste des Factures</h1>

    {{-- Formulaire de recherche + filtre filiale (admin uniquement) --}}
    <form method="GET" action="{{ route('factures.index') }}" class="row g-3 mb-4 align-items-end">
        <div class="col-md-4 col-12">
            <label for="search" class="form-label">Rechercher (Référence ou Client)</label>
            <input type="text" name="search" id="search" class="form-control" placeholder="Rechercher une facture..." value="{{ request('search') }}">
        </div>

        @if(auth()->user()->isAdmin())
            <div class="col-md-4 col-12">
                <label for="filiale_id" class="form-label">Filiale</label>
                <select name="filiale_id" id="filiale_id" class="form-select">
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
            <button type="submit" class="btn btn-success">
                <i class="fas fa-search"></i> Rechercher
            </button>
        </div>
    </form>

    {{-- Bouton ajouter une facture --}}
    <a href="{{ route('factures.create') }}" class="btn btn-success mb-4">
        <i class="fas fa-plus"></i> Ajouter une facture
    </a>

    {{-- Table desktop --}}
    <div class="d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Référence</th>
                        <th>Client</th>                        
                        <th>Date</th>
                        <th>Description</th>
                        <th>Remise</th>
                        <th class="text-end">Montant HT</th>
                        <th class="text-end">Montant après remise</th>
                        <th class="text-end">TVA</th>
                        <th class="text-end">Total TTC</th>
                        <th>Statut</th>
                        @if(auth()->user()->isAdmin())
                            <th>Filiale</th>
                        @endif
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($factures as $facture)
                        @php
                            $montantHT = $facture->amount;
                            $montantRemise = $montantHT * ($facture->remise / 100);
                            $montantApresRemise = $montantHT - $montantRemise;
                            $montantTVA = $montantApresRemise * ($facture->tva_rate / 100);
                            $totalTTC = $montantApresRemise + $montantTVA;
                        @endphp
                        <tr>
                            <td>{{ $facture->reference }}</td>
                            <td>{{ $facture->client->name }}</td>
                            <td>{{ $facture->date->format('d/m/Y') }}</td>
                            <td>{{ $facture->description ? Str::limit($facture->description, 30) : '-' }}</td>
                            <td class="text-center">{{ $facture->remise ? $facture->remise . '%' : '-' }}</td>
                            <td class="text-end">{{ number_format($montantHT, 2, ',', ' ') }} F CFA</td>
                            <td class="text-end">{{ $facture->remise ? number_format($montantApresRemise, 2, ',', ' ') . ' F CFA' : '-' }}</td>
                            <td class="text-end">{{ number_format($montantTVA, 2, ',', ' ') }} F CFA ({{ $facture->tva_rate }}%)</td>
                            <td class="text-end">{{ number_format($totalTTC, 2, ',', ' ') }} F CFA</td>
                            <td>
                                <span class="badge 
                                    {{ $facture->status === 'payée' ? 'bg-success' : 
                                       ($facture->status === 'envoyée' ? 'bg-info' : 
                                       ($facture->status === 'annulée' ? 'bg-danger' : 'bg-warning text-dark')) }}">
                                    {{ ucfirst($facture->status) }}
                                </span>
                            </td>
                            @if(auth()->user()->isAdmin())
                                <td>{{ $facture->filiale->nom ?? '—' }}</td>
                            @endif
                            <td class="text-nowrap">
                                <a href="{{ route('factures.show', $facture) }}" class="btn btn-info btn-sm" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('factures.export.pdf', $facture) }}" class="btn btn-secondary btn-sm" title="PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <a href="{{ route('factures.export.excel', $facture) }}" class="btn btn-success btn-sm" title="Excel">
                                    <i class="fas fa-file-excel"></i>
                                </a>
                                <form action="{{ route('factures.destroy', $facture) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette facture ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdmin() ? 12 : 11 }}" class="text-center">Aucune facture trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Affichage mobile (cartes) --}}
    <div class="d-md-none">
        @forelse($factures as $facture)
            @php
                $montantHT = $facture->amount;
                $montantRemise = $montantHT * ($facture->remise / 100);
                $montantApresRemise = $montantHT - $montantRemise;
                $montantTVA = $montantApresRemise * ($facture->tva_rate / 100);
                $totalTTC = $montantApresRemise + $montantTVA;
            @endphp
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Réf : {{ $facture->reference }}</h5>
                    <p class="card-text mb-1"><strong>Client :</strong> {{ $facture->client->name }}</p>                   
                    <p class="card-text mb-1"><strong>Date :</strong> {{ $facture->date->format('d/m/Y') }}</p>
                    <p class="card-text mb-1"><strong>Description :</strong> {{ $facture->description ?? '-' }}</p>
                    <p class="card-text mb-1"><strong>Remise :</strong> {{ $facture->remise ? $facture->remise . '%' : '-' }}</p>
                    <p class="card-text mb-1 text-end"><strong>Montant HT :</strong> {{ number_format($montantHT, 2, ',', ' ') }} F CFA</p>
                    @if($facture->remise)
                        <p class="card-text mb-1 text-end"><strong>Montant après remise :</strong> {{ number_format($montantApresRemise, 2, ',', ' ') }} F CFA</p>
                    @endif
                    <p class="card-text mb-1 text-end"><strong>TVA ({{ $facture->tva_rate }}%) :</strong> {{ number_format($montantTVA, 2, ',', ' ') }} F CFA</p>
                    <p class="card-text mb-1 text-end"><strong>TTC :</strong> {{ number_format($totalTTC, 2, ',', ' ') }} F CFA</p>
                    <p class="card-text mb-1"><strong>Statut :</strong>
                        <span class="badge 
                            {{ $facture->status === 'payée' ? 'bg-success' : 
                               ($facture->status === 'envoyée' ? 'bg-info' : 
                               ($facture->status === 'annulée' ? 'bg-danger' : 'bg-warning text-dark')) }}">
                            {{ ucfirst($facture->status) }}
                        </span>
                    </p>
                    @if(auth()->user()->isAdmin())
                        <p class="card-text mb-1"><strong>Filiale :</strong> {{ $facture->filiale->nom ?? '—' }}</p>
                    @endif
                    <div class="d-flex gap-2 flex-wrap mt-2">
                        <a href="{{ route('factures.show', $facture) }}" class="btn btn-info btn-sm" title="Voir">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('factures.export.pdf', $facture) }}" class="btn btn-secondary btn-sm" title="PDF">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        <a href="{{ route('factures.export.excel', $facture) }}" class="btn btn-success btn-sm" title="Excel">
                            <i class="fas fa-file-excel"></i>
                        </a>
                        <form action="{{ route('factures.destroy', $facture) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette facture ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center">Aucune facture trouvée.</p>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $factures->appends(request()->query())->links() }}
    </div>
</div>
@endsection