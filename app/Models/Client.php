<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Proforma; // <-- Import important
use App\Models\Filiale;

class Client extends Model
{
    use HasFactory;

    // Autoriser le remplissage de ces champs
    protected $fillable = [
        'name', 'responsable', 'email', 'phone', 'address', 'rccm', 'filiale_id'
    ];

    // Relation : un client peut avoir plusieurs proformas
    public function proformas()
    {
        return $this->hasMany(Proforma::class);
    }

    // Relation : facture
    public function factures()
    {
        return $this->hasMany(\App\Models\Facture::class);
    }

    // Relation filiale
    public function filiale()
    {
        return $this->belongsTo(Filiale::class);
    }

    // Dans app/Models/Client.php
    public function scopeForCurrentFiliale($query)
    {
        if (auth()->check() && !auth()->user()->isAdmin()) {
            return $query->where('filiale_id', auth()->user()->filiale_id);
        }
        return $query;
    }

    protected static function booted()
    {
        static::addGlobalScope('filiale', function ($query) {
            if (auth()->check() && !auth()->user()->isAdmin()) {
                $query->where('filiale_id', auth()->user()->filiale_id);
            }
        });
    }
}
