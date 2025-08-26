<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProformaArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'proforma_id',
        'designation',
        'quantite',
        'prix_unitaire',
    ];

    public function proforma()
    {
        return $this->belongsTo(Proforma::class);
    }

    public static function bootBelongsToFiliale()
    {
        static::addGlobalScope('filiale', function ($builder) {
            if (auth()->check() && !auth()->user()->isAdmin()) {
                $builder->where('filiale_id', auth()->user()->filiale_id);
            }
        });
    }

}
