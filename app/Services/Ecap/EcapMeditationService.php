<?php

namespace App\Services\Ecap;

use App\Enums\EcapVacationRole;
use App\Models\AcademicSession;
use App\Models\EcapMeditationSubmission;
use App\Models\EcapMeditationTemplate;
use App\Models\EcapStaffAssignment;
use App\Models\Enrollment;
use App\Models\User;
use App\Support\UserPresentation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Cahiers de méditation ECAP : modèle modérateur, remise fidèle, correction.
 */
class EcapMeditationService
{
  /**
   * @param  EcapPeriodAccessService  $periodAccessService  Session ECAP
   * @param  VacationQuestionService  $vacationQuestionService  Session via profil
   */
  public function __construct(
    private readonly EcapPeriodAccessService $periodAccessService,
    private readonly VacationQuestionService $vacationQuestionService,
  ) {}

  /**
   * Modèles publiés pour la session (et vacation) du fidèle.
   *
   * @return array<int, array<string, mixed>>
   */
  public function templatesForStudent(User $user): array
  {
    $session = $this->resolveStudentSession($user);

    if ($session === null) {
      return [];
    }

    $vacationId = $user->profile?->session_vacation_id;

    return EcapMeditationTemplate::query()
      ->where('academic_session_id', $session->id)
      ->where('is_published', true)
      ->where(function ($query) use ($vacationId) {
        $query->whereNull('session_vacation_id');

        if ($vacationId !== null) {
          $query->orWhere('session_vacation_id', $vacationId);
        }
      })
      ->with(['courseModule', 'sessionVacation', 'submissions' => fn ($query) => $query->where('user_id', $user->id)])
      ->orderByDesc('created_at')
      ->get()
      ->map(fn (EcapMeditationTemplate $template) => [
        'id' => $template->id,
        'title' => $template->title,
        'instructions' => $template->instructions,
        'module_name' => $template->courseModule?->name,
        'vacation_name' => $template->sessionVacation?->name,
        'scope_label' => $template->sessionVacation?->name ?? 'Toute la session',
        'due_on' => $template->due_on?->format('d/m/Y'),
        'template_file_url' => $template->template_file_url,
        'template_file_name' => $template->template_file_path ? basename($template->template_file_path) : null,
        'submission' => $this->mapSubmission($template->submissions->first()),
      ])
      ->values()
      ->all();
  }

  /**
   * Remise ou mise à jour du travail du fidèle.
   */
  public function submitForStudent(User $user, int $templateId, string $answerText, ?UploadedFile $file = null): EcapMeditationSubmission
  {
    $session = $this->resolveStudentSession($user);

    if ($session === null) {
      throw ValidationException::withMessages([
        'body' => 'Session ECAP introuvable.',
      ]);
    }

    $vacationId = $user->profile?->session_vacation_id;

    $template = EcapMeditationTemplate::query()
      ->whereKey($templateId)
      ->where('academic_session_id', $session->id)
      ->where('is_published', true)
      ->where(function ($query) use ($vacationId) {
        $query->whereNull('session_vacation_id');

        if ($vacationId !== null) {
          $query->orWhere('session_vacation_id', $vacationId);
        }
      })
      ->firstOrFail();

    $enrollment = Enrollment::query()
      ->where('user_id', $user->id)
      ->where('academic_session_id', $session->id)
      ->first();

    $existing = EcapMeditationSubmission::query()
      ->where('ecap_meditation_template_id', $template->id)
      ->where('user_id', $user->id)
      ->first();

    $filePath = $existing?->file_path;

    if ($file !== null) {
      if ($filePath) {
        Storage::disk('public')->delete($filePath);
      }

      $filePath = $file->store('ecap/meditation/submissions', 'public');
    }

    return EcapMeditationSubmission::query()->updateOrCreate(
      [
        'ecap_meditation_template_id' => $template->id,
        'user_id' => $user->id,
      ],
      [
        'enrollment_id' => $enrollment?->id,
        'answer_text' => $answerText,
        'file_path' => $filePath,
        'status' => 'submitted',
        'submitted_at' => now(),
        'moderator_notes' => $existing?->status === 'rejected' ? null : $existing?->moderator_notes,
        'reviewed_by_user_id' => $existing?->status === 'rejected' ? null : $existing?->reviewed_by_user_id,
        'reviewed_at' => $existing?->status === 'rejected' ? null : $existing?->reviewed_at,
      ],
    );
  }

