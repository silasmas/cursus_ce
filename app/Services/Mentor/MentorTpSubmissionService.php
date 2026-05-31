<?php

namespace App\Services\Mentor;

use App\Enums\AssessmentType;
use App\Enums\PortalNotificationType;
use App\Enums\SubmissionStatus;
use App\Models\Assessment;
use App\Models\AssignmentSubmission;
use App\Models\MentorAssignment;
use App\Models\User;
use App\Services\Admin\AdminNotificationService;
use App\Services\Portal\PortalNotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Remise de TP par le mentor pour ses mentorés (publication admin requise).
 */
class MentorTpSubmissionService
{
  /**
   * @param  PortalNotificationService  $notificationService  Notifications portail
   * @param  AdminNotificationService  $adminNotificationService  Notifications admin
   */
  public function __construct(
    private readonly PortalNotificationService $notificationService,
    private readonly AdminNotificationService $adminNotificationService,
  ) {}

  /**
   * TP publiés du programme assigné, disponibles pour remise mentor.
   *
   * @return Collection<int, Assessment>
   */
  public function availableAssessmentsForAssignment(MentorAssignment $assignment): Collection
  {
    return Assessment::query()
      ->where('type', AssessmentType::Tp->value)
      ->where('is_published', true)
      ->where('program_id', $assignment->program_id)
      ->with('chapter')
      ->orderBy('title')
      ->get();
  }

  /**
   * Remet un TP au nom d'un mentoré (invisible jusqu'à validation admin).
   */
  public function submitForMentee(
    User $mentor,
    MentorAssignment $assignment,
    Assessment $assessment,
    ?string $answerText,
    ?UploadedFile $file = null,
    ?string $existingFilePath = null,
  ): AssignmentSubmission {
    if ($assignment->mentor_id !== $mentor->id) {
      throw new \RuntimeException('Assignation mentor invalide.');
    }

    if ($assessment->program_id !== $assignment->program_id) {
      throw new \RuntimeException('Ce TP n\'appartient pas au programme du mentoré.');
    }

    $mentee = $assignment->mentee;

    if (! $mentee) {
      throw new \RuntimeException('Mentoré introuvable.');
    }

    $filePath = $existingFilePath;

    if ($file) {
      $filePath = $file->store('assignments/mentor/'.$mentee->id, 'public');
    }

    $latest = AssignmentSubmission::query()
      ->where('assessment_id', $assessment->id)
      ->where('user_id', $mentee->id)
      ->latest('submitted_at')
      ->first();

    $version = ($latest?->version ?? 0) + 1;

    $submission = AssignmentSubmission::query()->create([
      'assessment_id' => $assessment->id,
      'user_id' => $mentee->id,
      'submitted_by_user_id' => $mentor->id,
      'enrollment_id' => $assignment->enrollment_id,
      'version' => $version,
      'file_path' => $filePath,
      'answer_text' => $answerText,
      'submitted_at' => now(),
      'status' => SubmissionStatus::Pending->value,
      'mentor_status' => 'approved',
      'visible_to_mentee' => false,
      'admin_publication_status' => 'pending_review',
    ]);

    $this->notifyAdminOfMentorTp($submission, $mentor, $mentee, false);

    return $submission;
  }

  /**
   * Remet le même TP pour plusieurs mentorés (un enregistrement par mentoré).
   *
   * @param  array<int>  $assignmentIds
   * @return Collection<int, AssignmentSubmission>
   */
  public function submitForMany(
    User $mentor,
    array $assignmentIds,
    Assessment $assessment,
    ?string $answerText,
    ?UploadedFile $file = null,
  ): Collection {
    $assignments = MentorAssignment::query()
      ->where('mentor_id', $mentor->id)
      ->whereIn('id', $assignmentIds)
      ->where('status', 'active')
      ->get();

    if ($assignments->isEmpty()) {
      throw new \RuntimeException('Aucun mentoré actif sélectionné.');
    }

    $incompatible = $assignments->first(
      fn (MentorAssignment $a) => $a->program_id !== $assessment->program_id,
    );

    if ($incompatible) {
      throw new \RuntimeException('Ce TP n\'appartient pas au programme de tous les mentorés sélectionnés.');
    }

    $sharedFilePath = $file
      ? $file->store('assignments/mentor/bulk/'.now()->timestamp, 'public')
      : null;

    return $assignments->map(fn (MentorAssignment $assignment) => $this->submitForMentee(
      $mentor,
      $assignment,
      $assessment,
      $answerText,
      null,
      $sharedFilePath,
    ));
  }

