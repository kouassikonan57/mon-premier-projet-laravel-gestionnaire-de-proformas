<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proforma extends Model
{
    use HasFactory;

    // ✅ Ajout de 'amount' dans le tableau fillable
    protected $fillable = [
        'user_id',
        'client_id',
        'reference',
        'date',
        'description', 
        'remise', 
        'notes',
        'amount',
        'status', // ✅ ici
        'tva_rate',
        'filiale_id',
    ];

    protected static function booted()
{
    static::addGlobalScope('filiale', function ($query) {
        $filialeId = session('active_filiale_id'); // Ex: choisie par l'admin dans un dropdown

        if ($filialeId) {
            $query->where('filiale_id', $filialeId);
        } elseif (auth()->check() && !auth()->user()->isAdmin()) {
            $query->where('filiale_id', auth()->user()->filiale_id);
        }
    });
}
    
    protected $casts = [
        'date' => 'datetime',
    ];

    // Relations
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function filiale()
    {
        return $this->belongsTo(Filiale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForCurrentFiliale($query)
    {
        $user = auth()->user();
        if ($user && !$user->isAdmin()) {
            $query->where('filiale_id', $user->filiale_id);
        }

        return $query;
    }

}
