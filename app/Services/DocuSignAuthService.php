<?php

namespace App\Services;

use DocuSign\eSign\Configuration;
use DocuSign\eSign\Client\ApiClient;
use Illuminate\Support\Facades\Cache;
use Exception;

class DocuSignAuthService
{
    public function getApiClient(): ApiClient
    {
        $config = new Configuration();
        $config->setHost("https://demo.docusign.net/restapi");

        $apiClient = new ApiClient($config);
        $apiClient->getOAuth()->setOAuthBasePath('account-d.docusign.com');

        return $apiClient;
    }

    public function getAccessToken()
    {
        return Cache::remember('docusign_access_token', 55 * 60, function () {
            $integrationKey     = env('DOCUSIGN_INTEGRATION_KEY');
            $impersonatedUserId = env('DOCUSIGN_USER_ID');
            $authServer         = 'account-d.docusign.com';

            $privateKeyPath = storage_path('app/docusign/private_rsa.key');

            if (!file_exists($privateKeyPath)) {
                throw new Exception("Fichier de clé privée introuvable : {$privateKeyPath}");
            }

            $privateKeyContent = file_get_contents($privateKeyPath);

            if (strpos($privateKeyContent, '-----BEGIN ') !== 0) {
                throw new Exception("Clé privée invalide ou mal formatée");
            }

            $config = new Configuration();
            $config->setHost("https://demo.docusign.net/restapi");

            $apiClient = new ApiClient($config);
            $apiClient->getOAuth()->setOAuthBasePath($authServer);

            $scopes = ['signature', 'impersonation'];

            try {
                $response = $apiClient->requestJWTUserToken(
                    $integrationKey,
                    $impersonatedUserId,
                    $scopes,
                    $privateKeyContent,
                    3600
                );

                return $response[0]->getAccessToken();
            } catch (Exception $e) {
                throw new Exception("Erreur d'obtention du token JWT : " . $e->getMessage());
            }
        });
    }
}
