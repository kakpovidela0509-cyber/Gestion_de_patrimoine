<?php

use App\Http\Controllers\BienController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MouvementController;
use App\Http\Controllers\ServiceController;
use App\Http\Middleware\AuthViaService;
use Illuminate\Support\Facades\Route;

// Lecture : tout utilisateur connecté
Route::middleware(AuthViaService::class)->group(function () {
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/stats', [DashboardController::class, 'stats']);
    Route::get('/biens', [BienController::class, 'index']);
    Route::get('/biens/{bien}', [BienController::class, 'show']);
    Route::get('/mouvements', [MouvementController::class, 'index']);
    Route::get('/maintenances', [MaintenanceController::class, 'index']);
});

// Gestion des biens et de la maintenance : admin, gestionnaire
Route::middleware(AuthViaService::class . ':admin,gestionnaire')->group(function () {
    Route::post('/biens', [BienController::class, 'store']);
    Route::put('/biens/{bien}', [BienController::class, 'update']);
    Route::delete('/biens/{bien}', [BienController::class, 'destroy']);
    Route::post('/maintenances', [MaintenanceController::class, 'store']);
    Route::put('/maintenances/{maintenance}', [MaintenanceController::class, 'update']);
});

// Demande d'affectation, de transfert ou de sortie
Route::middleware(AuthViaService::class . ':admin,gestionnaire,responsable')->group(function () {
    Route::post('/mouvements', [MouvementController::class, 'store']);
});

// Validation des demandes : admin, direction
Route::middleware(AuthViaService::class . ':admin,direction')->group(function () {
    Route::post('/mouvements/{mouvement}/valider', [MouvementController::class, 'valider']);
    Route::post('/mouvements/{mouvement}/refuser', [MouvementController::class, 'refuser']);
});