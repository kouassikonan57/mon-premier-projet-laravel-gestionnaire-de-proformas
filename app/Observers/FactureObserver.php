<?php

namespace App\Observers;

use App\Models\Facture;
use App\Models\ActivityLog;
use App\Models\ActionLog;

class FactureObserver
{
    public function updating(Facture $facture)
    {
        if ($facture->isDirty('status')) {
            $old = $facture->getOriginal('status');
            $new = $facture->status;

            ActivityLog::create([
                'action' => 'Changement de statut',
                'entity_type' => 'Facture',
                'entity_id' => $facture->id,
                'description' => "La facture {$facture->reference} est passée de « {$old} » à « {$new} ».",
            ]);

            ActionLog::create([
                'user_id' => null,
                'action' => 'Modification statut facture',
                'proforma_id' => null,
                'description' => "Statut modifié de « {$old} » à « {$new} » pour la facture {$facture->reference}",
            ]);
        }
    }
}
