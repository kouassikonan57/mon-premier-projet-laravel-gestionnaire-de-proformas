<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionLog extends Model
{
    protected $fillable = ['user_id', 'action','model_type','model_id','changes','filiale_id', 'proforma_id', 'facture_id', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function filiale()
    {
        return $this->belongsTo(Filiale::class);
    }

    public function proforma()
    {
        return $this->belongsTo(Proforma::class, 'model_id')->where('model_type', Proforma::class);
    }

    public function facture()
    {
        return $this->belongsTo(Facture::class, 'model_id')->where('model_type', Facture::class);
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
