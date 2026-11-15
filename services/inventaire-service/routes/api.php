<?php

use App\Http\Controllers\InventaireController;
use App\Http\Controllers\LigneController;
use App\Http\Controllers\RapportController;
use App\Http\Middleware\AuthViaService;
use Illuminate\Support\Facades\Route;

// Lecture : tout utilisateur connecté
Route::middleware(AuthViaService::class)->group(function () {
    Route::get('/inventaires', [InventaireController::class, 'index']);
    Route::get('/inventaires/{inventaire}', [InventaireController::class, 'show']);
    Route::get('/rapports/{type}', [RapportController::class, 'show']);
});

// Constats sur le terrain
Route::middleware(AuthViaService::class . ':admin,gestionnaire,controleur')->group(function () {
    Route::post('/inventaires/{inventaire}/lignes', [LigneController::class, 'store']);
});

// Lancement et clôture d'une campagne
Route::middleware(AuthViaService::class . ':admin,gestionnaire')->group(function () {
    Route::post('/inventaires', [InventaireController::class, 'store']);
    Route::post('/inventaires/{inventaire}/cloturer', [InventaireController::class, 'cloturer']);
});