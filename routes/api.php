<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Ici, vous pouvez enregistrer les routes d'API pour votre application.
| Ces routes sont automatiquement préfixées par "api".
|
*/

Route::middleware('api')->get('/test', function () {
    return response()->json(['message' => 'API ok']);
});
