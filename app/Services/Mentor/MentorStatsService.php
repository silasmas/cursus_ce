<?php

namespace App\Services\Mentor;

use App\Models\AssignmentSubmission;
use App\Models\ChapterProgress;
use App\Models\MentorAppointment;
use App\Models\MentorAssignment;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Statistiques agrégées pour le tableau de bord mentor.
 */
class MentorStatsService
{
  /**
   * @param  \App\Services\Student\MentorPortalService  $mentorPortalService  Portail mentorat
   */
  public function __construct(
    private readonly \App\Services\Student\MentorPortalService $mentorPortalService,
  ) {}

  /**
   * Statistiques globales du mentor pour une période (jours).
   *
   * @return array<string, mixed>
   */
  public function dashboardStats(User $mentor, int $periodDays = 90): array
  {
    $mentees = $this->mentorPortalService->activeMenteesForMentor($mentor);
    $menteeIds = $mentees->pluck('mentee_id');
    $since = now()->subDays($periodDays);

    $recentAssignments = $mentees->filter(
      fn ($a) => $a->started_at && $a->started_at->gte($since),
    );

    return [
      'period_days' => $periodDays,
      'total_mentees' => $mentees->count(),
      'new_mentees_period' => $recentAssignments->count(),
      'pending_corrections' => AssignmentSubmission::query()
        ->whereIn('user_id', $menteeIds)
        ->where('mentor_status', 'pending')
        ->whereNotNull('submitted_at')
        ->whereNull('submitted_by_user_id')
        ->count(),
      'mentor_tps_pending_admin' => AssignmentSubmission::query()
        ->where('submitted_by_user_id', $mentor->id)
        ->where('admin_publication_status', 'pending_review')
        ->count(),
      'upcoming_appointments' => MentorAppointment::query()
        ->where('mentor_id', $mentor->id)
        ->where('scheduled_at', '>=', now())
        ->where('status', 'scheduled')
        ->count(),
      'appointments_awaiting_response' => MentorAppointment::query()
        ->where('mentor_id', $mentor->id)
        ->where('scheduled_at', '>=', now())
        ->where('mentee_response', 'pending')
        ->count(),
      'by_age' => $this->menteesByAge($mentees),
      'by_progress' => $this->menteesByProgress($mentees),
    ];
  }

  /**
   * Répartition des mentorés par tranche d'âge.
   *
   * @param  Collection<int, MentorAssignment>  $assignments
   * @return array<string, int>
   */
  private function menteesByAge(Collection $assignments): array
  {
    $brackets = [
      '-25' => 0,
      '26-35' => 0,
      '36-45' => 0,
      '46+' => 0,
      'inconnu' => 0,
    ];

    foreach ($assignments as $assignment) {
      $age = $assignment->mentee?->profile?->date_naissance?->age;

      if ($age === null) {
        $brackets['inconnu']++;
      } elseif ($age <= 25) {
        $brackets['-25']++;
      } elseif ($age <= 35) {
        $brackets['26-35']++;
      } elseif ($age <= 45) {
        $brackets['36-45']++;
      } else {
        $brackets['46+']++;
      }
    }

    return $brackets;
  }

  /**
   * Répartition mentorés : terminés, en cours, en attente validation.
   *
   * @param  Collection<int, MentorAssignment>  $assignments
   * @return array<string, int>
   */
  private function menteesByProgress(Collection $assignments): array
  {
    $finished = 0;
    $inProgress = 0;
    $pending = 0;

    foreach ($assignments as $assignment) {
      $menteeId = $assignment->mentee_id;
      $programId = $assignment->program_id;

      $hasPendingTp = AssignmentSubmission::query()
        ->where('user_id', $menteeId)
        ->where('mentor_status', 'pending')
        ->whereNotNull('submitted_at')
        ->exists();

      if ($hasPendingTp) {
        $pending++;
        continue;
      }

      $enrollmentId = $assignment->enrollment_id;

      if (! $enrollmentId) {
        $inProgress++;
        continue;
      }

      $totalChapters = \App\Models\Chapter::query()
        ->whereHas('course', fn ($q) => $q->where('program_id', $programId))
        ->count();

      $completedChapters = ChapterProgress::query()
        ->where('enrollment_id', $enrollmentId)
        ->whereNotNull('completed_at')
        ->whereHas('chapter.course', fn ($q) => $q->where('program_id', $programId))
        ->count();

      if ($totalChapters > 0 && $completedChapters >= $totalChapters) {
        $finished++;
      } else {
        $inProgress++;
      }
    }

    return [
      'finished' => $finished,
      'in_progress' => $inProgress,
      'pending_validation' => $pending,
    ];
  }

  /**
   * Liens et compteurs pour le hub formulaires mentor.
   *
   * @return array<string, mixed>
   */
  public function formsHubSummary(User $mentor): array
  {
    $menteeIds = MentorAssignment::query()
      ->where('mentor_id', $mentor->id)
      ->where('status', 'active')
      ->pluck('mentee_id');

    return [
      'pending_corrections' => AssignmentSubmission::query()
        ->whereIn('user_id', $menteeIds)
        ->where('mentor_status', 'pending')
        ->whereNotNull('submitted_at')
        ->count(),
      'upcoming_appointments' => MentorAppointment::query()
        ->where('mentor_id', $mentor->id)
        ->where('scheduled_at', '>=', now())
        ->count(),
      'postponed_appointments' => MentorAppointment::query()
        ->where('mentor_id', $mentor->id)
        ->where('mentee_response', 'postponed')
        ->where('scheduled_at', '>=', now()->subDays(7))
        ->count(),
      'mentor_tps_pending_admin' => AssignmentSubmission::query()
        ->where('submitted_by_user_id', $mentor->id)
        ->where('admin_publication_status', 'pending_review')
        ->count(),
    ];
  }
}
