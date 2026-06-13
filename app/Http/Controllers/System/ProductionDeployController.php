<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Services\System\ProductionDeployRunner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Route HTTP sécurisée pour déployer migrations, seeders et Shield en production.
 */
class ProductionDeployController extends Controller
{
  /**
   * @param  ProductionDeployRunner  $runner  Orchestrateur de déploiement
   */
  public function __construct(
    private readonly ProductionDeployRunner $runner,
  ) {}

  /**
   * Exécute le pipeline de déploiement (POST).
   *
   * Corps JSON optionnel : { "steps": ["migrate", "seed", "shield"] }
   * En-tête requis : X-Deployment-Token ou Authorization: Bearer {token}
   */
  public function invoke(Request $request): JsonResponse
  {
    try {
      $steps = $request->input('steps');

      if (is_string($steps)) {
        $steps = array_map('trim', explode(',', $steps));
      }

      $result = $this->runner->run(
        steps: is_array($steps) ? $steps : null,
        user: null,
      );

      return response()->json([
        'success' => $result['success'],
        'message' => $result['success']
          ? 'Déploiement production terminé avec succès.'
          : 'Déploiement interrompu : une étape a échoué.',
        'steps' => $result['steps'],
        'available_steps' => ProductionDeployRunner::STEPS,
      ], $result['success'] ? 200 : 500);
    } catch (InvalidArgumentException $exception) {
      return response()->json([
        'success' => false,
        'message' => $exception->getMessage(),
        'available_steps' => ProductionDeployRunner::STEPS,
      ], 422);
    }
  }

  /**
   * Retourne la documentation de la route (GET, jeton requis).
   */
  public function info(): JsonResponse
  {
    return response()->json([
      'route' => url('/'.ltrim(config('deployment.route_path', 'deploy/production'), '/')),
      'method' => 'POST',
      'headers' => [
        'X-Deployment-Token' => 'Jeton défini dans DEPLOYMENT_TOKEN',
        'Accept' => 'application/json',
      ],
      'body' => [
        'steps' => 'Optionnel — tableau ou liste CSV : storage, migrate, seed, shield',
      ],
      'default_steps' => ProductionDeployRunner::STEPS,
      'production_seeder' => config('deployment.production_seeder_key'),
    ]);
  }
}
