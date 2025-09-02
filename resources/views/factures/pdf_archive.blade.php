<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $facture->reference }} - Paiement du {{ $paiement->date_paiement->format('d/m/Y') }}</title>
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

        @page {
            counter-increment: page;
        }

        footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #aaa;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        footer::after {
            content: "Page " counter(page);
            display: block;
            margin-top: 2px;
        }

        .cachet-numerique img {
            width: 150px;
            height: 150px;
            object-fit: contain;
        }
    
        .signature-area {
            margin-top: 50px;
            text-align: right;
        }

        .client-info table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .client-info td {
            border: none;
            padding: 3px 5px;
        }

        .client-info td.label {
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
            width: 50%;
        }

        .client-info td.value {
            text-align: right;
            width: 50%;
        }

        table.facture-grid { 
            width:100%; 
            border-collapse:collapse; 
            font-family: Arial, sans-serif;
            page-break-inside: avoid;
            margin-top: 20px;
        }
        .facture-grid td { 
            border:1px solid #000; 
            padding:8px; 
            page-break-inside: avoid;
        }
        .facture-left { 
            width:42%; 
            text-align:center; 
            vertical-align:middle; 
            font-weight:bold; 
        }
        .facture-label { 
            font-weight:bold; 
            text-align:left; 
        }
        .facture-value { 
            text-align:right; 
            white-space:nowrap; 
        }
        .facture-important td {
            color: red;
            font-weight: bold;
            background-color: #ffe5e5;
        }
        
        .etat-paiement {
            margin-top: 20px;
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #f77f1f;
            background-color: #fff9f0;
        }
        
        .etat-paiement h4 {
            margin-top: 0;
            color: #f77f1f;
            text-align: center;
            border-bottom: 1px solid #f77f1f;
            padding-bottom: 5px;
        }
    </style>
