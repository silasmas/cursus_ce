<?php

namespace App\Http\Controllers\Ecap\Concerns;

use App\Enums\EcapVacationRole;
use App\Models\User;
use App\Services\Ecap\EcapStaffRoleService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Réponse Inertia conviviale lorsqu'un acteur ECAP n'a pas le rôle requis.
 */
trait RespondsWithEcapAccessDenied
{
  /**
   * Retourne une page d'accès refusé si le rôle est absent, sinon null.
   */
  protected function denyEcapStaffRole(
    Request $request,
    User $user,
    EcapVacationRole $requiredRole,
    string $featureLabel,
  ): ?Response {
    $roleService = app(EcapStaffRoleService::class);

    if ($roleService->hasRole($user, $requiredRole)) {
      return null;
    }

    return $this->ecapAccessDeniedResponse($request, $user, $requiredRole, $featureLabel);
  }

  /**
   * Construit la réponse Inertia « accès refusé » (403).
   */
  protected function ecapAccessDeniedResponse(
    Request $request,
    User $user,
    EcapVacationRole $requiredRole,
    string $featureLabel,
  ): Response {
    $roleService = app(EcapStaffRoleService::class);

    return Inertia::render('Ecap/AccessDenied', [
      'title' => 'Accès non autorisé',
      'feature' => $featureLabel,
      'requiredRole' => $requiredRole->label(),
      'requiredRoleKey' => $requiredRole->value,
      'yourRoles' => $roleService->activeRoleLabels($user),
      'hint' => $this->accessDeniedHint($requiredRole, $roleService->activeRoles($user)),
      'backUrl' => route('ecap.staff.questions.index'),
    ])->toResponse($request)->setStatusCode(403);
  }

  /**
   * Message d'aide selon le rôle manquant.
   *
   * @param  array<int, EcapVacationRole>  $userRoles
   */
  private function accessDeniedHint(EcapVacationRole $required, array $userRoles): string
  {
    if ($userRoles === []) {
      return 'Votre compte n\'a pas encore d\'affectation active sur cette session ECAP. Demandez à l\'administration de vous ajouter dans Acteurs ECAP.';
    }

    $labels = array_map(fn (EcapVacationRole $role) => $role->label(), $userRoles);

    return 'Vos rôles actuels : '.implode(', ', $labels).'. Seul un '.$required->label().' peut ouvrir cette section.';
  }
}
