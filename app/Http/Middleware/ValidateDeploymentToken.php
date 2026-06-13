<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vérifie le jeton secret pour les routes de déploiement HTTP.
 */
class ValidateDeploymentToken
{
  /**
   * @param  Request  $request  Requête entrante
   * @param  Closure(Request): Response  $next  Suite du pipeline
   */
  public function handle(Request $request, Closure $next): Response
  {
    $configuredToken = config('deployment.token');

    if (! is_string($configuredToken) || $configuredToken === '') {
      return response()->json([
        'success' => false,
        'message' => 'Route de déploiement désactivée : DEPLOYMENT_TOKEN non configuré.',
      ], 503);
    }

    $providedToken = $this->resolveToken($request);

    if ($providedToken === null || ! hash_equals($configuredToken, $providedToken)) {
      return response()->json([
        'success' => false,
        'message' => 'Jeton de déploiement invalide ou absent.',
      ], 401);
    }

    return $next($request);
  }

  /**
   * Extrait le jeton depuis les en-têtes ou le corps JSON.
   */
  private function resolveToken(Request $request): ?string
  {
    $headerToken = $request->header('X-Deployment-Token');

    if (is_string($headerToken) && $headerToken !== '') {
      return $headerToken;
    }

    $authorization = $request->header('Authorization');

    if (is_string($authorization) && str_starts_with($authorization, 'Bearer ')) {
      return trim(substr($authorization, 7));
    }

    $bodyToken = $request->input('token');

    if (is_string($bodyToken) && $bodyToken !== '') {
      return $bodyToken;
    }

    return null;
  }
}
