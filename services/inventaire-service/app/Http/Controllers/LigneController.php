<?php

namespace App\Http\Controllers;

use App\Models\Inventaire;
use App\Models\InventaireLigne;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class LigneController extends Controller
{
    private const RESULTATS = ['present' => 'conforme', 'degrade' => 'a_verifier', 'absent' => 'ecart'];

    // Enregistre (ou met à jour) le constat d'un bien dans une campagne.
    public function store(Request $request, Inventaire $inventaire): JsonResponse
    {
        if ($inventaire->statut === 'cloturee') {
            return response()->json(['message' => 'Cet inventaire est clôturé.'], 422);
        }

        $data = $request->validate([
            'bien_id' => 'required|integer',
            'constat' => ['required', Rule::in(array_keys(self::RESULTATS))],
            'commentaire' => 'nullable|string|max:500',
        ]);

        try {
            $reponse = Http::withToken($request->bearerToken())->acceptJson()->timeout(5)
                ->get(rtrim(config('microservices.patrimoine_url'), '/') . '/api/biens/' . $data['bien_id']);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Service Patrimoine indisponible.'], 503);
        }

        if (! $reponse->successful()) {
            return response()->json(['message' => 'Bien introuvable.'], 422);
        }

        $bien = $reponse->json();

        $ligne = InventaireLigne::updateOrCreate(
            ['inventaire_id' => $inventaire->id, 'bien_id' => $data['bien_id']],
            [
                'bien_code' => $bien['code'] ?? null,
                'bien_designation' => $bien['designation'] ?? null,
                'constat' => $data['constat'],
                'resultat' => self::RESULTATS[$data['constat']],
                'commentaire' => $data['commentaire'] ?? null,
            ]
        );

        return response()->json($ligne, 201);
    }
}