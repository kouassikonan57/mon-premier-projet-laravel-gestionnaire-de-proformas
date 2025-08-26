@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Sélectionner une Proforma</h1>

    <form action="{{ route('factures.create') }}" method="GET">
        <div class="form-group mb-3">
            <label for="proforma_id">Proforma</label>
            <select name="proforma_id" id="proforma_id" class="form-control" required>
                <option value="">-- Choisir une proforma --</option>
                @foreach($proformas as $proforma)
                    @php
                        $montantHT = $proforma->amount;
                        $montantRemise = $montantHT * ($proforma->remise / 100);
                        $montantApresRemise = $montantHT - $montantRemise;
                        $montantTVA = $montantApresRemise * ($proforma->tva_rate / 100);
                        $totalTTC = $montantApresRemise + $montantTVA;
                    @endphp
                    <option value="{{ $proforma->id }}">
                        {{ $proforma->reference }} - {{ $proforma->client->name }} - 
                        HT: {{ number_format($montantHT, 2, ',', ' ') }} F CFA - 
                        @if($proforma->remise)Remise: {{ $proforma->remise }}% - @endif
                        TTC: {{ number_format($totalTTC, 2, ',', ' ') }} F CFA
                        @if($proforma->description) - {{ Str::limit($proforma->description, 30) }}@endif
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Continuer</button>
    </form>
</div>
@endsection