<?php

namespace App\Services\Ecap;

use App\Filament\Resources\AssessmentAttempts\AssessmentAttemptResource;
use App\Models\AssessmentAttempt;
use App\Services\Admin\AdminNotificationService;
use App\Services\Portal\PortalNotificationService;
use App\Services\Student\AssessmentAttemptGradingService;
use App\Enums\PortalNotificationType;

/**
 * Notifications lorsqu'un quiz avec réponses rédigées attend une correction.
 */
class EcapQuizGradingNotifier
{
  /**
   * @param  AssessmentAttemptGradingService  $gradingService  Service de correction
   * @param  PortalNotificationService  $portalNotificationService  Notifications portail
   * @param  AdminNotificationService  $adminNotificationService  Notifications admin
   */
  public function __construct(
    private readonly AssessmentAttemptGradingService $gradingService,
    private readonly PortalNotificationService $portalNotificationService,
    private readonly AdminNotificationService $adminNotificationService,
  ) {}

  /**
   * Notifie staff ECAP et admins qu'une correction est requise (une seule fois).
   */
  public function notifyGradingRequired(AssessmentAttempt $attempt): void
  {
    $attempt->refresh();

    if ($attempt->grading_notified_at !== null) {
      return;
    }

    if (! $this->gradingService->needsManualGrading($attempt)) {
      return;
    }

    $attempt->loadMissing(['user', 'assessment.chapter', 'assessment.courseModule']);

    $studentName = $attempt->user?->name ?? 'Un fidèle';
    $quizTitle = $attempt->assessment?->title ?? 'Quiz';
    $moduleName = $attempt->assessment?->courseModule?->name;
    $chapterTitle = $attempt->assessment?->chapter?->title;

    $context = collect([$moduleName, $chapterTitle])->filter()->implode(' · ');
    $title = 'Quiz à corriger';
    $body = "{$studentName} a soumis « {$quizTitle} »".($context !== '' ? " ({$context})" : '').'. Merci de corriger les réponses rédigées.';

    $staffUrl = url('/ecap/acteurs/corrections-quiz/'.$attempt->id);
    $adminUrl = AssessmentAttemptResource::getUrl('grade', ['record' => $attempt]);

    foreach ($this->gradingService->staffGradersToNotify($attempt) as $staff) {
      $this->portalNotificationService->notifyWithEmail(
        $staff,
        PortalNotificationType::QuizPendingGrading,
        $title,
        $body,
        $staffUrl,
        'Corriger le quiz',
        ['attempt_id' => $attempt->id],
      );
    }

    $this->adminNotificationService->notifyAdmins($title, $body, $adminUrl);

    $attempt->update(['grading_notified_at' => now()]);
  }

  /**
   * Notifie le fidèle que sa tentative a été corrigée.
   */
  public function notifyStudentGraded(AssessmentAttempt $attempt): void
  {
    $attempt->loadMissing(['user', 'assessment', 'gradedBy']);

    $student = $attempt->user;

    if ($student === null) {
      return;
    }

    $graderName = $attempt->gradedBy?->name ?? 'Votre formateur';
    $passed = $attempt->passed ? 'réussi' : 'non réussi';
    $score = $attempt->score !== null ? (float) $attempt->score : 0;

    $this->portalNotificationService->notifyWithEmail(
      $student,
      PortalNotificationType::QuizGraded,
      'Quiz corrigé',
      "{$graderName} a corrigé votre quiz « {$attempt->assessment?->title} ». Score : {$score} % ({$passed}).",
      url('/mon-espace/tests/'.$attempt->assessment_id.'/resultat/'.$attempt->id),
      'Voir le résultat',
      ['attempt_id' => $attempt->id],
    );
  }
}
