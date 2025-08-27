<?php

use Illuminate\Support\Facades\Broadcast;

// Canal public pour les statistiques (accessible à tous les utilisateurs authentifiés)
Broadcast::channel('admin-dashboard', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'isAdmin' => $user->isAdmin()
    ];
});

// Canal privé par filiale (si vous voulez séparer par filiale)
Broadcast::channel('filiale.{filialeId}', function ($user, $filialeId) {
    return (int) $user->filiale_id === (int) $filialeId || $user->isAdmin();
});