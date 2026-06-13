<?php

namespace App\Services\Ecap;

use App\Enums\PeriodContentType;
use App\Models\AcademicSession;
use App\Models\EcapMeditationTemplate;
use App\Models\EcapStaffAssignment;
use App\Models\LearningGroup;
use App\Models\SessionModuleSchedule;
use App\Models\SessionPeriod;
use App\Models\SessionPeriodContent;
use App\Models\SessionVacation;
use Illuminate\Support\Facades\DB;

/**
 * Recopie la configuration d'une session ECAP vers une nouvelle session.
 */
class DuplicateEcapSessionConfigurationService
{
  /**
   * Duplique périodes, vacations, calendrier, groupes et affectations acteurs.
   *
   * @return array{periods: int, vacations: int, schedules: int, staff: int, groups: int, period_contents: int, meditations: int}
   */
  public function duplicateFromSession(
    AcademicSession $target,
    AcademicSession $source,
    ?PedagogicalCloneMaps $maps = null,
    ?DuplicateEcapSessionOptions $options = null,
  ): array {
    if (! $source->isEcap() || ! $target->isEcap()) {
      throw new \InvalidArgumentException('Seules les sessions ECAP peuvent être dupliquées.');
    }

    if ($source->is($target)) {
      throw new \InvalidArgumentException('Choisissez une session source différente de la session cible.');
    }

    $options ??= new DuplicateEcapSessionOptions();

    $counts = [
      'periods' => 0,
      'vacations' => 0,
      'schedules' => 0,
      'staff' => 0,
      'groups' => 0,
      'period_contents' => 0,
      'meditations' => 0,
    ];

    DB::transaction(function () use ($source, $target, $maps, $options, &$counts): void {
      $periodMap = [];

      foreach ($source->sessionPeriods()->orderBy('sort_order')->get() as $period) {
        $newPeriod = SessionPeriod::query()->create([
          'academic_session_id' => $target->id,
          'type' => $period->type,
          'name' => $period->name,
          'starts_on' => $period->starts_on,
          'ends_on' => $period->ends_on,
          'sort_order' => $period->sort_order,
          'is_active' => $period->is_active,
        ]);

        $periodMap[$period->id] = $newPeriod->id;
        $counts['periods']++;

        foreach ($period->contents as $content) {
          SessionPeriodContent::query()->create([
            'session_period_id' => $newPeriod->id,
            'content_type' => $content->content_type,
            'content_id' => $this->remapPeriodContentId($content->content_type, (int) $content->content_id, $maps),
            'sort_order' => $content->sort_order,
            'label' => $content->label,
          ]);
          $counts['period_contents']++;
        }
      }

      $vacationMap = [];

      foreach ($source->sessionVacations()->orderBy('sort_order')->get() as $vacation) {
        $newVacation = SessionVacation::query()->create([
          'academic_session_id' => $target->id,
          'name' => $vacation->name,
          'code' => $vacation->code,
          'time_starts' => $vacation->time_starts,
          'time_ends' => $vacation->time_ends,
          'capacity_max' => $vacation->capacity_max,
          'sort_order' => $vacation->sort_order,
          'is_active' => $vacation->is_active,
        ]);

        $vacationMap[$vacation->id] = $newVacation->id;
        $counts['vacations']++;
      }

      foreach ($source->moduleSchedules as $schedule) {
        SessionModuleSchedule::query()->create([
          'academic_session_id' => $target->id,
          'item_type' => $schedule->item_type,
          'course_module_id' => $maps?->moduleId($schedule->course_module_id),
          'session_period_id' => $schedule->session_period_id
            ? ($periodMap[$schedule->session_period_id] ?? null)
            : null,
          'title' => $schedule->title,
          'description' => $schedule->description,
          'recurrence' => $schedule->recurrence,
          'starts_on' => $schedule->starts_on,
          'ends_on' => $schedule->ends_on,
          'sort_order' => $schedule->sort_order,
        ]);
        $counts['schedules']++;
      }

      foreach ($source->ecapStaffAssignments()->where('is_active', true)->get() as $assignment) {
        $vacationId = $assignment->session_vacation_id
          ? ($vacationMap[$assignment->session_vacation_id] ?? null)
          : null;

        EcapStaffAssignment::query()->firstOrCreate(
          [
            'academic_session_id' => $target->id,
            'user_id' => $assignment->user_id,
            'role' => $assignment->role,
            'session_vacation_id' => $vacationId,
            'course_module_id' => $maps?->moduleId($assignment->course_module_id),
          ],
          [
            'is_active' => true,
            'notes' => $assignment->notes,
          ],
        );
        $counts['staff']++;
      }

      foreach ($source->learningGroups()->orderBy('sort_order')->get() as $group) {
        LearningGroup::query()->create([
          'academic_session_id' => $target->id,
          'name' => $group->name,
          'sort_order' => $group->sort_order,
        ]);
        $counts['groups']++;
      }

      if ($options->meditations) {
        foreach ($source->ecapMeditationTemplates()->get() as $template) {
          EcapMeditationTemplate::query()->create([
            'academic_session_id' => $target->id,
            'session_vacation_id' => $template->session_vacation_id
              ? ($vacationMap[$template->session_vacation_id] ?? null)
              : null,
            'course_module_id' => $maps?->moduleId($template->course_module_id),
            'created_by_user_id' => $template->created_by_user_id,
            'title' => $template->title,
            'instructions' => $template->instructions,
            'template_file_path' => $template->template_file_path,
            'due_on' => $template->due_on,
            'is_published' => $options->publishClonedContent && $template->is_published,
          ]);
          $counts['meditations']++;
        }
      }
    });

    return $counts;
  }

  /**
   * Remappe l'identifiant d'un contenu de période selon le clonage pédagogique.
   */
  private function remapPeriodContentId(PeriodContentType $type, int $contentId, ?PedagogicalCloneMaps $maps): int
  {
    if ($maps === null) {
      return $contentId;
    }

    return match ($type) {
      PeriodContentType::CourseModule => $maps->moduleId($contentId) ?? $contentId,
      PeriodContentType::Chapter => $maps->chapterId($contentId) ?? $contentId,
      PeriodContentType::Assessment => $maps->assessmentId($contentId) ?? $contentId,
    };
  }
}