  /**
   * Liste modérateur : modèles + remises.
   *
   * @return array{templates: array, pending_submissions: array, moderator_scope: array}
   */
  public function moderatorDashboard(User $moderator): array
  {
    $scope = $this->moderatorScope($moderator);
    $sessionIds = $scope['session_ids'];

    $templates = EcapMeditationTemplate::query()
      ->whereIn('academic_session_id', $sessionIds)
      ->when($scope['vacation_ids']->isNotEmpty(), function ($query) use ($scope) {
        $query->where(function ($inner) use ($scope) {
          $inner->whereNull('session_vacation_id')
            ->orWhereIn('session_vacation_id', $scope['vacation_ids']);
        });
      })
      ->with(['courseModule', 'sessionVacation', 'submissions.user.profile'])
      ->latest()
      ->get();

    $pending = EcapMeditationSubmission::query()
      ->whereHas('template', fn ($query) => $query->whereIn('academic_session_id', $sessionIds))
      ->where('status', 'submitted')
      ->with(['user.profile', 'template.sessionVacation'])
      ->latest('submitted_at')
      ->get()
      ->filter(fn (EcapMeditationSubmission $row) => $this->moderatorCanSeeSubmission($moderator, $row, $scope));

    return [
      'moderator_scope' => [
        'covers_whole_session' => $scope['covers_whole_session'],
        'vacation_names' => $scope['vacation_names'],
      ],
      'templates' => $templates->map(fn (EcapMeditationTemplate $template) => [
        'id' => $template->id,
        'title' => $template->title,
        'module_name' => $template->courseModule?->name,
        'scope_label' => $template->sessionVacation?->name ?? 'Toute la session',
        'due_on' => $template->due_on?->format('d/m/Y'),
        'template_file_url' => $template->template_file_url,
        'template_file_name' => $template->template_file_path ? basename($template->template_file_path) : null,
        'submissions_count' => $template->submissions->count(),
        'submissions' => $template->submissions
          ->sortByDesc('submitted_at')
          ->map(fn (EcapMeditationSubmission $row) => $this->mapSubmissionForModerator($row))
          ->values()
          ->all(),
      ])->values()->all(),
      'pending_submissions' => $pending->map(fn (EcapMeditationSubmission $row) => $this->mapSubmissionForModerator($row))->values()->all(),
    ];
  }

  /**
   * Publie un modèle de cahier (modérateur).
   */
  public function createTemplate(
    User $moderator,
    int $sessionId,
    string $title,
    ?string $instructions = null,
    ?int $courseModuleId = null,
    ?string $dueOn = null,
    ?UploadedFile $templateFile = null,
  ): EcapMeditationTemplate {
    $this->assertModeratorOfSession($moderator, $sessionId);

    $vacationId = $this->resolveTemplateVacationScope($moderator, $sessionId);

    return EcapMeditationTemplate::query()->create([
      'academic_session_id' => $sessionId,
      'session_vacation_id' => $vacationId,
      'course_module_id' => $courseModuleId,
      'created_by_user_id' => $moderator->id,
      'title' => $title,
      'instructions' => $instructions,
      'template_file_path' => $templateFile?->store('ecap/meditation/templates', 'public'),
      'due_on' => $dueOn,
      'is_published' => true,
    ]);
  }

  /**
   * Corrige une remise (modérateur).
   */
  public function reviewSubmission(
    User $moderator,
    EcapMeditationSubmission $submission,
    string $status,
    ?string $notes = null,
  ): EcapMeditationSubmission {
    $sessionId = $submission->template?->academic_session_id;

    if ($sessionId === null) {
      abort(404);
    }

    $this->assertModeratorOfSession($moderator, $sessionId);

    $submission->update([
      'status' => $status,
      'moderator_notes' => $notes,
      'reviewed_by_user_id' => $moderator->id,
      'reviewed_at' => now(),
    ]);

    return $submission->fresh();
  }

  /**
   * Session ECAP du fidèle (profil puis inscription).
   */
  private function resolveStudentSession(User $user): ?AcademicSession
  {
    return $this->vacationQuestionService->studentSession($user)
      ?? $this->periodAccessService->userEcapSession($user);
  }

