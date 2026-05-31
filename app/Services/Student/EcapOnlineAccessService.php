<?php

namespace App\Services\Student;

use App\Models\Enrollment;
use App\Models\Program;
use App\Models\User;

/**
 * Vérifie si un fidèle ECAP peut utiliser le parcours en ligne (progression, quiz, TP).
 */
class EcapOnlineAccessService
{
  /**
   * Indique si le fidèle peut interagir en ligne sur le cursus ECAP.
   *
   * @param  User  $user  Fidèle connecté
   * @param  Program|null  $program  Cursus du chapitre ou de la ressource
   */
  public function canUseOnlineProgression(User $user, ?Program $program): bool
  {
    if ($program === null || $program->slug !== 'ecap') {
      return true;
    }

    return $this->ecapEnrollment($user)?->is_online ?? true;
  }

  /**
   * Indique si le fidèle est inscrit ECAP en mode présentiel (lecture seule en ligne).
   */
  public function isPresentiel(User $user): bool
  {
    $enrollment = $this->ecapEnrollment($user);

    return $enrollment !== null && $enrollment->is_online === false;
  }

  /**
   * Retourne les métadonnées du mode ECAP pour l'espace membre.
   *
   * @return array{is_online: bool, is_presentiel: bool, label: string}|null
   */
  public function modePayloadForUser(User $user): ?array
  {
    $enrollment = $this->ecapEnrollment($user);

    if ($enrollment === null) {
      return null;
    }

    return [
      'is_online' => (bool) $enrollment->is_online,
      'is_presentiel' => ! $enrollment->is_online,
      'label' => $enrollment->is_online ? 'En ligne' : 'Présentiel',
    ];
  }

  /**
   * Inscription ECAP active du fidèle.
   */
  private function ecapEnrollment(User $user): ?Enrollment
  {
    return Enrollment::query()
      ->where('user_id', $user->id)
      ->whereHas('program', fn ($query) => $query->where('slug', 'ecap'))
      ->latest('enrolled_at')
      ->first();
  }
}
