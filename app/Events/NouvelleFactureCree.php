<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Facture;

class NouvelleFactureCree implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $facture;
    public $filiale;

    public function __construct(Facture $facture, $filiale)
    {
        $this->facture = $facture;
        $this->filiale = $filiale;
    }

    public function broadcastOn()
    {
        return new Channel('admin-dashboard');
    }

    public function broadcastWith()
    {
        return [
            'count' => Facture::count(),
            'facture' => [
                'reference' => $this->facture->reference,
                'client_name' => $this->facture->client->name ?? 'N/A',
                'amount' => $this->facture->amount,
                'status' => $this->facture->status
            ],
            'activity' => [
                'description' => "Nouvelle facture #{$this->facture->reference} créée",
                'action' => 'Création',
                'entity_type' => 'Facture'
            ]
        ];
    }
}