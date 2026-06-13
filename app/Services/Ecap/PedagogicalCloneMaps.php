<?php

namespace App\Services\Ecap;

/**
 * Correspondances d'identifiants après clonage pédagogique ECAP.
 */
final class PedagogicalCloneMaps
{
  /**
   * @param  array<int, int>  $modules  Ancien module → nouveau module
   * @param  array<int, int>  $chapters  Ancien chapitre → nouveau chapitre
   * @param  array<int, int>  $assessments  Ancienne évaluation → nouvelle évaluation
   */
  public function __construct(
    public array $modules = [],
    public array $chapters = [],
    public array $assessments = [],
  ) {}

  /**
   * Remappe un identifiant de module, ou conserve l'original si absent.
   */
  public function moduleId(?int $id): ?int
  {
    if ($id === null) {
      return null;
    }

    return $this->modules[$id] ?? $id;
  }

  /**
   * Remappe un identifiant de chapitre, ou conserve l'original si absent.
   */
  public function chapterId(?int $id): ?int
  {
    if ($id === null) {
      return null;
    }

    return $this->chapters[$id] ?? $id;
  }

  /**
   * Remappe un identifiant d'évaluation, ou conserve l'original si absent.
   */
  public function assessmentId(?int $id): ?int
  {
    if ($id === null) {
      return null;
    }

    return $this->assessments[$id] ?? $id;
  }
}