</head>
<body>
    <footer>
        {!! nl2br(e($facture->filiale->footer_text ?? '')) !!}
    </footer>

    @php
        // CALCULS DES MONTANTS (DOIT ÊTRE PLACÉ AU DÉBUT DU DOCUMENT)
        $montantHT = 0;
        $montantTVA = 0;
        $montantTTC = 0;
        $tauxTVA = $facture->tva_rate / 100;
        $remise = $facture->remise ?? 0;

        foreach($facture->articles as $a) {
            $totalHT = $a->quantity * $a->unit_price;
            $tva = $totalHT * $tauxTVA;
            $totalTTC = $totalHT + $tva;
            $montantHT += $totalHT;
            $montantTVA += $tva;
            $montantTTC += $totalTTC;
        }

        $montantRemise = $montantHT * ($remise / 100);
        $montantHTApresRemise = max($montantHT - $montantRemise, 0);
        $montantTVAApresRemise = $montantHTApresRemise * $tauxTVA;
        $montantTTCApresRemise = $montantHTApresRemise + $montantTVAApresRemise;

        // Calculer le montant payé jusqu'à ce paiement
        $montantPayeJusquici = $facture->paiements->where('id', '<=', $paiement->id)->sum('montant');
        $resteAPayer = $montantTTCApresRemise - $montantPayeJusquici;
        
        if ($montantTTCApresRemise > 0) {
            $pourcentagePaye = ($montantPayeJusquici / $montantTTCApresRemise) * 100;
        } else {
            $pourcentagePaye = 0;
        }
    @endphp

    <div class="header clearfix">
        <img src="{{ public_path($facture->filiale->logo_path ?? 'images/default-logo.png') }}" alt="Logo" class="logo">
        <div class="document-meta">
            <h2 class="titre-facture">
                @php
                    // DÉTERMINER LE TITRE DYNAMIQUE CORRECT
                    $titre = 'FACTURE';
                    
                    if ($pourcentagePaye == 100) {
                        $titre = 'FACTURE FINALE';
                    } elseif ($pourcentagePaye > 0) {
                        // Méthode 1: Compter le nombre de paiements jusqu'à celui-ci
                        $nombrePaiements = $facture->paiements->where('id', '<=', $paiement->id)->count();
                        
                        // Méthode 2: Vérifier le montant
                        $ecart = abs($montantPayeJusquici - ($facture->acompte_montant ?? 0));
                        
                        if ($nombrePaiements == 1 || $ecart < 0.01) {
                            // Premier paiement ou montant correspondant à l'acompte
                            $titre = 'FACTURE D\'AVANCE ' . number_format($pourcentagePaye, 2) . '%';
                        } else {
                            // Paiements supplémentaires
                            $titre = 'FACTURE SOLDE ' . number_format($pourcentagePaye, 2) . '%';
                        }
                    }
                @endphp
                {{ $titre }} - PAIEMENT DU {{ $paiement->date_paiement->format('d/m/Y') }}
            </h2>
            <table class="table-meta">
                <tr>
                    <th>DATE D'ÉMISSION</th>
                    <th>DATE DU PAIEMENT</th>
                    <th>RÉFÉRENCE</th>
                </tr>
                <tr>
                    <td>{{ $facture->date->format('d/m/Y') }}</td>
                    <td>{{ $paiement->date_paiement->format('d/m/Y') }}</td>
                    <td>{{ $facture->reference }}-PAY{{ $paiement->id }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="client-info">
        <table>
            <tr>
                <td class="label">CLIENT</td>
                <td class="value">{{ $facture->client->name }}</td>
            </tr>
            <tr>
                <td class="label">ADRESSE</td>
                <td class="value">{{ $facture->client->address ?? 'Non renseignée' }}</td>
            </tr>
            <tr>
                <td class="label">TÉLÉPHONE</td>
                <td class="value">{{ $facture->client->phone ?? 'Non renseigné' }}</td>
            </tr>
            @if($facture->bon_commande)
            <tr>
                <td class="label">N° BON DE COMMANDE</td>
                <td class="value">{{ $facture->bon_commande }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Afficher les informations spécifiques à ce paiement -->
    <div class="etat-paiement">
        <h4>DÉTAIL DU PAIEMENT</h4>
        <table width="100%">
            <tr>
                <td width="60%"><strong>Date du paiement:</strong></td>
                <td width="40%" class="right">{{ $paiement->date_paiement->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Mode de paiement:</strong></td>
                <td class="right">{{ ucfirst($paiement->mode_paiement) }}</td>
            </tr>
            <tr>
                <td><strong>Montant de ce paiement:</strong></td>
                <td class="right">{{ number_format($paiement->montant, 2, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td><strong>Pourcentage de ce paiement:</strong></td>
                <td class="right">{{ number_format($paiement->pourcentage, 2) }}%</td>
            </tr>
            <tr>
                <td><strong>Total payé jusqu'à ce jour:</strong></td>
                <td class="right">{{ number_format($montantPayeJusquici, 2, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td><strong>Pourcentage total payé:</strong></td>
                <td class="right">{{ number_format($pourcentagePaye, 2) }}%</td>
            </tr>
            <tr>
                <td><strong>Reste à payer après ce paiement:</strong></td>
                <td class="right">{{ number_format($resteAPayer, 2, ',', ' ') }} FCFA</td>
            </tr>
            @if($paiement->reference)
            <tr>
                <td><strong>Référence:</strong></td>
                <td class="right">{{ $paiement->reference }}</td>
            </tr>
            @endif
            @if($paiement->notes)
            <tr>
                <td><strong>Notes:</strong></td>
                <td class="right">{{ $paiement->notes }}</td>
            </tr>
            @endif
        </table>
    </div>

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
        // FONCTION POUR CONVERSION EN LETTRES
        if (!function_exists('convertirEnLettres')) {
            function convertirEnLettres($nombre) {
                $formatter = new \NumberFormatter('fr', \NumberFormatter::SPELLOUT);
                return ucfirst($formatter->format($nombre));
            }
        }

        $montantEnLettres = convertirEnLettres($montantTTCApresRemise);
        
        // CALCUL DU ROWSPAN POUR LE TABLEAU
        $rowspan = 1; // Total HT
        if ($remise > 0) { 
            $rowspan += 4; // Remise, HT après remise, TVA après remise, TTC après remise
        } else { 
            $rowspan += 2; // TVA, TTC
        }
        $rowspan += 2; // Montant payé + Reste à payer/Statut
    @endphp

    <table class="facture-grid">
        <tr>
            <td class="facture-left" rowspan="{{ $rowspan }}">
                Arrêter la présente facture à la somme de :<br><br>
                {{ $montantEnLettres }} CFA
            </td>
            <td class="facture-label">Total HT</td>
            <td class="facture-value">{{ number_format($montantHT, 2, ',', ' ') }} FCFA</td>
        </tr>

        @if($remise > 0)
            <tr>
                <td class="facture-label">Remise ({{ $remise }}%)</td>
                <td class="facture-value">- {{ number_format($montantRemise, 2, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td class="facture-label">Total HT après remise</td>
                <td class="facture-value">{{ number_format($montantHTApresRemise, 2, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td class="facture-label">TVA ({{ $facture->tva_rate }}%) après remise</td>
                <td class="facture-value">{{ number_format($montantTVAApresRemise, 2, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td class="facture-label">Total TTC après remise</td>
                <td class="facture-value">{{ number_format($montantTTCApresRemise, 2, ',', ' ') }} FCFA</td>
            </tr>
        @else
            <tr>
                <td class="facture-label">TVA ({{ $facture->tva_rate }}%)</td>
                <td class="facture-value">{{ number_format($montantTVA, 2, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td class="facture-label">Total TTC</td>
                <td class="facture-value">{{ number_format($montantTTC, 2, ',', ' ') }} FCFA</td>
            </tr>
        @endif

        <tr class="facture-important">
            <td class="facture-label">MONTANT {{ $pourcentagePaye == 100 ? 'TOTAL ' : '' }}PAYÉ ({{ number_format($pourcentagePaye, 2) }}%)</td>
            <td class="facture-value">{{ number_format($montantPayeJusquici, 2, ',', ' ') }} FCFA</td>
        </tr>

        @if($resteAPayer > 0)
            <tr class="facture-important">
                <td class="facture-label">RESTE À PAYER ({{ number_format(100 - $pourcentagePaye, 2) }}%)</td>
                <td class="facture-value">{{ number_format($resteAPayer, 2, ',', ' ') }} FCFA</td>
            </tr>
        @else
            <tr class="facture-important">
                <td class="facture-label">STATUT</td>
                <td class="facture-value">FACTURE COMPLÈTEMENT PAYÉE</td>
            </tr>
        @endif
    </table>

    <div class="signature-area">
        @php
            // DÉTERMINATION DU CACHET
            $filialeCode = $facture->filiale->code ?? 'default';
            $cachetFilename = '';
            
            switch($filialeCode) {
                case 'DDCS-001':
                    $cachetFilename = 'DDCS-001.png';
                    break;
                case 'YADI-002':
                    $cachetFilename = 'YADI-002.png';
                    break;
                case 'YDIA_CONSTRUCTION-003':
                    $cachetFilename = 'YDIA_CONSTRUCTION-003.png';
                    break;
                case 'VROOM-004':
                    $cachetFilename = 'VROOM-004.png';
                    break;
                default:
                    $cachetFilename = 'default.png';
            }
            
            $cachetPath = public_path("cachets/{$cachetFilename}");
        @endphp

        <div style="text-align: right;">
            Fait à Abidjan, le {{ $paiement->date_paiement->format('d/m/Y') }}
        </div>

        @if(file_exists($cachetPath))
            <div class="cachet-numerique">
                <img src="{{ $cachetPath }}" alt="Cachet numérique" style="width: 150px; height: 150px; object-fit: contain;">
            </div>
        @else
            <div class="cachet-numerique">
                <div style="width: 150px; height: 150px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #999;">
                    Cachet non trouvé
                </div>
            </div>
        @endif
    </div>
</body>
</html>