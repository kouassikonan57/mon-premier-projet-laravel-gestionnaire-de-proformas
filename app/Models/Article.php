<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CatalogArticle;

class Article extends Model
{
    use HasFactory;

    // ✅ "total", et PAS "total_price" ni "name"
    protected $fillable = ['proforma_id', 'facture_id', 'designation', 'quantity', 'unit_price', 'total'];

    //relations
    public function proforma()
    {
        return $this->belongsTo(Proforma::class);
    }

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
}
