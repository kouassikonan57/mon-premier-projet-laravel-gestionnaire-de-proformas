<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactureArticle extends Model
{
    protected $table = 'facture_articles';

    protected $fillable = [
        'facture_id',
        'designation',
        'quantity',
        'unit_price',
        'total',
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    public static function bootBelongsToFiliale()
    {
        static::addGlobalScope('filiale', function ($builder) {
            if (auth()->check() && !auth()->user()->isAdmin()) {
                $builder->where('filiale_id', auth()->user()->filiale_id);
            }
        });
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
