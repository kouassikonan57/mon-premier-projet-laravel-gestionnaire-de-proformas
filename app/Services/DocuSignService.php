<?php
namespace App\Services;

use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Configuration;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\EnvelopeDefinition;
use App\Services\DocuSignAuthService;
use DocuSign\eSign\Model\RecipientViewRequest;
use Illuminate\Support\Facades\View;
use DocuSign\eSign\Client\Auth\OAuth;

use PDF;

class DocuSignService
{
    protected $apiClient;
    protected $accountId;
    protected $basePath;

   public function __construct(DocuSignAuthService $authService)
    {
        $this->basePath = env('DOCUSIGN_BASE_PATH', 'https://demo.docusign.net/restapi');
        $this->accountId = env('DOCUSIGN_ACCOUNT_ID');
        $oauthToken = $authService->getAccessToken(); // 🔑 Récupération via ton service JWT

        $config = new Configuration();
        $config->setHost($this->basePath);
        $config->addDefaultHeader("Authorization", "Bearer $oauthToken");

        $this->apiClient = new ApiClient($config);
    }

    /**
     * Envoie un document à signer.
     * 
     * @param string $signerEmail
     * @param string $signerName
     * @param string $documentPath  Chemin complet vers le PDF
     * @return array Réponse DocuSign (ex: envelopeId)
     */
    public function sendProformaToSign(Proforma $proforma)
    {
        // 1. Générer le PDF
        $pdf = PDF::loadView('proformas.pdf', compact('proforma'))->output();
        $base64Pdf = base64_encode($pdf);

        // 2. Créer un document DocuSign
        $document = new Document([
            'document_base64' => $base64Pdf,
            'name' => 'Proforma '.$proforma->reference,
            'file_extension' => 'pdf',
            'document_id' => '1',
        ]);

        // 3. Signataire
        $signer = new Signer([
            'email' => $proforma->client->email,
            'name' => $proforma->client->name,
            'recipient_id' => '1',
            'routing_order' => '1',
            'client_user_id' => '1234', // Required for embedded signing
        ]);

        // 4. Où signer dans le document ?
        $signHere = new SignHere([
            'document_id' => '1',
            'page_number' => '1',
            'recipient_id' => '1',
            'tab_label' => 'SignHereTab',
            'x_position' => '400',
            'y_position' => '600',
        ]);

        $tabs = new Tabs([
            'sign_here_tabs' => [$signHere],
        ]);
        $signer->setTabs($tabs);

        // 5. Enveloppe
        $envelopeDefinition = new EnvelopeDefinition([
            'email_subject' => "Signature de la proforma {$proforma->reference}",
            'documents' => [$document],
            'recipients' => new Recipients(['signers' => [$signer]]),
            'status' => 'sent',
        ]);

        // 6. Envoi de l'enveloppe
        $envelopesApi = new EnvelopesApi($this->apiClient);
        $envelopeSummary = $envelopesApi->createEnvelope($this->accountId, $envelopeDefinition);
        $envelopeId = $envelopeSummary->getEnvelopeId();

        // 7. Générer l'URL de signature intégrée
        $recipientViewRequest = new RecipientViewRequest([
            'authentication_method' => 'none',
            'client_user_id' => '1234',
            'recipient_id' => '1',
            'return_url' => route('proformas.index'), // Après signature, rediriger ici
            'user_name' => $proforma->client->name,
            'email' => $proforma->client->email,
        ]);

        $viewUrl = $envelopesApi->createRecipientView($this->accountId, $envelopeId, $recipientViewRequest);

        return $viewUrl->getUrl(); // 🔁 on renvoie l'URL de signature
    }
}
