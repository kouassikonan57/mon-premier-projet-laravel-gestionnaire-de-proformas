@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Détail du client</h1>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Nom :</strong> {{ $client->name }}</p>
            <p><strong>Responsable :</strong> {{ $client->responsable }}</p>
            <p><strong>Email :</strong> {{ $client->email }}</p>
            <p><strong>Téléphone :</strong> {{ $client->phone }}</p>
            <p><strong>Adresse :</strong> {{ $client->address }}</p>
            <p><strong>Numéro RCCM :</strong> {{ $client->rccm }}</p>
        </div>
    </div>

    <!-- Proformas -->
    <h2 id="proformas" class="mt-5">
        Proformas liées à ce client
        <span class="badge bg-primary">
            {{ $client->proformas->count() }} {{ Str::plural('proforma', $client->proformas->count()) }}
        </span>
    </h2>

    @if($client->proformas->isEmpty())
        <div class="alert alert-info">Aucun proforma trouvé pour ce client.</div>
    @else
        <!-- Table desktop -->
        <div class="d-none d-md-block mt-3">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>TVA</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($client->proformas as $proforma)
                        <tr>
                            <td>{{ $proforma->reference }}</td>
                            <td>{{ \Carbon\Carbon::parse($proforma->date)->format('d/m/Y') }}</td>
                            <td>{{ number_format($proforma->amount, 0, ',', ' ') }} F CFA</td>
                            <td>{{ $proforma->tva_rate }}%</td>
                            <td>
                                <span class="badge 
                                    {{ $proforma->status === 'validée' ? 'bg-success' : ($proforma->status === 'rejetée' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                    {{ ucfirst($proforma->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('proformas.show', $proforma) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('proformas.pdf', $proforma) }}" class="btn btn-secondary btn-sm"><i class="fas fa-file-pdf"></i></a>
                                <a href="{{ route('proformas.export.excel', $proforma) }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Cards mobile -->
        <div class="d-md-none mt-3">
            @foreach($client->proformas as $proforma)
                <div class="card mb-3">
                    <div class="card-body">
                        <h5>Réf : {{ $proforma->reference }}</h5>
                        <p><strong>Date :</strong> {{ \Carbon\Carbon::parse($proforma->date)->format('d/m/Y') }}</p>
                        <p><strong>Montant :</strong> {{ number_format($proforma->amount, 0, ',', ' ') }} F CFA</p>
                        <p><strong>TVA :</strong> {{ $proforma->tva_rate }}%</p>
                        <p><strong>Statut :</strong>
                            <span class="badge 
                                {{ $proforma->status === 'validée' ? 'bg-success' : ($proforma->status === 'rejetée' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                {{ ucfirst($proforma->status) }}
                            </span>
                        </p>
                        <div class="mt-2">
                            <a href="{{ route('proformas.show', $proforma) }}" class="btn btn-info btn-sm mb-1"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('proformas.pdf', $proforma) }}" class="btn btn-secondary btn-sm mb-1"><i class="fas fa-file-pdf"></i></a>
                            <a href="{{ route('proformas.export.excel', $proforma) }}" class="btn btn-success btn-sm mb-1"><i class="fas fa-file-excel"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Factures -->
    <h2 class="mt-5">
        Factures liées à ce client
        <span class="badge bg-primary">
            {{ $client->factures->count() }} {{ Str::plural('facture', $client->factures->count()) }}
        </span>
    </h2>

    @if($client->factures->isEmpty())
        <div class="alert alert-info">Aucune facture trouvée pour ce client.</div>
    @else
        <!-- Table desktop -->
        <div class="d-none d-md-block mt-3">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Date</th>
                        <th>Montant HT</th>
                        <th>Montant TVA</th>
                        <th>Montant TTC</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($client->factures as $facture)
                        <tr>
                            <td>{{ $facture->reference }}</td>
                            <td>{{ $facture->date->format('d/m/Y') }}</td>
                            <td class="text-end">{{ number_format($facture->amount, 2, ',', ' ') }} F CFA</td>
                            <td class="text-end">{{ number_format($facture->amount * 0.18, 2, ',', ' ') }} F CFA</td>
                            <td class="text-end">{{ number_format($facture->amount * 1.18, 2, ',', ' ') }} F CFA</td>
                            <td>
                                <span class="badge 
                                    {{ $facture->status === 'payée' ? 'bg-success' : ($facture->status === 'envoyée' ? 'bg-info' : ($facture->status === 'annulée' ? 'bg-danger' : 'bg-warning text-dark')) }}">
                                    {{ ucfirst($facture->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('factures.show', $facture) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('factures.export.pdf', $facture) }}" class="btn btn-secondary btn-sm"><i class="fas fa-file-pdf"></i></a>
                                <a href="{{ route('factures.export.excel', $facture) }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Cards mobile -->
        <div class="d-md-none mt-3">
            @foreach($client->factures as $facture)
                <div class="card mb-3">
                    <div class="card-body">
                        <h5>Réf : {{ $facture->reference }}</h5>
                        <p><strong>Date :</strong> {{ $facture->date->format('d/m/Y') }}</p>
                        <p><strong>HT :</strong> {{ number_format($facture->amount, 2, ',', ' ') }} F CFA</p>
                        <p><strong>TVA :</strong> {{ number_format($facture->amount * 0.18, 2, ',', ' ') }} F CFA</p>
                        <p><strong>TTC :</strong> {{ number_format($facture->amount * 1.18, 2, ',', ' ') }} F CFA</p>
                        <p><strong>Statut :</strong>
                            <span class="badge 
                                {{ $facture->status === 'payée' ? 'bg-success' : ($facture->status === 'envoyée' ? 'bg-info' : ($facture->status === 'annulée' ? 'bg-danger' : 'bg-warning text-dark')) }}">
                                {{ ucfirst($facture->status) }}
                            </span>
                        </p>
                        <div class="mt-2">
                            <a href="{{ route('factures.show', $facture) }}" class="btn btn-info btn-sm mb-1"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('factures.export.pdf', $facture) }}" class="btn btn-secondary btn-sm mb-1"><i class="fas fa-file-pdf"></i></a>
                            <a href="{{ route('factures.export.excel', $facture) }}" class="btn btn-success btn-sm mb-1"><i class="fas fa-file-excel"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <a href="{{ route('clients.index') }}" class="btn btn-secondary mt-4">⬅️ Retour à la liste</a>
</div>
@endsection
