<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use App\Models\Mouvement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MouvementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $requete = Mouvement::with('bien:id,code,designation', 'origine', 'destination')->latest();

        if ($statut = $request->query('statut')) {
            $requete->where('statut', $statut);
        }

        return response()->json($requete->paginate(15));
    }

    // Demande d'affectation, de transfert ou de sortie : elle attend une validation.
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'bien_id' => 'required|exists:biens,id',
            'type' => ['required', Rule::in(['affectation', 'transfert', 'sortie'])],
            'service_destination_id' => 'required_unless:type,sortie|nullable|exists:services,id',
            'motif' => 'nullable|string|max:500',
        ]);

        $bien = Bien::findOrFail($data['bien_id']);

        $mouvement = Mouvement::create([
            'bien_id' => $bien->id,
            'type' => $data['type'],
            'origine_id' => $bien->service_id,
            'destination_id' => $data['service_destination_id'] ?? null,
            'motif' => $data['motif'] ?? null,
            'statut' => 'en_attente',
            'demandeur_id' => $request->attributes->get('auth_user')['id'],
        ]);

        return response()->json($mouvement->load('bien:id,code,designation', 'origine', 'destination'), 201);
    }

    public function valider(Request $request, Mouvement $mouvement): JsonResponse
    {
        if ($mouvement->statut !== 'en_attente') {
            return response()->json(['message' => 'Cette demande a déjà été traitée.'], 422);
        }

        $bien = $mouvement->bien;

        if ($mouvement->type === 'sortie') {
            $bien->update(['statut' => 'sorti']);
        } else {
            $bien->update(['service_id' => $mouvement->destination_id]);
        }

        $mouvement->update([
            'statut' => 'valide',
            'valide_par' => $request->attributes->get('auth_user')['id'],
        ]);

        return response()->json($mouvement->load('bien:id,code,designation', 'origine', 'destination'));
    }

    public function refuser(Request $request, Mouvement $mouvement): JsonResponse
    {
        if ($mouvement->statut !== 'en_attente') {
            return response()->json(['message' => 'Cette demande a déjà été traitée.'], 422);
        }

        $mouvement->update([
            'statut' => 'refuse',
            'valide_par' => $request->attributes->get('auth_user')['id'],
        ]);

        return response()->json($mouvement);
    }
}