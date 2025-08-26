<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class YousignApiLaravel
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.yousign.api_url', 'https://api.yousign.app/v3');
        $this->apiKey = config('services.yousign.api_key');
    }

    protected function headers()
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    public function createProcedure(array $data)
    {
        $response = Http::withHeaders($this->headers())
            ->post("{$this->apiUrl}/procedures", [
                'name' => $data['name'],
                'description' => $data['description'],
                'ordered' => true,
                'start' => false,
            ]);

        return $response->json();
    }

    public function addFile($filePath, $procedureId)
    {
        $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->attach('file', file_get_contents($filePath), basename($filePath))
            ->post("{$this->apiUrl}/files", [
                'procedure' => $procedureId,
            ]);

        return $response->json();
    }

    public function addMember(array $data, $procedureId)
    {
        $response = Http::withHeaders($this->headers())
            ->post("{$this->apiUrl}/members", [
                'procedure' => $procedureId,
                'info' => [
                    'firstname' => $data['firstname'],
                    'lastname' => $data['lastname'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                ],
                'fileObjects' => $data['fileObjects'],
            ]);

        return $response->json();
    }

    public function launchProcedure($procedureId)
    {
        $response = Http::withHeaders($this->headers())
            ->post("{$this->apiUrl}/procedures/{$procedureId}/start");

        return $response->json();
    }
}
