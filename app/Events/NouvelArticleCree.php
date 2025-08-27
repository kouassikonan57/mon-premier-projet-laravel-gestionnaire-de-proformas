<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Article;

class NouvelArticleCree implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $article;
    public $filiale;

    public function __construct(Article $article, $filiale)
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
            'count' => Article::count(),
            'article' => [
                'name' => $this->article->name,
                'reference' => $this->article->reference,
                'price' => $this->article->price,
                'category' => $this->article->category->name ?? 'N/A'
            ],
            'activity' => [
                'description' => "Nouvel article {$this->article->name} créé",
                'action' => 'Création',
                'entity_type' => 'Article'
            ]
        ];
    }
}