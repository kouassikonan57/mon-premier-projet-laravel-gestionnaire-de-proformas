<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Filiale;
use App\Models\FactureArticle;
use NumberFormatter;

class Facture extends Model
{
    protected $fillable = ['proforma_id','user_id', 'description', 'remise', 'bon_commande', 'acompte_pourcentage', 'montant_paye', 'reste_a_payer', 'acompte_montant', 'montant_a_payer','filiale_id','client_id', 'reference', 'date','filiale_id', 'amount',  'status'];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'tva_rate' => 'decimal:2',
        'remise' => 'decimal:2',
        'acompte_pourcentage' => 'decimal:2', // Nouveau cast
        'acompte_montant' => 'decimal:2', // Nouveau cast
        'montant_a_payer' => 'decimal:2', // Nouveau cast
        'montant_paye' => 'decimal:2', // Nouveau
        'reste_a_payer' => 'decimal:2', // Nouveau
    ];

    //relations
    public function articles()
    {
        return $this->hasMany(FactureArticle::class, 'facture_id')->withoutGlobalScopes();
    }

     public function paiements() // Nouvelle relation
    {
        return $this->hasMany(Paiement::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function proforma()
    {
        return $this->belongsTo(Proforma::class);
    }

    public function factureArticles()
    {
        return $this->hasMany(FactureArticle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function filiale()
    {
        return $this->belongsTo(Filiale::class);
    }

    public function scopeForCurrentFiliale($query)
    {
        $user = auth()->user();
        if ($user && !$user->isAdmin()) {
            $query->where('filiale_id', $user->filiale_id);
        }

        return $query;
    }

    public function getMontantEnLettresAttribute()
    {
        $totalTTC = $this->articles->sum(function ($article) {
            $ht = $article->quantity * $article->unit_price;
            $tva = $ht * 0.18;
            return $ht + $tva;
        });

        $formatter = new \NumberFormatter('fr_FR', \NumberFormatter::SPELLOUT);
        $lettres = ucfirst($formatter->format($totalTTC));

        return $lettres . ' francs CFA';
    }

    protected static function booted()
    {
        parent::booted();

        // Scope global filiale (existant)
        static::addGlobalScope('filiale', function (\Illuminate\Database\Eloquent\Builder $builder) {
            if (auth()->check() && !auth()->user()->isAdmin()) {
                $builder->where('filiale_id', auth()->user()->filiale_id);
            }
        });

        // Générer automatiquement le numéro de facture lors de la création
        // static::creating(function ($facture) {
        //     // Si le numéro est déjà défini, ne pas le changer
        //     if (!empty($facture->numero)) {
        //         return;
        //     }

        //     $prefix = 'FACTURE N°';

        //     // Récupérer le dernier ID pour avoir un numéro unique
        //     $lastId = self::max('id') ?? 0;

        //     // Générer un numéro séquentiel avec 5 chiffres (ex : 00025)
        //     $numeroUnique = str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

        //     // Code filiale (ex : S023) ; si pas de filiale, mettre S000
        //     $filialeCode = $facture->filiale->code ?? 'S000';

        //     // Année en cours
        //     $annee = now()->format('Y');

        //     // Composer le numéro complet
        //     $facture->numero = "{$prefix} {$numeroUnique} {$filialeCode}/{$annee}";
        // });
    }
}
