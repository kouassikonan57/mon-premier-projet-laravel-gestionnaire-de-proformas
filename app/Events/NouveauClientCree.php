<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Client;

class NouveauClientCree implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $client;
    public $filiale;

    public function __construct(Client $client, $filiale)
    {
        $this->client = $client;
        $this->filiale = $filiale;
    }

    public function broadcastOn()
    {
        return new Channel('admin-dashboard');
    }

    public function broadcastWith()
    {
        return [
            'count' => Client::count(),
            'activity' => [
                'description' => "Nouveau client {$this->client->name} créé",
                'action' => 'Création',
                'entity_type' => 'Client'
            ]
        ];
    }
}