<?php

namespace App\Services\Ecap;

use App\Models\LearningGroup;
use App\Models\LearningGroupMember;
use App\Models\StudentAcademicRecord;
use App\Models\User;

/**
 * Finalise le parcours ECAP d'un fidèle après inscription (dossier + groupe de vacation).
 */
class EcapStudentOnboardingService
{
  /**
   * Crée le dossier académique et affecte le fidèle à un groupe de vacation si présentiel.
   *
   * @param  User  $user  Fidèle inscrit
   * @param  int  $academicSessionId  Session ECAP
   * @param  bool  $isOnline  Mode en ligne (pas de groupe si true)
   */
  public function onboard(User $user, int $academicSessionId, bool $isOnline = true): void
  {
    $this->ensureAcademicRecord($user, $academicSessionId);

    if (! $isOnline) {
      $this->assignToBalancedLearningGroup($user, $academicSessionId);
    }
  }

  /**
   * Ouvre ou récupère le dossier académique ECAP du fidèle.
   */
  public function ensureAcademicRecord(User $user, int $academicSessionId): StudentAcademicRecord
  {
    return StudentAcademicRecord::query()->firstOrCreate(
      [
        'user_id' => $user->id,
        'academic_session_id' => $academicSessionId,
      ],
      [
        'summary' => null,
        'final_average' => null,
        'validated_at' => null,
      ],
    );
  }

  /**
   * Affecte le fidèle au groupe de vacation le moins peuplé de la session.
   */
  public function assignToBalancedLearningGroup(User $user, int $academicSessionId): ?LearningGroupMember
  {
    $alreadyAssigned = LearningGroupMember::query()
      ->where('user_id', $user->id)
      ->whereHas('learningGroup', fn ($query) => $query->where('academic_session_id', $academicSessionId))
      ->exists();

    if ($alreadyAssigned) {
      return null;
    }

    $group = LearningGroup::query()
      ->where('academic_session_id', $academicSessionId)
      ->withCount('members')
      ->orderBy('members_count')
      ->orderBy('sort_order')
      ->first();

    if ($group === null) {
      return null;
    }

    return LearningGroupMember::query()->firstOrCreate(
      [
        'learning_group_id' => $group->id,
        'user_id' => $user->id,
      ],
      [
        'group_role' => 'membre',
      ],
    );
  }
}
