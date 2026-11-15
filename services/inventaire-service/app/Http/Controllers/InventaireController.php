<?php

namespace App\Http\Controllers;

use App\Models\Inventaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class InventaireController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Inventaire::withCount('lignes')->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'libelle' => 'required|string|max:255',
            'annee' => 'required|integer|min:2000|max:2100',
        ]);

        $inventaire = Inventaire::create($data + [
            'statut' => 'en_cours',
            'cree_par' => $request->attributes->get('auth_user')['id'],
        ]);

        return response()->json($inventaire, 201);
    }

    public function show(Request $request, Inventaire $inventaire): JsonResponse
    {
        $inventaire->load('lignes');

        // Nombre total de biens, demandé au service Patrimoine, pour calculer l'avancement.
        $total = null;
        try {
            $reponse = Http::withToken($request->bearerToken())->acceptJson()->timeout(5)
                ->get(rtrim(config('microservices.patrimoine_url'), '/') . '/api/stats');
            if ($reponse->successful()) {
                $total = $reponse->json('total');
            }
        } catch (\Throwable $e) {
            // Le service Patrimoine est indisponible : on continue sans le total.
        }

        $constates = $inventaire->lignes->count();

        return response()->json(array_merge($inventaire->toArray(), [
            'total_biens' => $total,
            'progression' => $total ? (int) round($constates / $total * 100) : null,
        ]));
    }

    public function cloturer(Inventaire $inventaire): JsonResponse
    {
        if ($inventaire->statut === 'cloturee') {
            return response()->json(['message' => 'Cet inventaire est déjà clôturé.'], 422);
        }

        $inventaire->update(['statut' => 'cloturee']);

        return response()->json($inventaire);
    }
}