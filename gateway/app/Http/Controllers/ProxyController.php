<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

/**
 * Passerelle (API Gateway) : point d'entrée unique.
 * /api/auth/...       -> service Auth
 * /api/patrimoine/... -> service Patrimoine
 * /api/inventaire/... -> service Inventaire
 */
class ProxyController extends Controller
{
    public function forward(Request $request, string $service, string $path = '')
    {
        $base = config('microservices.' . $service . '_url');
        $url = rtrim($base, '/') . '/api/' . ltrim($path, '/');

        $http = Http::acceptJson()->timeout(20);

        if ($token = $request->bearerToken()) {
            $http = $http->withToken($token);
        }

        $methode = strtolower($request->method());

        try {
            if (in_array($methode, ['get', 'delete', 'head'])) {
                $reponse = $http->{$methode === 'head' ? 'get' : $methode}($url, $request->query());
            } else {
                $url .= $request->query() ? '?' . http_build_query($request->query()) : '';
                $reponse = $http->asJson()->{$methode}($url, $request->json()->all());
            }
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Service indisponible : ' . $service . '.'], 503);
        }

        $entetes = array_filter([
            'Content-Type' => $reponse->header('Content-Type'),
            'Content-Disposition' => $reponse->header('Content-Disposition'),
        ]);

        return new Response($reponse->body(), $reponse->status(), $entetes);
    }
}