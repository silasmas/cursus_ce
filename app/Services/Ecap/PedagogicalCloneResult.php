<?php

namespace App\Services\Ecap;

use App\Models\Course;

/**
 * Résultat du clonage pédagogique d'une session ECAP.
 */
final class PedagogicalCloneResult
{
  /**
   * @param  Course  $course  Cours cloné pour la session cible
   * @param  PedagogicalCloneMaps  $maps  Correspondances d'identifiants
   * @param  array<string, int>  $counts  Compteurs par type d'entité
   */
  public function __construct(
    public Course $course,
    public PedagogicalCloneMaps $maps,
    public array $counts,
  ) {}
}
