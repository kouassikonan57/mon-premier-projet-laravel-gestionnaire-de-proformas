<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filiale extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'code', 'description', 'logo_path', 'footer_text'];

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function proformas()
    {
        return $this->hasMany(Proforma::class);
    }

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
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