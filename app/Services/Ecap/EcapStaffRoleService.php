<?php

namespace App\Services\Ecap;

use App\Enums\EcapVacationRole;
use App\Models\EcapStaffAssignment;
use App\Models\User;

/**
 * Vérifie les rôles vacation ECAP d'un utilisateur.
 */
class EcapStaffRoleService
{
  /**
   * Indique si l'utilisateur occupe un rôle actif sur une session ECAP.
   */
  public function hasRole(
    User $user,
    EcapVacationRole $role,
    ?int $academicSessionId = null,
    ?int $sessionVacationId = null,
  ): bool {
    return EcapStaffAssignment::query()
      ->where('user_id', $user->id)
      ->where('role', $role->value)
      ->where('is_active', true)
      ->when($academicSessionId, fn ($query) => $query->where('academic_session_id', $academicSessionId))
      ->when(
        $sessionVacationId,
        fn ($query) => $query->where(function ($inner) use ($sessionVacationId) {
          $inner->whereNull('session_vacation_id')
            ->orWhere('session_vacation_id', $sessionVacationId);
        }),
      )
      ->exists();
  }

  /**
   * Rôles actifs de l'utilisateur pour une session.
   *
   * @return array<int, EcapVacationRole>
   */
  public function rolesForSession(User $user, int $academicSessionId): array
  {
    return EcapStaffAssignment::query()
      ->where('user_id', $user->id)
      ->where('academic_session_id', $academicSessionId)
      ->where('is_active', true)
      ->pluck('role')
      ->map(fn (EcapVacationRole|string $role) => $role instanceof EcapVacationRole ? $role : EcapVacationRole::from($role))
      ->unique()
      ->values()
      ->all();
  }

  /**
   * Tous les rôles ECAP actifs de l'utilisateur (toutes sessions).
   *
   * @return array<int, EcapVacationRole>
   */
  public function activeRoles(User $user): array
  {
    return EcapStaffAssignment::query()
      ->where('user_id', $user->id)
      ->where('is_active', true)
      ->pluck('role')
      ->map(fn (EcapVacationRole|string $role) => $role instanceof EcapVacationRole ? $role : EcapVacationRole::from($role))
      ->unique()
      ->values()
      ->all();
  }

  /**
   * Libellés français des rôles actifs.
   *
   * @return array<int, string>
   */
  public function activeRoleLabels(User $user): array
  {
    return array_map(
      fn (EcapVacationRole $role) => $role->label(),
      $this->activeRoles($user),
    );
  }

  /**
   * Clés des rôles actifs (teacher, supervisor, …).
   *
   * @return array<int, string>
   */
  public function activeRoleKeys(User $user): array
  {
    return array_map(
      fn (EcapVacationRole $role) => $role->value,
      $this->activeRoles($user),
    );
  }

  /**
   * Indique si l'utilisateur peut corriger des quiz (enseignant, superviseur ou modérateur).
   */
  public function canGradeQuizzes(User $user): bool
  {
    foreach ([EcapVacationRole::Teacher, EcapVacationRole::Supervisor, EcapVacationRole::Moderator] as $role) {
      if ($this->hasRole($user, $role)) {
        return true;
      }
    }

    return false;
  }
}