  /**
   * Modifie une remise mentor encore en attente de validation admin.
   */
  public function updateSubmission(
    User $mentor,
    AssignmentSubmission $submission,
    ?string $answerText,
    ?UploadedFile $file = null,
  ): AssignmentSubmission {
    if ($submission->submitted_by_user_id !== $mentor->id) {
      throw new \RuntimeException('Remise non autorisée.');
    }

    if ($submission->admin_publication_status !== 'pending_review') {
      throw new \RuntimeException('Seules les remises en attente admin peuvent être modifiées.');
    }

    $data = ['answer_text' => $answerText];

    if ($file) {
      if ($submission->file_path) {
        Storage::disk('public')->delete($submission->file_path);
      }

      $data['file_path'] = $file->store('assignments/mentor/'.$submission->user_id, 'public');
    }

    $submission->update($data);

    $mentee = $submission->user;

    if ($mentee) {
      $this->notificationService->notify(
        $mentee,
        PortalNotificationType::AdminMessage,
        'TP mis à jour par votre mentor',
        'Votre mentor a modifié un TP remis pour vous. L\'administration doit le valider avant publication.',
        '/mon-espace/mentor',
        'Mon mentor',
      );
    }

    $this->notifyAdminOfMentorTp($submission->fresh(), $mentor, $mentee, true);

    return $submission->fresh();
  }

  /**
   * Publie une ou plusieurs remises mentor et notifie tous les concernés (in-app + e-mail).
   *
   * @param  Collection<int, AssignmentSubmission>|AssignmentSubmission  $submissions
   */
  public function publishForMentee(
    AssignmentSubmission|Collection $submissions,
    User $admin,
  ): AssignmentSubmission|Collection {
    $items = $submissions instanceof Collection
      ? $submissions
      : collect([$submissions]);

    $published = $items->map(function (AssignmentSubmission $submission) use ($admin) {
      return $this->publishSingleForMentee($submission, $admin);
    });

    return $submissions instanceof Collection ? $published : $published->first();
  }

  /**
   * Publie une remise et notifie mentoré + mentor.
   */
  private function publishSingleForMentee(AssignmentSubmission $submission, User $admin): AssignmentSubmission
  {
    if ($submission->admin_publication_status !== 'pending_review') {
      throw new \RuntimeException('Cette remise n\'est pas en attente de publication.');
    }

    $submission->update([
      'visible_to_mentee' => true,
      'admin_publication_status' => 'published',
      'grader_id' => $submission->grader_id ?? $admin->id,
    ]);

    $submission->load(['user', 'submittedBy', 'assessment']);
    $tpTitle = $submission->assessment?->title ?? 'Travail pratique';
    $mentee = $submission->user;

    if ($mentee) {
      $this->notificationService->notifyWithEmail(
        $mentee,
        PortalNotificationType::LevelUnlocked,
        'TP validé par l\'administration',
        'Le travail pratique « '.$tpTitle.' » remis par votre mentor est maintenant visible dans votre parcours.',
        route('dashboard', ['cursus' => 'metamorpho']),
        'Voir mon parcours',
        ['submission_id' => $submission->id],
      );
    }

    $mentor = $submission->submittedBy;

    if ($mentor) {
      $assignment = MentorAssignment::query()
        ->where('mentor_id', $mentor->id)
        ->where('mentee_id', $submission->user_id)
        ->latest('started_at')
        ->first();

      $menteeName = $mentee?->name ?? 'le mentoré';
      $mentorPath = $assignment
        ? '/mentor/mentore/'.$assignment->id
        : '/mentor/formulaires';

      $this->notificationService->notifyWithEmail(
        $mentor,
        PortalNotificationType::AdminMessage,
        'TP publié pour le mentoré',
        'L\'administration a validé le TP « '.$tpTitle.' » pour '.$menteeName.'.',
        $mentorPath,
        'Voir la fiche',
        ['submission_id' => $submission->id],
      );
    }

    return $submission->fresh();
  }

  /**
   * Remises effectuées par le mentor pour un mentoré.
   *
   * @return Collection<int, AssignmentSubmission>
   */
  public function mentorSubmissionsForMentee(User $mentor, User $mentee): Collection
  {
    return AssignmentSubmission::query()
      ->where('user_id', $mentee->id)
      ->where('submitted_by_user_id', $mentor->id)
      ->with(['assessment.chapter'])
      ->latest('submitted_at')
      ->get();
  }

  /**
   * Notifie l'administration d'une remise ou modification mentor.
   */
  private function notifyAdminOfMentorTp(
    AssignmentSubmission $submission,
    User $mentor,
    ?User $mentee,
    bool $isUpdate,
  ): void {
    $title = $isUpdate ? 'TP mentor modifié' : 'Nouveau TP à valider (mentor)';
    $body = $mentor->name.' a '.($isUpdate ? 'modifié' : 'soumis').' un TP pour '
      .($mentee?->name ?? 'un mentoré').' — '.$submission->assessment?->title;

    $this->adminNotificationService->notifyAdmins(
      $title,
      $body,
      url('/admin/tp-mentors'),
    );
  }
}
