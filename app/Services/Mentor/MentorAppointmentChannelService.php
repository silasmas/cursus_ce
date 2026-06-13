<?php

namespace App\Services\Mentor;

use App\Enums\AppointmentChannel;
use App\Models\MentorAssignment;
use App\Models\Program;
use App\Services\Mentor\MentorSettingService;
use App\Services\Program\ProgramSettingService;
use Illuminate\Support\Collection;

/**
 * Résout les canaux de rendez-vous autorisés pour le mentorat.
 */
class MentorAppointmentChannelService
{
  /**
   * @param  ProgramSettingService  $programSettingService  Configuration métier des cursus
   */
  public function __construct(
    private readonly ProgramSettingService $programSettingService,
    private readonly MentorSettingService $mentorSettingService,
  ) {}

  /**
   * Retourne les canaux autorisés pour un programme.
   *
   * @return array<int, string>
   */
  public function allowedValuesForProgram(?Program $program): array
  {
    $globalChannels = $this->globalVisibleChannels();

    if (! $program) {
      return $globalChannels;
    }

    $programChannels = $this->programSettingService->visibleAppointmentChannels($program);
    $resolved = array_values(array_intersect($globalChannels, $programChannels));

    return count($resolved) > 0 ? $resolved : $globalChannels;
  }

  /**
   * Retourne les canaux autorisés pour une liste d'assignations.
   *
   * @param  Collection<int, MentorAssignment>  $assignments
   * @return array<int, string>
   */
  public function allowedValuesForAssignments(Collection $assignments): array
  {
    $programIds = $assignments->pluck('program_id')->filter()->unique()->values();

    if ($programIds->isEmpty()) {
      return $this->globalVisibleChannels();
    }

    $globalChannels = $this->globalVisibleChannels();
    $allowed = $programIds
      ->map(fn (int $programId) => $this->programSettingService->visibleAppointmentChannels($programId))
      ->reduce(fn (array $carry, array $channels) => array_values(array_intersect($carry, $channels)), $globalChannels);

    return count($allowed) > 0 ? $allowed : $globalChannels;
  }

  /**
   * Retourne les options structurées pour le frontend.
   *
   * @param  array<int, string>  $values
   * @return array<int, array{value: string, label: string}>
   */
  public function optionsFromValues(array $values): array
  {
    return collect($values)
      ->map(function (string $value): ?array {
        $enum = AppointmentChannel::tryFrom($value);

        if (! $enum) {
          return null;
        }

        return [
          'value' => $enum->value,
          'label' => $enum->label(),
        ];
      })
      ->filter()
      ->values()
      ->all();
  }

  /**
   * Tous les canaux pris en charge par la plateforme.
   *
   * @return array<int, string>
   */
  public function allValues(): array
  {
    return collect(AppointmentChannel::cases())->map(fn (AppointmentChannel $channel) => $channel->value)->all();
  }

  /**
   * Canaux globaux visibles dans les paramètres mentorat.
   *
   * @return array<int, string>
   */
  private function globalVisibleChannels(): array
  {
    $raw = $this->mentorSettingService->current()->visible_channels;

    if (! is_array($raw) || count($raw) === 0) {
      return $this->allValues();
    }

    $allowed = array_values(array_intersect($this->allValues(), array_map('strval', $raw)));

    return count($allowed) > 0 ? $allowed : $this->allValues();
  }
}

