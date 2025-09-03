<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\CatalogArticle;

class NouvelArticleCree implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CatalogArticle $article;
    public $filiale;

    public function __construct(CatalogArticle $article, $filiale)
    {
        $this->article = $article;
        $this->filiale = $filiale;
    }

    public function broadcastOn()
    {
        return new Channel('admin-dashboard');
    }

    public function broadcastWith()
    {
        return [
            'count' => CatalogArticle::count(),
            'article' => [
                'name' => $this->article->name,
                'price' => $this->article->default_price,
                // 'reference' et 'category' ne sont peut-être pas définis sur CatalogArticle
                'reference' => $this->article->reference ?? 'N/A',
                'category' => $this->article->category->name ?? 'N/A'
            ],
            'activity' => [
                'description' => "Nouvel article {$this->article->name} créé",
                'action' => 'Création',
                'entity_type' => 'CatalogArticle'
            ]
        ];
    }
}
