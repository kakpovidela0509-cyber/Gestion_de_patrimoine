<?php

use Illuminate\Support\Facades\Route;

// Interface web (page unique)
Route::get('/', fn () => response()->file(public_path('app.html')));