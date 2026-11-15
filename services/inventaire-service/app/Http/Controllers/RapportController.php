<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class RapportController extends Controller
{
    private const TYPES = ['inventaire-general', 'biens-par-service', 'a-rehabiliter'];

    // GET /api/rapports/{type}?format=json|csv
    public function show(Request $request, string $type): JsonResponse|Response
    {
        if (! in_array($type, self::TYPES, true)) {
            return response()->json(['message' => 'Rapport inconnu.'], 404);
        }

        try {
            $reponse = Http::withToken($request->bearerToken())->acceptJson()->timeout(10)
                ->get(rtrim(config('microservices.patrimoine_url'), '/') . '/api/biens', ['per_page' => 500]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Service Patrimoine indisponible.'], 503);
        }

        if (! $reponse->successful()) {
            return response()->json(['message' => 'Impossible de lire les biens.'], 502);
        }

        $biens = collect($reponse->json('data', []));

        if ($type === 'a-rehabiliter') {
            $biens = $biens->whereIn('etat', ['a_rehabiliter', 'degrade']);
        }

        $lignes = $biens->map(fn ($b) => [
            'code' => $b['code'] ?? '',
            'designation' => $b['designation'] ?? '',
            'type' => $b['type'] ?? '',
            'adresse' => $b['adresse'] ?? '',
            'superficie' => $b['superficie'] ?? '',
            'etat' => $b['etat'] ?? '',
            'service' => $b['service']['nom'] ?? 'Non affecté',
            'valeur' => $b['valeur'] ?? '',
        ]);

        if ($type === 'biens-par-service') {
            $lignes = $lignes->sortBy('service');
        }

        $lignes = $lignes->values();

        if ($request->query('format') === 'csv') {
            return $this->csv($type, $lignes->all());
        }

        return response()->json(['rapport' => $type, 'total' => $lignes->count(), 'lignes' => $lignes]);
    }

    private function csv(string $type, array $lignes): Response
    {
        $sortie = "\xEF\xBB\xBF" . implode(';', ['Code', 'Désignation', 'Type', 'Adresse', 'Superficie (m²)', 'État', 'Service', 'Valeur (FCFA)']) . "\n";

        foreach ($lignes as $l) {
            $sortie .= implode(';', array_map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', $l)) . "\n";
        }

        return response($sortie, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="rapport-' . $type . '.csv"',
        ]);
    }
}