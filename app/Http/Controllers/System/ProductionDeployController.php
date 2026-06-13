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
   * Exécute le pipeline de déploiement (GET ou POST).
   *
   * Authentification : ?token=, X-Deployment-Token, Authorization: Bearer ou body JSON token.
   * Corps JSON optionnel (POST) : { "steps": ["migrate", "seed", "shield"] }
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
        'route' => url('/'.ltrim((string) config('deployment.route_path', '_system/run-deploy'), '/')),
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

}
