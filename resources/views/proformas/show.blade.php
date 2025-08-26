@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Détails de la Proforma {{ $proforma->reference }}</h1>

    <p><strong>Client :</strong> {{ $proforma->client->name }}</p>
    <p><strong>Date :</strong> {{ $proforma->date->format('d/m/Y') }}</p>
    <p><strong>Description :</strong> {{ $proforma->description ?? '-' }}</p> <!-- Ajouté -->
    <p><strong>Remise :</strong> {{ $proforma->remise ? $proforma->remise . '%' : '-' }}</p> <!-- Ajouté -->

    <!-- Affichage des articles -->
    <h3>Articles</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Quantité</th>
                <th>Prix unitaire</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($proforma->articles as $article)
            <tr>
                <td>{{ $article->designation }}</td>
                <td>{{ $article->quantity }}</td>
                <td>{{ number_format($article->unit_price, 0, ',', ' ') }} F CFA</td>
                <td>{{ number_format($article->quantity * $article->unit_price, 0, ',', ' ') }} F CFA</td>

            </tr>
            @endforeach
        </tbody>
    </table>


    <p><strong>Montant total :</strong>
        {{ number_format($proforma->articles->sum(fn($article) => $article->quantity * $article->unit_price), 0, ',', ' ') }} F CFA
    </p>

    <h3>Statut actuel : <strong>{{ ucfirst($proforma->status) }}</strong></h3>
    <a href="{{ route('proformas.pdf', $proforma) }}" class="btn btn-secondary mb-3">📄 Télécharger PDF</a>
    <a href="{{ route('proformas.export.excel', $proforma) }}" class="btn btn-success mb-3">📊 Exporter en Excel</a>
    <!-- <a href="{{ route('proformas.convert', $proforma) }}" class="btn btn-success mb-3">
        💸 Convertir en facture
    </a> -->
    <a href="{{ route('proformas.index') }}" class="btn btn-primary">⬅️ Retour à la liste</a>
</div>
@endsection
