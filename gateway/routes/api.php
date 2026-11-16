<?php

use App\Http\Controllers\ProxyController;
use Illuminate\Support\Facades\Route;

Route::any('/{service}/{path?}', [ProxyController::class, 'forward'])
    ->where('service', 'auth|patrimoine|inventaire')
    ->where('path', '.*');