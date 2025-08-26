<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class WebhookController extends Controller
{
    public function handleEsignature(Request $request)
    {
        $data = $request->all();

        if ($data['event'] === 'contract_signed') {
            $contractId = $data['contract_id'];
            $documentUrl = $data['contract_pdf_url'];
            $signerEmail = $data['signer_email'];

            // Nom du fichier
            $fileName = 'signed_contracts/' . $contractId . '_' . time() . '.pdf';

            // Télécharger le fichier
            $pdfContent = Http::get($documentUrl)->body();

            // Stocker dans storage/app/signed_contracts/
            Storage::put($fileName, $pdfContent);

            // Optionnel : enregistrer en base de données
            // SignedDocument::create([...]);

            return response('PDF reçu', 200);
        }

        return response('Aucun traitement effectué.', 200);
    }
}

