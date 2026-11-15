<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use App\Models\Mouvement;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $actifs = fn () => Bien::where('statut', 'actif');

        return response()->json([
            'total' => $actifs()->count(),
            'superficie_totale' => (float) $actifs()->sum('superficie'),
            'a_rehabiliter' => $actifs()->whereIn('etat', ['a_rehabiliter', 'degrade'])->count(),
            'par_type' => $actifs()->selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type'),
            'derniers_mouvements' => Mouvement::with('bien:id,code,designation')->latest()->limit(4)->get(),
        ]);
    }
}