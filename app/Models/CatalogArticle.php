<?php

namespace App\Models;
use App\Models\Filiale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CatalogArticle extends Model
{
    protected $fillable = ['name', 'default_price', 'filiale_id'];

    public function filiale()
    {
        return $this->belongsTo(Filiale::class);
    }

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
