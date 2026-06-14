<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Services\System\SuperAdminBootstrapService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

/**
 * Route HTTP sécurisée pour initialiser le super administrateur et Shield.
 */
class SuperAdminBootstrapController extends Controller
{
  /**
   * @param  SuperAdminBootstrapService  $bootstrapService  Service d'initialisation admin
   */
  public function __construct(
    private readonly SuperAdminBootstrapService $bootstrapService,
  ) {}

  /**
   * Initialise le super admin, Shield et les permissions (GET ou POST).
   *
   * Authentification : ?token=, X-Deployment-Token, Authorization: Bearer ou body JSON token.
   */
  public function invoke(): JsonResponse
  {
    try {
      $result = $this->bootstrapService->run();

      return response()->json([
        'success' => $result['success'],
        'message' => $result['success']
          ? 'Super administrateur initialisé avec succès.'
          : 'Initialisation interrompue : une étape a échoué.',
        'admin' => $result['admin'],
        'login_url' => url('/admin/login'),
        'steps' => $result['steps'],
      ], $result['success'] ? 200 : 500);
    } catch (InvalidArgumentException $exception) {
      return response()->json([
        'success' => false,
        'message' => $exception->getMessage(),
      ], 422);
    }
  }
}
