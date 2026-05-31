<?php

namespace App\Services\Ecap;

use App\Enums\EcapVacationRole;
use App\Models\EcapStaffAssignment;
use Illuminate\Validation\ValidationException;

/**
 * Valide les règles métier d'affectation des acteurs ECAP (max 2 rôles, incompatibilités).
 */
class EcapStaffAssignmentValidator
{
  private const MAX_ROLES = 2;

  /**
   * Rôles pédagogiques incompatibles avec superviseur / modérateur.
   *
   * @var array<int, EcapVacationRole>
   */
  private const TEACHING_ROLES = [
    EcapVacationRole::Teacher,
    EcapVacationRole::Inspector,
  ];

  /**
   * Rôles de facilitation incompatibles avec enseignant / inspecteur.
   *
   * @var array<int, EcapVacationRole>
   */
  private const FACILITATION_ROLES = [
    EcapVacationRole::Supervisor,
    EcapVacationRole::Moderator,
  ];

  /**
   * Vérifie qu'une nouvelle affectation respecte les contraintes de rôles.
   *
   * @param  int  $userId  Utilisateur cible
   * @param  int  $academicSessionId  Session ECAP
   * @param  EcapVacationRole|string  $role  Rôle demandé
   * @param  int|null  $ignoreAssignmentId  Affectation en cours d'édition
   * @param  int|null  $courseModuleId  Module (enseignant / superviseur)
   */
  public function assertCanAssign(
    int $userId,
    int $academicSessionId,
    EcapVacationRole|string $role,
    ?int $ignoreAssignmentId = null,
    ?int $courseModuleId = null,
  ): void {
    $role = $role instanceof EcapVacationRole ? $role : EcapVacationRole::from($role);

    $existing = EcapStaffAssignment::query()
      ->where('user_id', $userId)
      ->where('academic_session_id', $academicSessionId)
      ->where('is_active', true)
      ->when($ignoreAssignmentId, fn ($query) => $query->whereKeyNot($ignoreAssignmentId))
      ->get();

    if ($this->hasDuplicateAssignment($existing, $role, $courseModuleId)) {
      $field = $courseModuleId !== null ? 'course_module_ids' : 'role';
      $message = $courseModuleId !== null
        ? 'Cet utilisateur occupe déjà ce rôle sur ce module.'
        : 'Cet utilisateur occupe déjà ce rôle sur cette session.';

      throw ValidationException::withMessages([
        $field => $message,
      ]);
    }

    $existingRoleTypes = $existing
      ->map(fn (EcapStaffAssignment $row) => $row->role instanceof EcapVacationRole ? $row->role : EcapVacationRole::from($row->role))
      ->toBase()
      ->unique(fn (EcapVacationRole $role): string => $role->value)
      ->values();

    if (! $existingRoleTypes->contains($role) && $existingRoleTypes->count() >= self::MAX_ROLES) {
      throw ValidationException::withMessages([
        'user_id' => 'Un utilisateur ne peut pas cumuler plus de '.self::MAX_ROLES.' rôles ECAP actifs sur une même session.',
      ]);
    }

    $allRoleTypes = $existingRoleTypes
      ->push($role)
      ->unique(fn (EcapVacationRole $role): string => $role->value)
      ->values()
      ->all();

    if ($this->hasTeachingRole($allRoleTypes) && $this->hasFacilitationRole($allRoleTypes)) {
      throw ValidationException::withMessages([
        'role' => 'Un enseignant ou inspecteur ne peut pas être superviseur ou modérateur (et inversement).',
      ]);
    }
  }

  /**
   * Détecte un doublon exact (même rôle + même module ou même rôle session).
   *
   * @param  \Illuminate\Support\Collection<int, EcapStaffAssignment>  $existing
   */
  private function hasDuplicateAssignment($existing, EcapVacationRole $role, ?int $courseModuleId): bool
  {
    return $existing->contains(function (EcapStaffAssignment $row) use ($role, $courseModuleId) {
      $rowRole = $row->role instanceof EcapVacationRole ? $row->role : EcapVacationRole::from($row->role);

      if ($rowRole !== $role) {
        return false;
      }

      if ($courseModuleId !== null) {
        return (int) $row->course_module_id === $courseModuleId;
      }

      return $row->course_module_id === null;
    });
  }

  /**
   * Indique si la liste contient un rôle pédagogique.
   *
   * @param  array<int, EcapVacationRole>  $roles
   */
  private function hasTeachingRole(array $roles): bool
  {
    foreach ($roles as $role) {
      if (in_array($role, self::TEACHING_ROLES, true)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Indique si la liste contient un rôle de facilitation.
   *
   * @param  array<int, EcapVacationRole>  $roles
   */
  private function hasFacilitationRole(array $roles): bool
  {
    foreach ($roles as $role) {
      if (in_array($role, self::FACILITATION_ROLES, true)) {
        return true;
      }
    }

    return false;
  }
}
