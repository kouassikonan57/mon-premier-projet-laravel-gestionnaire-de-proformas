<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = [
        'facture_id', 'montant', 'date_paiement', 'mode_paiement', 
        'reference', 'notes', 'pourcentage', 'pdf_path'
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_paiement' => 'date',
        'pourcentage' => 'decimal:2',
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }
}
