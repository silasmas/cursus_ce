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
   * Canaux de rendez-vous mentor disponibles par défaut.
   *
   * @var array<int, string>
   */
  private const DEFAULT_APPOINTMENT_CHANNELS = ['whatsapp', 'zoom', 'google_meet'];

  /**
   * Paramètres effectifs pour un programme (valeurs par défaut si non configuré).
   *
   * @return array{linear_progression: bool, quiz_mandatory: bool, visible_appointment_channels: array<int, string>, zoom_auto_create_link: bool}
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
      'visible_appointment_channels' => $this->resolveVisibleAppointmentChannels($setting?->settings),
      'zoom_auto_create_link' => (bool) data_get($setting?->settings, 'zoom.auto_create_link', false),
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

  /**
   * Canaux de rendez-vous visibles pour les mentors sur un cursus.
   *
   * @return array<int, string>
   */
  public function visibleAppointmentChannels(Program|int $program): array
  {
    return $this->forProgram($program)['visible_appointment_channels'];
  }

  /**
   * Indique si la création automatique de lien Zoom est active pour le cursus.
   */
  public function shouldAutoCreateZoomLink(Program|int $program): bool
  {
    return $this->forProgram($program)['zoom_auto_create_link'];
  }

  /**
   * Normalise la liste des canaux depuis settings JSON.
   *
   * @param  array<string, mixed>|null  $settings
   * @return array<int, string>
   */
  private function resolveVisibleAppointmentChannels(?array $settings): array
  {
    $raw = data_get($settings, 'mentor_appointments.visible_channels');

    if (! is_array($raw)) {
      return self::DEFAULT_APPOINTMENT_CHANNELS;
    }

    $allowed = array_values(array_intersect(
      self::DEFAULT_APPOINTMENT_CHANNELS,
      array_map('strval', $raw),
    ));

    return count($allowed) > 0 ? $allowed : self::DEFAULT_APPOINTMENT_CHANNELS;
  }
}
