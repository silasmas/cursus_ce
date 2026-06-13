<?php

namespace App\Services\Portal;

use App\Models\Enrollment;
use App\Models\MemberSurvey;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Éligibilité et enregistrement du sondage de satisfaction fidèle.
 */
class MemberSurveyService
{
  /**
   * Indique si le sondage doit s'afficher pour ce fidèle.
   */
  public function shouldPrompt(User $user): bool
  {
    if (! config('portal.member_survey.enabled', true)) {
      return false;
    }

    $weeksRequired = (int) config('portal.member_survey.weeks_after_enrollment', 4);

    if ($weeksRequired <= 0) {
      return false;
    }

    $firstEnrollmentAt = $this->firstEnrollmentAt($user);

    if ($firstEnrollmentAt === null) {
      return false;
    }

    if ($firstEnrollmentAt->diffInWeeks(now()) < $weeksRequired) {
      return false;
    }

    $survey = MemberSurvey::query()->where('user_id', $user->id)->first();

    if ($survey?->submitted_at !== null) {
      return false;
    }

    if ($survey?->snoozed_until !== null && $survey->snoozed_until->isFuture()) {
      return false;
    }

    return true;
  }

  /**
   * Payload partagé avec le frontend Inertia.
   *
   * @return array<string, mixed>|null
   */
  public function promptPayload(User $user): ?array
  {
    if (! $this->shouldPrompt($user)) {
      return null;
    }

    $weeksRequired = (int) config('portal.member_survey.weeks_after_enrollment', 4);

    return [
      'weeks_after_enrollment' => $weeksRequired,
      'submit_url' => route('member-survey.store'),
      'snooze_url' => route('member-survey.snooze'),
    ];
  }

  /**
   * Enregistre une réponse complète au sondage.
   *
   * @param  array{satisfaction: int, nps_score?: int|null, comment?: string|null}  $data
   */
  public function submit(User $user, array $data): MemberSurvey
  {
    $weeksSinceEnrollment = $this->weeksSinceFirstEnrollment($user);

    return MemberSurvey::query()->updateOrCreate(
      ['user_id' => $user->id],
      [
        'satisfaction' => $data['satisfaction'],
        'nps_score' => $data['nps_score'] ?? null,
        'comment' => $data['comment'] ?? null,
        'weeks_since_enrollment' => $weeksSinceEnrollment,
        'submitted_at' => now(),
        'snoozed_until' => null,
      ],
    );
  }

  /**
   * Reporte l'affichage du sondage (bouton « Plus tard »).
   */
  public function snooze(User $user): MemberSurvey
  {
    $snoozeDays = max(1, (int) config('portal.member_survey.snooze_days', 7));

    return MemberSurvey::query()->updateOrCreate(
      ['user_id' => $user->id],
      [
        'snoozed_until' => now()->addDays($snoozeDays),
      ],
    );
  }

  /**
   * Date de la première inscription du fidèle.
   */
  private function firstEnrollmentAt(User $user): ?Carbon
  {
    $enrolledAt = Enrollment::query()
      ->where('user_id', $user->id)
      ->orderBy('enrolled_at')
      ->value('enrolled_at');

    return $enrolledAt ? Carbon::parse($enrolledAt) : null;
  }

  /**
   * Nombre de semaines depuis la première inscription.
   */
  private function weeksSinceFirstEnrollment(User $user): ?int
  {
    $firstEnrollmentAt = $this->firstEnrollmentAt($user);

    if ($firstEnrollmentAt === null) {
      return null;
    }

    return (int) $firstEnrollmentAt->diffInWeeks(now());
  }
}