  /**
   * Portée modérateur : session entière ou vacation(s) assignée(s).
   *
   * @return array{session_ids: Collection<int, int>, vacation_ids: Collection<int, int>, covers_whole_session: bool, vacation_names: array<int, string>}
   */
  private function moderatorScope(User $moderator): array
  {
    $assignments = EcapStaffAssignment::query()
      ->where('user_id', $moderator->id)
      ->where('role', EcapVacationRole::Moderator->value)
      ->where('is_active', true)
      ->with('sessionVacation')
      ->get();

    $coversWholeSession = $assignments->contains(fn (EcapStaffAssignment $row) => $row->session_vacation_id === null);

    $vacationIds = $coversWholeSession
      ? collect()
      : $assignments->pluck('session_vacation_id')->filter()->unique()->values();

    $vacationNames = $assignments
      ->filter(fn (EcapStaffAssignment $row) => $row->sessionVacation !== null)
      ->map(fn (EcapStaffAssignment $row) => $row->sessionVacation->name)
      ->unique()
      ->values()
      ->all();

    return [
      'session_ids' => $assignments->pluck('academic_session_id')->unique()->values(),
      'vacation_ids' => $vacationIds,
      'covers_whole_session' => $coversWholeSession,
      'vacation_names' => $vacationNames,
    ];
  }

  /**
   * Vacation cible lors de la publication d'un modèle.
   */
  private function resolveTemplateVacationScope(User $moderator, int $sessionId): ?int
  {
    $assignments = EcapStaffAssignment::query()
      ->where('user_id', $moderator->id)
      ->where('academic_session_id', $sessionId)
      ->where('role', EcapVacationRole::Moderator->value)
      ->where('is_active', true)
      ->get();

    if ($assignments->contains(fn (EcapStaffAssignment $row) => $row->session_vacation_id === null)) {
      return null;
    }

    $vacationIds = $assignments->pluck('session_vacation_id')->filter()->unique();

    return $vacationIds->count() === 1 ? (int) $vacationIds->first() : null;
  }

  /**
   * Le modérateur peut voir cette remise selon sa portée vacation.
   */
  private function moderatorCanSeeSubmission(User $moderator, EcapMeditationSubmission $submission, array $scope): bool
  {
    if ($scope['covers_whole_session']) {
      return true;
    }

    $templateVacationId = $submission->template?->session_vacation_id;

    if ($templateVacationId === null) {
      return true;
    }

    return $scope['vacation_ids']->contains($templateVacationId);
  }

  /**
   * @return Collection<int, int>
   */
  private function moderatorSessionIds(User $moderator): Collection
  {
    return EcapStaffAssignment::query()
      ->where('user_id', $moderator->id)
      ->where('role', EcapVacationRole::Moderator->value)
      ->where('is_active', true)
      ->pluck('academic_session_id');
  }

  /**
   * Vérifie le rôle modérateur sur la session.
   */
  private function assertModeratorOfSession(User $moderator, int $sessionId): void
  {
    $allowed = EcapStaffAssignment::query()
      ->where('user_id', $moderator->id)
      ->where('academic_session_id', $sessionId)
      ->where('role', EcapVacationRole::Moderator->value)
      ->where('is_active', true)
      ->exists();

    if (! $allowed) {
      abort(403);
    }
  }

  /**
   * @return array<string, mixed>
   */
  private function mapSubmissionForModerator(EcapMeditationSubmission $submission): array
  {
    $presentation = UserPresentation::for($submission->user);

    return [
      'id' => $submission->id,
      'student_name' => $presentation['name'],
      'student_avatar_url' => $presentation['avatar_url'],
      'student_initials' => $presentation['initials'],
      'template_title' => $submission->template?->title,
      'template_scope' => $submission->template?->sessionVacation?->name ?? 'Toute la session',
      'status' => $submission->status,
      'submitted_at' => $submission->submitted_at?->format('d/m/Y H:i'),
      'answer_text' => $submission->answer_text,
      'file_url' => $submission->file_url,
      'file_name' => $submission->file_path ? basename($submission->file_path) : null,
    ];
  }

  /**
   * @return array<string, mixed>|null
   */
  private function mapSubmission(?EcapMeditationSubmission $submission): ?array
  {
    if ($submission === null) {
      return null;
    }

    return [
      'id' => $submission->id,
      'status' => $submission->status,
      'answer_text' => $submission->answer_text,
      'file_url' => $submission->file_url,
      'file_name' => $submission->file_path ? basename($submission->file_path) : null,
      'moderator_notes' => $submission->moderator_notes,
      'submitted_at' => $submission->submitted_at?->format('d/m/Y H:i'),
      'reviewed_at' => $submission->reviewed_at?->format('d/m/Y H:i'),
    ];
  }
}
