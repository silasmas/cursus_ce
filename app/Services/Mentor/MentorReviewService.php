<?php

namespace App\Services\Mentor;

use App\Enums\PortalNotificationType;
use App\Models\AssignmentSubmission;
use App\Models\MentorAssignment;
use App\Models\MentoringDecision;
use App\Models\User;
use App\Services\Portal\PortalNotificationService;
use App\Services\Student\MentorPortalService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Validation des TP par le mentor avant progression du mentoré.
 */
class MentorReviewService
{
  /**
   * @param  PortalNotificationService  $notificationService  Notifications portail
   * @param  MentorPortalService  $mentorPortalService  Portail mentorat
   */
  public function __construct(
    private readonly PortalNotificationService $notificationService,
    private readonly MentorPortalService $mentorPortalService,
  ) {}
  /**
   * Soumissions en attente de validation pour les mentorés d'un mentor.
   *
   * @return Collection<int, AssignmentSubmission>
   */
  public function pendingSubmissionsForMentor(User $mentor): Collection
  {
    $menteeIds = MentorAssignment::query()
      ->where('mentor_id', $mentor->id)
      ->where('status', 'active')
      ->pluck('mentee_id');

    if ($menteeIds->isEmpty()) {
      return collect();
    }

    return AssignmentSubmission::query()
      ->whereIn('user_id', $menteeIds)
      ->where('mentor_status', 'pending')
      ->whereNotNull('submitted_at')
      ->with(['user', 'assessment.chapter.course.program'])
      ->latest('submitted_at')
      ->get();
  }

  /**
   * Valide ou refuse un TP côté mentor et enregistre l'avis pour l'administration.
   */
  public function reviewSubmission(
    User $mentor,
    AssignmentSubmission $submission,
    string $decision,
    string $notes,
  ): AssignmentSubmission {
    $assignment = MentorAssignment::query()
      ->where('mentor_id', $mentor->id)
      ->where('mentee_id', $submission->user_id)
      ->where('status', 'active')
      ->first();

    if (! $assignment) {
      throw new \RuntimeException('Ce travail ne concerne pas l\'un de vos mentorés.');
    }

    if ($submission->mentor_status !== 'pending') {
      throw new \RuntimeException('Cette soumission a déjà été traitée.');
    }

    $mentorStatus = $decision === 'approved' ? 'approved' : 'rejected';

    return DB::transaction(function () use ($mentor, $submission, $assignment, $mentorStatus, $notes, $decision) {
      $submission->update([
        'mentor_status' => $mentorStatus,
        'mentor_notes' => $notes,
        'mentor_reviewer_id' => $mentor->id,
        'mentor_reviewed_at' => now(),
      ]);

      MentoringDecision::query()->create([
        'mentor_assignment_id' => $assignment->id,
        'chapter_id' => $submission->assessment?->chapter_id,
        'assignment_submission_id' => $submission->id,
        'decided_by_user_id' => $mentor->id,
        'decision' => $decision,
        'notes' => $notes,
        'decided_at' => now(),
      ]);

      $mentee = $submission->user;

      if ($mentee) {
        if ($decision === 'approved') {
          $this->notificationService->notify(
            $mentee,
            PortalNotificationType::MentorApproval,
            'Aval de votre mentor',
            'Votre mentor a validé votre TP. Vous pouvez soumettre votre rapport de progression.',
            '/mon-espace/mentor',
            'Voir mon mentor',
            ['submission_id' => $submission->id],
          );
          $this->mentorPortalService->notifyReportUnlocked($assignment);
        } else {
          $this->notificationService->notify(
            $mentee,
            PortalNotificationType::MentorRejection,
            'TP à corriger',
            'Votre mentor demande une correction : '.\Illuminate\Support\Str::limit($notes, 100),
            '/mon-espace?cursus=metamorpho',
            'Reprendre le cursus',
            ['submission_id' => $submission->id],
          );
        }
      }

      return $submission->fresh(['user', 'assessment.chapter']);
    });
  }

  /**
   * Soumissions d'un mentoré pour la fiche mentor.
   *
   * @return Collection<int, AssignmentSubmission>
   */
  public function submissionsForMentee(User $mentor, User $mentee): Collection
  {
    $isAssigned = MentorAssignment::query()
      ->where('mentor_id', $mentor->id)
      ->where('mentee_id', $mentee->id)
      ->where('status', 'active')
      ->exists();

    if (! $isAssigned) {
      return collect();
    }

    return AssignmentSubmission::query()
      ->where('user_id', $mentee->id)
      ->whereNotNull('submitted_at')
      ->with(['assessment.chapter.course.program'])
      ->latest('submitted_at')
      ->get();
  }

  /**
   * Formate une soumission pour l'interface mentor.
   *
   * @return array<string, mixed>
   */
  public function submissionPayload(AssignmentSubmission $submission): array
  {
    $chapter = $submission->assessment?->chapter;

    return [
      'id' => $submission->id,
      'mentee_name' => $submission->user?->name,
      'mentee_email' => $submission->user?->email,
      'title' => $submission->assessment?->title,
      'chapter' => $chapter?->title,
      'program' => $chapter?->course?->program?->name,
      'answer_text' => $submission->answer_text,
      'file_path' => $submission->file_path,
      'file_url' => $submission->file_path ? asset('storage/'.$submission->file_path) : null,
      'submitted_at' => $submission->submitted_at?->format('d/m/Y H:i'),
      'mentor_status' => $submission->mentor_status,
      'mentor_notes' => $submission->mentor_notes,
      'status' => $submission->status,
    ];
  }
}
