<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class seller
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Récupérer le token depuis l'entête 'Authorization'
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        // 2. Vérifier si le token est valide avec Sanctum
        $personalAccessToken = PersonalAccessToken::findToken($token);

        if (!$personalAccessToken) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        // 3. Récupérer l'utilisateur associé au token
        $user = $personalAccessToken->tokenable;

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // 4. Vérifier si l'utilisateur est admin
        if (!$user["status"] === "seller") {
            return response()->json(['error' => 'Unauthorized: Admin access required'], 403);
        }

        // 5. Ajouter l'utilisateur à la requête (facultatif)
        $request->merge(['user' => $user]);

        // 6. Continuer la chaîne de middlewares
        return $next($request);
    }
}
