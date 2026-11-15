<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vérifie le jeton en interrogeant le service Auth (GET /api/me),
 * puis contrôle le rôle si des rôles sont indiqués.
 */
class AuthViaService
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        try {
            $reponse = Http::withToken($token)
                ->acceptJson()
                ->timeout(5)
                ->get(rtrim(config('microservices.auth_url'), '/') . '/api/me');
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Service d\'authentification indisponible.'], 503);
        }

        if (! $reponse->successful()) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        $utilisateur = $reponse->json();

        if ($roles && ! in_array($utilisateur['role'] ?? null, $roles, true)) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $request->attributes->set('auth_user', $utilisateur);

        return $next($request);
    }
}