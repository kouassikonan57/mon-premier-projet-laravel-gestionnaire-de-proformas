<?php
namespace App\Observers;

use App\Models\Proforma;
use App\Models\ActivityLog;
use App\Models\ActionLog;

class ProformaObserver
{
    public function updating(Proforma $proforma)
    {
        // Vérifie si le statut change
        if ($proforma->isDirty('status')) {
            $old = $proforma->getOriginal('status');
            $new = $proforma->status;

            // Historique global
            ActivityLog::create([
                'action' => 'Changement de statut',
                'entity_type' => 'Proforma',
                'entity_id' => $proforma->id,
                'description' => "La proforma #{$proforma->id} est passée de « {$old} » à « {$new} ».",
            ]);

            // Journal des actions
            ActionLog::create([
                'user_id' => null, // ou Auth::id() plus tard
                'action' => 'Modification statut proforma',
                'proforma_id' => $proforma->id,
                'description' => "Statut modifié de « {$old} » à « {$new} » pour la proforma #{$proforma->id}.",
            ]);
        }
    }
}
