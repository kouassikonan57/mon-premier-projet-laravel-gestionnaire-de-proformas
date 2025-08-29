<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $facture->reference }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .logo {
            height: 60px;
            width: auto;
        }

        .document-meta {
            text-align: right;
            font-size: 12px;
        }

        .titre-facture {
            text-align: center;
            border: 2px solid black;
            padding: 8px 0;
            font-size: 18px;
            margin-top: 10px;
            margin-bottom: 10px;
            background-color: #f7f7f7;
        }

        .infos-supplementaires {
            margin: 10px 0 20px 0;
        }

        .infos-supplementaires p {
            margin: 4px 0;
            font-weight: bold;
        }

        .table-meta {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .table-meta th, .table-meta td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px;
        }

        th {
            background-color: #f77f1f;
            color: white;
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .right {
            text-align: right;
        }

        .total-table {
            width: 100%;
            border-top: 2px solid #333;
            margin-top: 20px;
            font-weight: bold;
        }

        .total-table td {
            padding: 6px;
            text-align: right;
        }

        footer {
            position: fixed;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #aaa;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        .cachet-numerique {
            position: absolute;
            right: 50px;
            bottom: 100px;
            opacity: 0.7; /* Légère transparence */
            z-index: 1000;
        }
        
        .signature-area {
            margin-top: 50px;
            text-align: right;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin: 40px 0 5px auto;
            padding-top: 5px;
            text-align: center;
        }

        .client-info {
            margin: 15px 0;
            border: 1px solid #ccc;
            padding: 10px;
            background-color: #f9f9f9;
        }
        
        .client-info p {
            margin: 4px 0;
        }
        
        .acompte-section {
            margin-top: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header clearfix">
        <img src="{{ public_path($facture->filiale->logo_path ?? 'images/default-logo.png') }}" alt="Logo" class="logo">
        <div class="document-meta">
            <h2 class="titre-facture">FACTURE @if($facture->acompte_pourcentage > 0) D'AVANCE {{ $facture->acompte_pourcentage }}% @endif</h2>
            <table class="table-meta">
                <tr>
                    <th>DATE D'ÉMISSION</th>
                    <th>DESCRIPTION</th>
                </tr>
                <tr>
                    <td>{{ $facture->date->format('d/m/Y') }}</td>
                    <td>{{ $facture->description ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Informations client avec adresse et téléphone -->
    <div class="client-info">
        <p><strong>CLIENT : </strong><span style="display:inline-block; width: 60%; text-align: right;">{{ $facture->client->name }}</span> </p>
        <p><strong>ADRESSE : </strong> <span style="display:inline-block; width: 60%; text-align: right;">{{ $facture->client->address ?? 'Non renseignée' }}</span></p>
        <p><strong>TÉLÉPHONE:</strong> <span style="display:inline-block; width: 60%; text-align: right;">{{ $facture->client->phone ?? 'Non renseigné' }}</span></p>
        @if($facture->bon_commande)
        <p><strong>N° BON DE COMMANDE:</strong> <span style="display:inline-block; width: 60%; text-align: right;">{{ $facture->bon_commande }}</span></p>
        @endif
    </div>

    @php
        $montantHT = 0;
        $montantTVA = 0;
        $montantTTC = 0;
        $tauxTVA = $facture->tva_rate / 100;
        $remise = $facture->remise ?? 0;
        
        // Initialiser $montantRemise pour éviter l'erreur
        $montantRemise = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th>Désignation</th>
                <th>Quantité</th>
                <th>Prix unitaire (HT)</th>
                <th>Total HT</th>
                <th>TVA ({{ $facture->tva_rate }}%)</th>
                <th>Total TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facture->articles as $a)
                @php
                    $totalHT = $a->quantity * $a->unit_price;
                    $tva = $totalHT * $tauxTVA;
                    $totalTTC = $totalHT + $tva;

                    $montantHT += $totalHT;
                    $montantTVA += $tva;
                    $montantTTC += $totalTTC;
                @endphp
                <tr>
                    <td>{{ $a->designation }}</td>
                    <td class="right">{{ $a->quantity }}</td>
                    <td class="right">{{ number_format($a->unit_price, 2, ',', ' ') }} FCFA</td>
                    <td class="right">{{ number_format($totalHT, 2, ',', ' ') }} FCFA</td>
                    <td class="right">{{ number_format($tva, 2, ',', ' ') }} FCFA</td>
                    <td class="right">{{ number_format($totalTTC, 2, ',', ' ') }} FCFA</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        // Application de la remise (en pourcentage)
        $montantRemise = $montantHT * ($remise / 100);
        $montantHTApresRemise = max($montantHT - $montantRemise, 0);
        $montantTVAApresRemise = $montantHTApresRemise * $tauxTVA;
        $montantTTCApresRemise = $montantHTApresRemise + $montantTVAApresRemise;
        
        // Calcul de l'acompte si applicable
        $acompteMontant = 0;
        $montantAPayer = $montantTTCApresRemise;
        
        if ($facture->acompte_pourcentage > 0) {
            $acompteMontant = ($montantTTCApresRemise * $facture->acompte_pourcentage) / 100;
            $montantAPayer = $montantTTCApresRemise - $acompteMontant;
        }
    @endphp

    <table class="total-table">
        <tr>
            <td><strong>Total HT :</strong></td>
            <td>{{ number_format($montantHT, 2, ',', ' ') }} FCFA</td>
        </tr>
        @if($remise > 0)
        <tr>
            <td><strong>Remise ({{ $remise }}%) :</strong></td>
            <td>- {{ number_format($montantRemise, 2, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
            <td><strong>Total HT après remise :</strong></td>
            <td>{{ number_format($montantHTApresRemise, 2, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
            <td><strong>TVA ({{ $facture->tva_rate }}%) après remise :</strong></td>
            <td>{{ number_format($montantTVAApresRemise, 2, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
            <td><strong>Total TTC après remise :</strong></td>
            <td>{{ number_format($montantTTCApresRemise, 2, ',', ' ') }} FCFA</td>
        </tr>
        @else
        <tr>
            <td><strong>TVA ({{ $facture->tva_rate }}%) :</strong></td>
            <td>{{ number_format($montantTVA, 2, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
            <td><strong>Total TTC :</strong></td>
            <td>{{ number_format($montantTTC, 2, ',', ' ') }} FCFA</td>
        </tr>
        @endif
        
        <!-- Section acompte -->
        @if($facture->acompte_pourcentage > 0)
        <tr>
            <td><strong>ACOMPTE {{ $facture->acompte_pourcentage }}% :</strong></td>
            <td>{{ number_format($acompteMontant, 2, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
            <td><strong>MONTANT TTC À PAYER :</strong></td>
            <td>{{ number_format($montantAPayer, 2, ',', ' ') }} FCFA</td>
        </tr>
        @endif
    </table>

    @php
        function convertirEnLettres($nombre) {
            $formatter = new \NumberFormatter('fr', \NumberFormatter::SPELLOUT);
            return ucfirst($formatter->format($nombre));
        }

        // Utiliser le montant à payer si acompte, sinon le montant TTC
        $montantEnLettres = convertirEnLettres($facture->acompte_pourcentage > 0 ? $montantAPayer : ($montantTTCApresRemise ?? $montantTTC));
    @endphp

    <p style="margin-top: 15px; font-weight: bold;">
        Arrêter la présente facture à la somme de : {{ $montantEnLettres }} francs CFA
    </p>

    <!-- Zone de signature avec cachet -->
    <div class="signature-area">
        <div style="text-align: right;">
            Fait à Abidjan, le {{ date('d/m/Y') }}
        </div>
        <!-- Cachet numérique -->
        @php
            $filialeCode = $facture->filiale->code ?? 'default';
            $cachetRelativePath = "cachets/{$filialeCode}.png";
            $cachetPath = public_path($cachetRelativePath);

            // Fallback si le fichier n'existe pas
            if (!file_exists($cachetPath)) {
                $cachetPath = public_path("cachets/default.png");
            }
        @endphp

        @if(file_exists($cachetPath))
            <div class="cachet-numerique">
                <img src="{{ $cachetPath }}" alt="Cachet numérique" style="width: 150px; opacity: 0.7; position: absolute; bottom: 50px; right: 50px;">
            </div>
        @endif

    </div>
    <footer>
        <div>
            {!! nl2br(e($facture->filiale->footer_text ?? '')) !!}
        </div>
    </footer>

</body>
</html>