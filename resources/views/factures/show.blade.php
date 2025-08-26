@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Facture {{ $facture->reference }}</h1>
    <p><strong>Client :</strong> {{ $facture->client->name }}</p>
    <p><strong>Date :</strong> {{ $facture->date->format('d/m/Y') }}</p>
    <p><strong>Description :</strong> {{ $facture->description ?? '-' }}</p> <!-- Ajouté -->
    <p><strong>Remise :</strong> {{ $facture->remise ? $facture->remise . '%' : '-' }}</p> <!-- Ajouté -->

    <h4>Articles</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Désignation</th>
                <th>Quantité</th>
                <th>Prix unitaire HT (F CFA)</th>
                <th>Total HT (F CFA)</th>
                <th>TVA ({{ $facture->tva_rate }}%) (F CFA)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facture->articles as $a)
            <tr>
                <td>{{ $a->designation }}</td>
                <td>{{ $a->quantity }}</td>
                <td class="text-end">{{ number_format($a->unit_price, 2, ',', ' ') }}</td>
                <td class="text-end">{{ number_format($a->quantity * $a->unit_price, 2, ',', ' ') }}</td>
                <td class="text-end">{{ number_format($a->quantity * $a->unit_price * ($facture->tva_rate / 100), 2, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $montantHT = $facture->amount;
        $montantRemise = $montantHT * ($facture->remise / 100);
        $montantApresRemise = $montantHT - $montantRemise;
        $montantTVA = $montantApresRemise * ($facture->tva_rate / 100);
        $totalTTC = $montantApresRemise + $montantTVA;
    @endphp

    <p><strong>Montant HT :</strong> {{ number_format($montantHT, 2, ',', ' ') }} F CFA</p>
    
    @if($facture->remise > 0)
    <p><strong>Remise ({{ $facture->remise }}%) :</strong> -{{ number_format($montantRemise, 2, ',', ' ') }} F CFA</p>
    <p><strong>Montant après remise :</strong> {{ number_format($montantApresRemise, 2, ',', ' ') }} F CFA</p>
    @endif
    
    <p><strong>Montant TVA ({{ $facture->tva_rate }}%) :</strong> {{ number_format($montantTVA, 2, ',', ' ') }} F CFA</p>
    <p><strong>Montant TTC :</strong> {{ number_format($totalTTC, 2, ',', ' ') }} F CFA</p>
    <p><strong>Filiale :</strong> {{ $facture->filiale->nom ?? '-' }}</p>

    <a href="{{ route('factures.export.pdf', $facture) }}" class="btn btn-danger mb-3">📄 Télécharger PDF</a>
    <a href="{{ route('factures.export.excel', $facture) }}" class="btn btn-success mb-3">📊 Exporter en Excel</a>
    <a href="{{ route('factures.index') }}" class="btn btn-primary">⬅️ Retour aux factures</a>

    <h3>Statut actuel : <strong>{{ ucfirst($facture->status) }}</strong></h3>

    @if ($facture->status !== 'envoyée')
        <form action="{{ route('factures.changeStatus', [$facture, 'envoyée']) }}" method="POST" style="display:inline;">
            @csrf
            @method('PUT')
            <button type="submit" class="btn btn-outline-primary">📤 Marquer comme envoyée</button>
        </form>
    @endif

    @if ($facture->status === 'envoyée')
        <form action="{{ route('factures.changeStatus', [$facture, 'payée']) }}" method="POST" style="display:inline;">
            @csrf
            @method('PUT')
            <button type="submit" class="btn btn-outline-success">💰 Marquer comme payée</button>
        </form>
    @endif

    @if ($facture->status !== 'annulée')
        <form action="{{ route('factures.changeStatus', [$facture, 'annulée']) }}" method="POST" style="display:inline;">
            @csrf
            @method('PUT')
            <button type="submit" class="btn btn-outline-danger">❌ Annuler</button>
        </form>
    @endif
</div>
@endsection