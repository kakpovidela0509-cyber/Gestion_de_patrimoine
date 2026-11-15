<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaintenanceController extends Controller
{
    private const STATUTS = ['en_cours', 'terminee', 'expertise'];

    public function index(Request $request): JsonResponse
    {
        $requete = Maintenance::with('bien:id,code,designation')->latest();

        if ($bien = $request->query('bien_id')) {
            $requete->where('bien_id', $bien);
        }

        return response()->json($requete->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'bien_id' => 'required|exists:biens,id',
            'nature' => 'required|string|max:255',
            'date_intervention' => 'nullable|date',
            'prestataire' => 'nullable|string|max:255',
            'cout' => 'nullable|numeric|min:0',
            'statut' => ['sometimes', Rule::in(self::STATUTS)],
        ]);

        return response()->json(Maintenance::create($data), 201);
    }

    public function update(Request $request, Maintenance $maintenance): JsonResponse
    {
        $data = $request->validate([
            'nature' => 'sometimes|string|max:255',
            'date_intervention' => 'nullable|date',
            'prestataire' => 'nullable|string|max:255',
            'cout' => 'nullable|numeric|min:0',
            'statut' => ['sometimes', Rule::in(self::STATUTS)],
        ]);

        $maintenance->update($data);

        return response()->json($maintenance);
    }
}