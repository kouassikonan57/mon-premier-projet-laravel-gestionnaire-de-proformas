@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Créer une Facture</h1>

    <div class="mb-3">
        <strong>Proforma :</strong> {{ $proforma->reference }}<br>
        <strong>Client :</strong> {{ $proforma->client->name }}<br>
        <strong>Montant HT :</strong> {{ number_format($proforma->amount, 2, ',', ' ') }} F CFA
    </div>

    <form action="{{ route('factures.store') }}" method="POST">
        @csrf
        <input type="hidden" name="proforma_id" value="{{ $proforma->id }}">
        <input type="hidden" name="client_id" value="{{ $proforma->client_id }}">
        <input type="hidden" name="filiale_id" value="{{ $proforma->filiale_id }}">
        <input type="hidden" name="tva_rate" value="{{ $proforma->tva_rate }}">
        <input type="hidden" name="remise" value="{{ $proforma->remise }}">

        <div class="form-group mb-3">
            <label for="reference">Référence de la facture *</label>
            <input type="text" name="reference" id="reference" class="form-control" value="{{ old('reference', 'FAC-' . date('Y') . '-0001') }}" required>
        </div>

        <div class="form-group mb-3">
            <label for="date">Date de la facture *</label>
            <input type="date" name="date" id="date" class="form-control" value="{{ now()->toDateString() }}" required>
        </div>

        @if($proforma->description)
        <div class="form-group mb-3">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control" readonly>{{ $proforma->description }}</textarea>
        </div>
        @endif

        <h4>Articles repris de la proforma :</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th>Quantité</th>
                    <th>Prix unitaire HT (F CFA)</th>
                    <th>Total HT (F CFA)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalHT = 0;
                @endphp
                @foreach($proforma->articles as $article)
                @php
                    $totalHT += $article->quantity * $article->unit_price;
                @endphp
                <tr>
                    <td>{{ $article->designation }}</td>
                    <td>{{ $article->quantity }}</td>
                    <td class="text-end">{{ number_format($article->unit_price, 2, ',', ' ') }}</td>
                    <td class="text-end">{{ number_format($article->quantity * $article->unit_price, 2, ',', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mb-3">
            @php
                $montantRemise = $totalHT * ($proforma->remise / 100);
                $totalApresRemise = $totalHT - $montantRemise;
                $montantTVA = $totalApresRemise * ($proforma->tva_rate / 100);
                $totalTTC = $totalApresRemise + $montantTVA;
            @endphp
            
            <strong>Total HT :</strong> {{ number_format($totalHT, 2, ',', ' ') }} F CFA<br>
            
            @if($proforma->remise > 0)
            <strong>Remise ({{ $proforma->remise }}%) :</strong> -{{ number_format($montantRemise, 2, ',', ' ') }} F CFA<br>
            <strong>Total après remise :</strong> {{ number_format($totalApresRemise, 2, ',', ' ') }} F CFA<br>
            @endif
            
            <strong>TVA ({{ $proforma->tva_rate }}%) :</strong> {{ number_format($montantTVA, 2, ',', ' ') }} F CFA<br>
            <strong>Total TTC :</strong> {{ number_format($totalTTC, 2, ',', ' ') }} F CFA
        </div>

        <button type="submit" class="btn btn-success">Créer la facture</button>
        <a href="{{ route('factures.selectProforma') }}" class="btn btn-secondary">Retour</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation du formulaire
    const form = document.querySelector('form');
    form.addEventListener('submit', function(event) {
        let errors = [];

        // Vérification de la référence
        const reference = document.getElementById('reference').value.trim();
        if (!reference) {
            errors.push('La référence de la facture est obligatoire.');
        }

        // Vérification de la date
        const date = document.getElementById('date').value;
        if (!date) {
            errors.push('La date de la facture est obligatoire.');
        }

        if (errors.length > 0) {
            event.preventDefault();
            alert(errors.join('\n'));
        }
    });
});
</script>
@endsection