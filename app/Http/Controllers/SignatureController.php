<?php

namespace App\Http\Controllers;

use App\Models\Proforma;
use App\Services\DocuSignService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class SignatureController extends Controller
{
    public function sendForSignature(Proforma $proforma)
    {
        // Génère le PDF localement
        $pdf = \PDF::loadView('proformas.pdf', compact('proforma'));
        $pdfPath = storage_path('app/proformas/proforma_' . $proforma->reference . '.pdf');
        Storage::put('proformas/proforma_' . $proforma->reference . '.pdf', $pdf->output());

        // Appelle le service DocuSign
        $docuSign = new DocuSignService();

        try {
            $response = $docuSign->sendDocumentForSignature(
                $pdfPath,
                $proforma->client->name,
                $proforma->client->email // Assure-toi que l'email du client existe
            );

            return back()->with('success', 'Proforma envoyée à la signature !');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l’envoi : ' . $e->getMessage());
        }
    }
}
