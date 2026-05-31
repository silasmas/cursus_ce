<?php

namespace App\Services\Program;

use App\Models\Program;
use App\Models\ProgramSetting;

/**
 * Lit les paramètres métier d'un cursus (progression, quiz obligatoires…).
 */
class ProgramSettingService
{
  /**
   * Paramètres effectifs pour un programme (valeurs par défaut si non configuré).
   *
   * @return array{linear_progression: bool, quiz_mandatory: bool}
   */
  public function forProgram(Program|int $program): array
  {
    $programId = $program instanceof Program ? $program->id : $program;

    $setting = ProgramSetting::query()
      ->where('program_id', $programId)
      ->first();

    return [
      'linear_progression' => $setting?->linear_progression ?? true,
      'quiz_mandatory' => $setting?->quiz_mandatory ?? false,
    ];
  }

  /**
   * Indique si le cursus impose une progression chapitre par chapitre.
   */
  public function requiresLinearProgression(Program|int $program): bool
  {
    return $this->forProgram($program)['linear_progression'];
  }

  /**
   * Indique si les quiz de chapitre bloquent la validation de l'étape.
   */
  public function requiresQuizPass(Program|int $program): bool
  {
    return $this->forProgram($program)['quiz_mandatory'];
  }
}
