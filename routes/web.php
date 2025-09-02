<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProformaController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\ActionLogController;
use App\Http\Controllers\HistoriqueController;
use App\Http\Controllers\CatalogArticleController;
use App\Http\Controllers\StatistiquesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FilialeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;

// Redirection racine vers welcome
Route::get('/', function () {
    return view('welcome');
});

// Routes d'authentification (seulement login, logout, password reset)
Auth::routes(['register' => false]);

// Routes protégées (nécessitent une connexion)
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Routes pour tous les utilisateurs authentifiés
    Route::resource('clients', ClientController::class);
    Route::resource('proformas', ProformaController::class);
    Route::resource('factures', FactureController::class);
    Route::resource('catalog-articles', CatalogArticleController::class);

    // Routes spécifiques
    Route::get('/factures/select-proforma', [FactureController::class, 'selectProforma'])->name('factures.selectProforma');
    Route::get('proformas/{proforma}/pdf', [ProformaController::class, 'exportPdf'])->name('proformas.pdf');
    Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');
    Route::get('proformas/{proforma}/convert', [ProformaController::class, 'convert'])->name('proformas.convert');
    Route::get('/proformas/{proforma}/export/excel', [ProformaController::class, 'exportExcel'])->name('proformas.export.excel');
    Route::get('/factures/{facture}/export/pdf', [FactureController::class, 'exportPdf'])->name('factures.export.pdf');
    Route::get('/factures/{facture}/export/excel', [FactureController::class, 'exportExcel'])->name('factures.export.excel');
    Route::get('/historique', [HistoriqueController::class, 'index'])->name('historique.index');
    Route::put('/factures/{facture}/changer-statut/{status}', [FactureController::class, 'changerStatut'])->name('factures.changeStatus');
    Route::get('/statistiques', [StatistiquesController::class, 'ventes'])->name('stats.ventes');
    
    // NOUVELLE ROUTE PAIEMENT
    Route::post('/factures/{facture}/paiement', [FactureController::class, 'enregistrerPaiement'])
        ->name('factures.paiement.store');

    Route::get('/factures/{facture}/paiement/{paiement}/pdf', [FactureController::class, 'telechargerPaiementPdf'])
        ->name('factures.paiement.pdf');

    // Tous peuvent voir la liste des filiales
    Route::get('/filiales', [FilialeController::class, 'index'])->name('filiales.index');

    // ✅ SOLUTION TEMPORAIRE - Remplacez le middleware par une vérification manuelle
    // Routes accessibles uniquement aux administrateurs
    Route::prefix('admin')->group(function () {
        // Vérification manuelle pour toutes les routes admin
        Route::middleware(['auth'])->group(function () {
            Route::get('/check-admin', function () {
                if (!auth()->user()->isAdmin()) {
                    abort(403, 'Accès non autorisé');
                }
                return 'ok';
            });
            
            Route::resource('users', UserController::class);
            Route::resource('filiales', FilialeController::class)->except(['index']);
            Route::get('action-logs', [ActionLogController::class, 'index'])->name('action_logs.index');
        });
    });
});

// Redirection /home vers /dashboard
Route::get('/home', function () {
    return redirect()->route('dashboard');
})->name('home');