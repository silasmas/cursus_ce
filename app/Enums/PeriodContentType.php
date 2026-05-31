<?php

namespace App\Enums;

use App\Models\Assessment;
use App\Models\Chapter;
use App\Models\CourseModule;

/**
 * Types de contenus affectables à une période ECAP.
 */
enum PeriodContentType: string
{
  case CourseModule = 'course_module';

  case Chapter = 'chapter';

  case Assessment = 'assessment';

  /**
   * Classe Eloquent associée au type.
   *
   * @return class-string
   */
  public function modelClass(): string
  {
    return match ($this) {
      self::CourseModule => CourseModule::class,
      self::Chapter => Chapter::class,
      self::Assessment => Assessment::class,
    };
  }

  /**
   * Libellé pour l'interface d'administration.
   */
  public function label(): string
  {
    return match ($this) {
      self::CourseModule => 'Module de cours',
      self::Chapter => 'Chapitre',
      self::Assessment => 'Évaluation (quiz / examen)',
    };
  }
}
