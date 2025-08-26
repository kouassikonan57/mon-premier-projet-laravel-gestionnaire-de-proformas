<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'action',
        'entity_type',
        'entity_id',
        'description',
        'user_id',
        'filiale_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function filiale()
    {
        return $this->belongsTo(Filiale::class);
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

