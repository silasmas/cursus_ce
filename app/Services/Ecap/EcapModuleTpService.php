<?php

namespace App\Services\Ecap;

use App\Enums\AssessmentType;
use App\Enums\EcapVacationRole;
use App\Models\Assessment;
use App\Models\AssignmentSubmission;
use App\Models\Chapter;
use App\Models\CourseModule;
use App\Models\EcapStaffAssignment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Gestion des TP modèles déposés par les enseignants et corrigés par les superviseurs.
 */
class EcapModuleTpService
{
  /**
   * Modules où l'utilisateur est enseignant actif.
   *
   * @return Collection<int, CourseModule>
   */
  public function teacherModules(User $user): Collection
  {
    $moduleIds = EcapStaffAssignment::query()
      ->where('user_id', $user->id)
      ->where('role', EcapVacationRole::Teacher->value)
      ->where('is_active', true)
      ->whereNotNull('course_module_id')
      ->pluck('course_module_id');

    return CourseModule::query()
      ->whereIn('id', $moduleIds)
      ->with('course')
      ->orderBy('sort_order')
      ->get();
  }

  /**
   * Modules supervisés par l'utilisateur.
   *
   * @return Collection<int, CourseModule>
   */
  public function supervisorModules(User $user): Collection
  {
    $moduleIds = EcapStaffAssignment::query()
      ->where('user_id', $user->id)
      ->where('role', EcapVacationRole::Supervisor->value)
      ->where('is_active', true)
      ->whereNotNull('course_module_id')
      ->pluck('course_module_id');

    return CourseModule::query()
      ->whereIn('id', $moduleIds)
      ->with('course')
      ->orderBy('sort_order')
      ->get();
  }

  /**
   * Crée un TP modèle pour un module enseigné.
   */
  public function createTeacherTp(User $teacher, int $courseModuleId, string $title, string $instructions, ?int $chapterId = null): Assessment
  {
    $this->assertTeacherOfModule($teacher, $courseModuleId);

    $module = CourseModule::query()->with('course.program')->findOrFail($courseModuleId);
    $program = $module->course?->program;

    if ($program?->slug !== 'ecap') {
      throw ValidationException::withMessages([
        'course_module_id' => 'Seuls les modules ECAP acceptent un TP enseignant.',
      ]);
    }

    if ($chapterId !== null) {
      $chapter = Chapter::query()
        ->whereKey($chapterId)
        ->where('course_module_id', $courseModuleId)
        ->first();

      if ($chapter === null) {
        throw ValidationException::withMessages([
          'chapter_id' => 'Ce chapitre n\'appartient pas au module sélectionné.',
        ]);
      }
    }

    $assessment = Assessment::query()->create([
      'program_id' => $program->id,
      'course_id' => $module->course_id,
      'course_module_id' => $module->id,
      'chapter_id' => $chapterId,
      'title' => $title,
      'type' => AssessmentType::Tp->value,
      'is_published' => true,
      'passing_score' => 60,
    ]);

    if (filled($instructions)) {
      \App\Models\Question::query()->create([
        'assessment_id' => $assessment->id,
        'stem' => $instructions,
        'type' => \App\Enums\QuestionType::Written->value,
        'sort_order' => 1,
        'points' => 100,
      ]);
    }

    return $assessment;
  }

  /**
   * Remises de TP pour correction superviseur.
   *
   * @return array<int, array<string, mixed>>
   */
  public function pendingSubmissionsForSupervisor(User $supervisor): array
  {
    $moduleIds = $this->supervisorModules($supervisor)->pluck('id');

    if ($moduleIds->isEmpty()) {
      return [];
    }

    return AssignmentSubmission::query()
      ->whereHas('assessment', fn ($query) => $query
        ->where('type', AssessmentType::Tp->value)
        ->whereIn('course_module_id', $moduleIds))
      ->whereNotNull('submitted_at')
      ->whereNull('graded_at')
      ->with(['user', 'assessment.courseModule', 'assessment.chapter'])
      ->latest('submitted_at')
      ->get()
      ->map(fn (AssignmentSubmission $submission) => [
        'id' => $submission->id,
        'student_name' => $submission->user?->name,
        'tp_title' => $submission->assessment?->title,
        'module_name' => $submission->assessment?->courseModule?->name,
        'chapter_title' => $submission->assessment?->chapter?->title,
        'submitted_at' => $submission->submitted_at?->format('d/m/Y H:i'),
        'answer_text' => $submission->answer_text,
        'file_url' => $submission->file_url,
        'status' => $submission->status,
      ])
      ->values()
      ->all();
  }

  /**
   * Corrige une remise de TP (superviseur).
   */
  public function gradeSubmission(User $supervisor, AssignmentSubmission $submission, float $grade, ?string $notes = null): AssignmentSubmission
  {
    $moduleId = $submission->assessment?->course_module_id;

    if ($moduleId === null || ! $this->supervisorModules($supervisor)->contains('id', $moduleId)) {
      throw ValidationException::withMessages([
        'submission' => 'Vous n\'êtes pas superviseur de ce module.',
      ]);
    }

    $submission->update([
      'grade' => $grade,
      'grader_notes' => $notes,
      'grader_id' => $supervisor->id,
      'graded_at' => now(),
      'status' => $grade >= ($submission->assessment?->passing_score ?? 60) ? 'passed' : 'failed',
    ]);

    return $submission->fresh();
  }

  /**
   * TP publiés par l'enseignant pour ses modules.
   *
   * @return array<int, array<string, mixed>>
   */
  public function teacherTpList(User $teacher): array
  {
    $moduleIds = $this->teacherModules($teacher)->pluck('id');

    return Assessment::query()
      ->where('type', AssessmentType::Tp->value)
      ->whereIn('course_module_id', $moduleIds)
      ->with(['courseModule', 'chapter'])
      ->latest()
      ->get()
      ->map(fn (Assessment $assessment) => [
        'id' => $assessment->id,
        'title' => $assessment->title,
        'module_name' => $assessment->courseModule?->name,
        'chapter_title' => $assessment->chapter?->title,
        'submissions_count' => $assessment->assignmentSubmissions()->whereNotNull('submitted_at')->count(),
      ])
      ->values()
      ->all();
  }

  /**
   * Vérifie que l'utilisateur enseigne le module.
   */
  private function assertTeacherOfModule(User $teacher, int $courseModuleId): void
  {
    $allowed = EcapStaffAssignment::query()
      ->where('user_id', $teacher->id)
      ->where('role', EcapVacationRole::Teacher->value)
      ->where('course_module_id', $courseModuleId)
      ->where('is_active', true)
      ->exists();

    if (! $allowed) {
      throw ValidationException::withMessages([
        'course_module_id' => 'Vous n\'êtes pas enseignant de ce module.',
      ]);
    }
  }
}
