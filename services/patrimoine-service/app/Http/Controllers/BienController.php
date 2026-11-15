<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use App\Models\Mouvement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BienController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $requete = Bien::with('service')->where('statut', $request->query('statut', 'actif'));

        if ($q = $request->query('q')) {
            $requete->where(function ($sous) use ($q) {
                $motif = '%' . strtolower($q) . '%';
                $sous->whereRaw('LOWER(designation) LIKE ?', [$motif])
                     ->orWhereRaw('LOWER(code) LIKE ?', [$motif]);
            });
        }

        foreach (['type', 'etat', 'service_id'] as $filtre) {
            if ($valeur = $request->query($filtre)) {
                $requete->where($filtre, $valeur);
            }
        }

        return response()->json($requete->orderBy('code')->paginate(min((int) $request->query('per_page', 15), 500)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->valider($request);

        $bien = Bien::create($data);
        $bien->update(['code' => 'IMM-' . str_pad((string) $bien->id, 4, '0', STR_PAD_LEFT)]);

        Mouvement::create([
            'bien_id' => $bien->id,
            'type' => 'acquisition',
            'destination_id' => $bien->service_id,
            'motif' => 'Enregistrement du bien',
            'statut' => 'valide',
            'demandeur_id' => $request->attributes->get('auth_user')['id'],
        ]);

        return response()->json($bien->fresh('service'), 201);
    }

    public function show(Bien $bien): JsonResponse
    {
        return response()->json($bien->load('service', 'mouvements.origine', 'mouvements.destination', 'maintenances'));
    }

    public function update(Request $request, Bien $bien): JsonResponse
    {
        $bien->update($this->valider($request, true));

        return response()->json($bien->fresh('service'));
    }

    // Un bien n'est jamais supprimé : il est archivé.
    public function destroy(Bien $bien): JsonResponse
    {
        $bien->update(['statut' => 'archive']);

        return response()->json(['message' => 'Bien archivé.']);
    }

    private function valider(Request $request, bool $partiel = false): array
    {
        $obligatoire = $partiel ? 'sometimes' : 'required';

        return $request->validate([
            'designation' => [$obligatoire, 'string', 'max:255'],
            'type' => [$obligatoire, Rule::in(Bien::TYPES)],
            'adresse' => [$obligatoire, 'string', 'max:255'],
            'superficie' => 'nullable|numeric|min:0',
            'date_acquisition' => 'nullable|date',
            'valeur' => 'nullable|numeric|min:0',
            'titre_propriete' => 'nullable|string|max:255',
            'etat' => ['sometimes', Rule::in(Bien::ETATS)],
            'service_id' => 'nullable|exists:services,id',
        ]);
    }
}