<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Proforma;

class NouvelleProformaCree implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $proforma;
    public $filiale;

    public function __construct(Proforma $proforma, $filiale)
    {
        $this->proforma = $proforma;
        $this->filiale = $filiale;
    }

    public function broadcastOn()
    {
        // Diffusion sur le canal admin-dashboard
        return new Channel('admin-dashboard');
    }

    public function broadcastWith()
    {
        // Données à envoyer aux clients
        return [
            'count' => Proforma::count(), // Le nouveau total de proformas
            'proforma' => [
                'reference' => $this->proforma->reference,
                'client_name' => $this->proforma->client->name ?? 'N/A',
                'amount' => $this->proforma->amount
            ],
            'activity' => [
                'description' => "Nouvelle proforma #{$this->proforma->reference} créée",
                'action' => 'Création',
                'entity_type' => 'Proforma'
            ]
        ];
    }
}