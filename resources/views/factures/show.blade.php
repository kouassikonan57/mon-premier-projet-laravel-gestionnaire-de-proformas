@extends('layouts.app')
<style>
@media (max-width: 768px) {
    .table-responsive-mobile thead {
        display: none;
    }

    .table-responsive-mobile tr {
        display: block;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        padding: 10px;
    }

    .table-responsive-mobile td {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        border: none !important;
    }

    .table-responsive-mobile td::before {
        content: attr(data-label);
        font-weight: bold;
        text-transform: uppercase;
        color: #555;
    }
}
</style>

@section('content')
<div class="container">
    <h1>Facture {{ $facture->reference }}</h1>
    <p><strong>Client :</strong> {{ $facture->client->name }}</p>
    <p><strong>Date :</strong> {{ $facture->date->format('d/m/Y') }}</p>
    <p><strong>Description :</strong> {{ $facture->description ?? '-' }}</p>
    <p><strong>Remise :</strong> {{ $facture->remise ? $facture->remise . '%' : '-' }}</p>

    <h4>Articles</h4>
    <table class="table table-bordered table-responsive-mobile">
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
                <td data-label="Désignation">{{ $a->designation }}</td>
                <td data-label="Quantité">{{ $a->quantity }}</td>
                <td data-label="Prix unitaire HT" class="text-end">{{ number_format($a->unit_price, 2, ',', ' ') }}</td>
                <td data-label="Total HT" class="text-end">{{ number_format($a->quantity * $a->unit_price, 2, ',', ' ') }}</td>
                <td data-label="TVA ({{ $facture->tva_rate }}%)" class="text-end">{{ number_format($a->quantity * $a->unit_price * ($facture->tva_rate / 100), 2, ',', ' ') }}</td>
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

    <!-- NOUVEAU : ÉTAT DES PAIEMENTS -->
    <h3>État des paiements</h3>
    <div class="mb-3 p-3 border rounded bg-light">
        <p><strong>Total TTC :</strong> {{ number_format($totalTTC, 2, ',', ' ') }} F CFA</p>
        <p><strong>Montant payé :</strong> {{ number_format($facture->montant_paye, 2, ',', ' ') }} F CFA</p>
        <p><strong>Reste à payer :</strong> {{ number_format($facture->reste_a_payer, 2, ',', ' ') }} F CFA</p>
    </div>

    @if($facture->reste_a_payer > 0)
    <h4>Enregistrer un nouveau paiement</h4>
    <form action="{{ route('factures.paiement.store', $facture) }}" method="POST" class="mb-4 p-3 border rounded">
        @csrf
        <div class="row g-3">
            <div class="col-md-3">
                <label for="pourcentage_paiement" class="form-label">Pourcentage de paiement</label>
                <input type="number" name="pourcentage_paiement" id="pourcentage_paiement" class="form-control" 
                    step="0.01" min="0.01" max="100" required oninput="calculerMontantFromPourcentage()">
                <small class="form-text text-muted">% du total TTC</small>
            </div>
            <div class="col-md-3">
                <label for="montant" class="form-label">Montant (F CFA)</label>
                <input type="number" name="montant" id="montant" class="form-control" 
                    step="0.01" min="0.01" required oninput="calculerPourcentageFromMontant()">
                <small class="form-text text-muted">Montant exact</small>
            </div>
            <div class="col-md-3">
                <label for="date_paiement" class="form-label">Date du paiement</label>
                <input type="date" name="date_paiement" id="date_paiement" 
                    class="form-control" value="{{ now()->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-3">
                <label for="mode_paiement" class="form-label">Mode de paiement</label>
                <select name="mode_paiement" id="mode_paiement" class="form-select" required>
                    <option value="espèce">Espèce</option>
                    <option value="virement">Virement</option>
                    <option value="chèque">Chèque</option>
                    <option value="carte">Carte bancaire</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="reference" class="form-label">Référence</label>
                <input type="text" name="reference" id="reference" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="notes" class="form-label">Notes</label>
                <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-success">Enregistrer le paiement</button>
            </div>
        </div>
    </form>

    <script>
    // Calculer le montant à partir du pourcentage
    function calculerMontantFromPourcentage() {
        const pourcentage = parseFloat(document.getElementById('pourcentage_paiement').value) || 0;
        
        // Calculer par rapport au TOTAL TTC ({{ $totalTTC }})
        const totalTTC = {{ $totalTTC }};
        let montant = (totalTTC * pourcentage) / 100;
        
        // Ne pas dépasser le reste à payer
        const resteAPayer = {{ $facture->reste_a_payer }};
        if (montant > resteAPayer) {
            montant = resteAPayer;
            document.getElementById('pourcentage_paiement').value = ((resteAPayer / totalTTC) * 100).toFixed(2);
            alert('Le montant a été ajusté pour ne pas dépasser le reste à payer.');
        }
        
        document.getElementById('montant').value = montant.toFixed(2);
    }

    // Calculer le pourcentage à partir du montant
    function calculerPourcentageFromMontant() {
        const montant = parseFloat(document.getElementById('montant').value) || 0;
        
        // Calculer le pourcentage par rapport au TOTAL TTC
        const totalTTC = {{ $totalTTC }};
        const pourcentage = (montant / totalTTC) * 100;
        
        document.getElementById('pourcentage_paiement').value = pourcentage.toFixed(2);
    }

    // Initialiser le calcul au chargement
    document.addEventListener('DOMContentLoaded', function() {
        calculerMontantFromPourcentage();
    });
    </script>
    @endif

    @if($facture->paiements->count() > 0)
        <h4>Historique des paiements</h4>
        <table class="table table-bordered table-responsive-mobile">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Mode</th>
                    <th>Référence</th>
                    <th>Notes</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($facture->paiements as $paiement)
                <tr>
                    <td data-label="Date">{{ $paiement->date_paiement->format('d/m/Y') }}</td>
                    <td data-label="Montant" class="text-end">{{ number_format($paiement->montant, 2, ',', ' ') }} F CFA</td>
                    <td data-label="Mode">{{ ucfirst($paiement->mode_paiement) }}</td>
                    <td data-label="Référence">{{ $paiement->reference ?? '-' }}</td>
                    <td data-label="Notes">{{ $paiement->notes ?? '-' }}</td>
                    <td data-label="Action">
                        <a href="{{ route('factures.paiement.pdf', ['facture' => $facture->id, 'paiement' => $paiement->id]) }}" 
                            class="btn btn-sm btn-outline-primary" title="Télécharger ce paiement">
                            <i class="fas fa-file-pdf text-danger"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="mt-4">
        <a href="{{ route('factures.export.pdf', $facture) }}" class="btn btn-danger mb-3">📄 Télécharger PDF</a>
        <a href="{{ route('factures.export.excel', $facture) }}" class="btn btn-success mb-3">📊 Exporter en Excel</a>
        <a href="{{ route('factures.index') }}" class="btn btn-primary">⬅️ Retour aux factures</a>
    </div>

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