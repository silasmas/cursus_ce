<?php

namespace App\Services\Student;

use App\Enums\CalendarItemType;
use App\Support\UserPresentation;
use App\Models\AcademicSession;
use App\Models\Chapter;
use App\Models\ChapterProgress;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\SessionModuleSchedule;
use App\Models\User;
use App\Services\Ecap\EcapModuleCalendarAccessService;
use App\Services\Ecap\EcapModuleCountdownService;
use App\Services\Ecap\EcapPeriodAccessService;
use App\Services\Program\ProgramSettingService;
use Illuminate\Support\Collection;

/**
 * Construit le parcours de formation avec déblocage progressif des étapes.
 */
class FormationJourneyService
{
  /**
   * @param  ChapterGateService  $gateService  Métadonnées tests / TP par étape
   * @param  ModuleExitQuizService  $moduleExitQuizService  Quiz fin de module ECAP (M5)
   */
  public function __construct(
    private readonly ChapterGateService $gateService,
    private readonly ModuleExitQuizService $moduleExitQuizService,
    private readonly EcapModuleCountdownService $moduleCountdownService,
    private readonly EcapModuleCalendarAccessService $moduleCalendarAccessService,
    private readonly EcapPeriodAccessService $periodAccessService,
    private readonly ProgramSettingService $programSettingService,
  ) {}

  /**
   * Retourne le parcours complet de formation pour un fidèle.
   *
   * @param  User  $user  Fidèle connecté
   * @return array{program: array|null, modules: array<int, array>, steps: array<int, array>, stats: array}
   */
  public function forUser(User $user): array
  {
    $enrollment = $this->resolveEnrollment($user);
    $program = $this->resolveProgram($user, $enrollment);

    if (! $program) {
      return $this->emptyResult();
    }

    return $this->buildJourney($user, $program, $enrollment);
  }

  /**
   * Retourne le parcours pour un programme (cursus) donné.
   *
   * @param  User  $user  Fidèle connecté
   * @param  Program  $program  Programme / cursus cible
   * @return array{program: array|null, modules: array<int, array>, steps: array<int, array>, stats: array}
   */
  public function forProgram(User $user, Program $program): array
  {
    $enrollment = $user->enrollments()
      ->where('program_id', $program->id)
      ->with(['course', 'academicSession'])
      ->latest('enrolled_at')
      ->first();

    return $this->buildJourney($user, $program, $enrollment);
  }

  /**
   * Construit le parcours à partir d'un programme et d'une inscription éventuelle.
   *
   * @return array{program: array|null, enrollment: array|null, modules: array, steps: array, stats: array}
   */
  private function buildJourney(User $user, Program $program, ?Enrollment $enrollment): array
  {
    $chapters = $this->loadPublishedChapters($user, $program, $enrollment);
    $progressMap = $this->loadProgressMap($enrollment);
    $steps = $this->buildSteps($user, $program, $chapters, $progressMap);
    $modules = $this->groupStepsByModule($steps, $user, $program, $enrollment);
    $modules = $this->mergeScheduledEcapModules($modules, $user, $program, $enrollment);

    $completed = collect($steps)->where('status', 'completed')->count();
    $available = collect($steps)->whereIn('status', ['available', 'in_progress'])->count();
    $locked = collect($steps)->where('status', 'locked')->count();
    $total = count($steps);

    return [
      'program' => [
        'id' => $program->id,
        'name' => $program->name,
        'description' => $program->description,
        'slug' => $program->slug,
      ],
      'enrollment' => $enrollment ? [
        'id' => $enrollment->id,
        'course' => $enrollment->course?->name,
        'session' => $enrollment->academicSession?->name,
      ] : null,
      'modules' => $modules,
      'steps' => $steps,
      'stats' => [
        'total' => $total,
        'completed' => $completed,
        'available' => $available,
        'locked' => $locked,
        'progress' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
      ],
    ];
  }

  /**
   * @return array{program: null, modules: array, steps: array, stats: array}
   */
  private function emptyResult(): array
  {
    return [
      'program' => null,
      'modules' => [],
      'steps' => [],
      'stats' => [
        'total' => 0,
        'completed' => 0,
        'available' => 0,
        'locked' => 0,
        'progress' => 0,
      ],
    ];
  }

  /**
   * Récupère l'inscription la plus récente du fidèle.
   */
  private function resolveEnrollment(User $user): ?Enrollment
  {
    $ecapEnrollment = $user->enrollments()
      ->with(['program', 'course', 'academicSession'])
      ->whereHas('program', fn ($query) => $query->where('slug', 'ecap'))
      ->latest('enrolled_at')
      ->first();

    if ($ecapEnrollment !== null) {
      return $ecapEnrollment;
    }

    return $user->enrollments()
      ->with(['program', 'course', 'academicSession'])
      ->latest('enrolled_at')
      ->first();
  }

  /**
   * Session académique ECAP du fidèle ou de l'acteur (inscription, profil, affectation).
   */
  private function resolveAcademicSessionId(User $user, ?Enrollment $enrollment): ?int
  {
    if ($enrollment?->academic_session_id) {
      return (int) $enrollment->academic_session_id;
    }

    if ($user->profile?->academic_session_id) {
      return (int) $user->profile->academic_session_id;
    }

    $session = $this->periodAccessService->userEcapSession($user);

    return $session?->id;
  }

  /**
   * Détermine le programme actif du fidèle.
   */
  private function resolveProgram(User $user, ?Enrollment $enrollment): ?Program
  {
    if ($enrollment?->program) {
      return $enrollment->program;
    }

    $sessionId = $user->profile?->academic_session_id;

    if ($sessionId) {
      $session = AcademicSession::query()->with('program')->find($sessionId);

      return $session?->program;
    }

    return Program::query()
      ->where('is_active', true)
      ->orderBy('sort_order')
      ->first();
  }

  /**
   * Charge les chapitres publiés du programme, filtrés par cours ou session ECAP.
   *
   * @return Collection<int, Chapter>
   */
  private function loadPublishedChapters(User $user, Program $program, ?Enrollment $enrollment): Collection
  {
    $query = Chapter::query()
      ->where('is_published', true)
      ->whereHas('course', function ($inner) use ($program) {
        $inner->where('program_id', $program->id)->where('is_published', true);
      });

    if ($program->slug === 'ecap') {
      $courseId = $this->resolveEcapCourseId($program, $enrollment);

      if ($courseId !== null) {
        $query->where('course_id', $courseId);
      }

      $sessionId = $this->resolveAcademicSessionId($user, $enrollment);

      if ($sessionId !== null) {
        $moduleIds = $this->openModuleIdsForSession($sessionId);

        if ($moduleIds->isNotEmpty()) {
          $query->whereIn('course_module_id', $moduleIds);
        }
      }
    } elseif ($enrollment?->course_id) {
      $query->where('course_id', $enrollment->course_id);
    }

    return $query
      ->with(['courseModule', 'course', 'instructor.mentorProfile'])
      ->get()
      ->sortBy(fn (Chapter $chapter) => sprintf(
        '%04d-%04d-%04d',
        $chapter->course?->sort_order ?? 0,
        $chapter->courseModule?->sort_order ?? 0,
        $chapter->sort_order,
      ))
      ->values();
  }

  /**
   * Identifiants des modules ECAP dont la fenêtre calendrier a commencé.
   *
   * @return Collection<int, int>
   */
  private function openModuleIdsForSession(int $sessionId): Collection
  {
    $today = now()->startOfDay();

    return SessionModuleSchedule::query()
      ->where('academic_session_id', $sessionId)
      ->where('item_type', CalendarItemType::Module)
      ->whereNotNull('course_module_id')
      ->orderBy('sort_order')
      ->get()
      ->filter(function (SessionModuleSchedule $schedule) use ($today): bool {
        if ($schedule->starts_on === null) {
          return true;
        }

        return $schedule->starts_on->copy()->startOfDay()->lte($today);
      })
      ->pluck('course_module_id')
      ->map(fn ($id) => (int) $id)
      ->unique()
      ->values();
  }

  /**
   * Détermine le cours ECAP à afficher (évite la duplication Module 1).
   */
  private function resolveEcapCourseId(Program $program, ?Enrollment $enrollment): ?int
  {
    if ($enrollment?->course_id) {
      return (int) $enrollment->course_id;
    }

    if ($enrollment?->academic_session_id) {
      $sessionCourseId = Course::query()
        ->where('program_id', $program->id)
        ->where('academic_session_id', $enrollment->academic_session_id)
        ->value('id');

      if ($sessionCourseId) {
        return (int) $sessionCourseId;
      }
    }

    $canonical = Course::query()
      ->where('program_id', $program->id)
      ->where('is_published', true)
      ->where('slug', 'fondamentaux-apollos')
      ->value('id');

    if ($canonical) {
      return (int) $canonical;
    }

    return Course::query()
      ->where('program_id', $program->id)
      ->where('is_published', true)
      ->orderBy('sort_order')
      ->value('id');
  }

  /**
   * @return Collection<int, ChapterProgress>
   */
  private function loadProgressMap(?Enrollment $enrollment): Collection
  {
    if (! $enrollment) {
      return collect();
    }

    return ChapterProgress::query()
      ->where('enrollment_id', $enrollment->id)
      ->get()
      ->keyBy('chapter_id');
  }

  /**
   * Construit la liste des étapes avec statut de déblocage.
   *
   * @param  Collection<int, Chapter>  $chapters
   * @param  Collection<int, ChapterProgress>  $progressMap
   * @return array<int, array<string, mixed>>
   */
  private function buildSteps(User $user, Program $program, Collection $chapters, Collection $progressMap): array
  {
    $steps = [];

    $linearProgression = $this->programSettingService->requiresLinearProgression($program);

    foreach ($chapters as $index => $chapter) {
      $progress = $progressMap->get($chapter->id);
      $isCompleted = $progress?->completed_at !== null;
      $isInProgress = $progress !== null && ! $isCompleted;
      $previousCompleted = ! $linearProgression
        || $index === 0
        || (
          $this->isChapterCompleted($chapters[$index - 1], $progressMap)
          && $this->moduleExitQuizService->moduleTransitionAllows(
            $user,
            $chapters[$index - 1],
            $chapter,
          )
        );

      $status = match (true) {
        $isCompleted => 'completed',
        ! $previousCompleted => 'locked',
        $isInProgress => 'in_progress',
        default => 'available',
      };

      $assessments = $this->gateService->stepAssessmentMeta($user, $chapter, $status);

      $steps[] = [
        'id' => $chapter->id,
        'order' => $index + 1,
        'title' => $chapter->title,
        'module' => $chapter->courseModule?->name ?? 'Module général',
        'course_module_id' => $chapter->course_module_id,
        'course' => $chapter->course?->name ?? 'Formation',
        'status' => $status,
        'completed_at' => $progress?->completed_at?->format('d/m/Y'),
        'has_started' => $progress !== null,
        'has_quiz' => $assessments['has_quiz'],
        'has_tp' => $assessments['has_tp'],
        'quiz_count' => $assessments['quiz_count'],
        'tp_count' => $assessments['tp_count'],
        'quiz_passed' => $assessments['quiz_passed'],
        'tp_status' => $assessments['tp_status'],
        'pending_labels' => $assessments['pending_labels'],
        'instructor' => $this->instructorPayload($chapter->instructor),
      ];
    }

    $steps = $this->applyStepNavigationFlags($steps);

    if ($program->slug === 'ecap') {
      $steps = $this->applyModuleClosedRules($user, $steps);
    }

    return $steps;
  }

  /**
   * Verrouille les chapitres non terminés lorsque la fenêtre calendaire du module est expirée.
   *
   * @param  array<int, array<string, mixed>>  $steps
   * @return array<int, array<string, mixed>>
   */
  private function applyModuleClosedRules(User $user, array $steps): array
  {
    foreach ($steps as &$step) {
      $moduleId = $step['course_module_id'] ?? null;

      if ($moduleId === null || ! $this->moduleCalendarAccessService->isModuleClosed($user, (int) $moduleId)) {
        continue;
      }

      if ($step['status'] === 'completed') {
        $step['module_closed_review'] = true;
        $step['is_reviewable'] = true;
        $step['is_focus'] = false;

        continue;
      }

      $step['status'] = 'locked';
      $step['lock_reason'] = 'module_closed';
      $step['is_focus'] = false;
      $step['is_reviewable'] = false;
    }
    unset($step);

    return $this->applyStepNavigationFlags($steps);
  }

  /**
   * Ajoute les indicateurs de navigation (étape courante vs reprise).
   *
   * @param  array<int, array<string, mixed>>  $steps
   * @return array<int, array<string, mixed>>
   */
  private function applyStepNavigationFlags(array $steps): array
  {
    $activeIndex = collect($steps)->search(
      fn (array $step) => in_array($step['status'], ['in_progress', 'available'], true),
    );

    if ($activeIndex === false) {
      $activeIndex = count($steps);
    }

    foreach ($steps as $index => &$step) {
      $isLocked = $step['status'] === 'locked';
      $isCompleted = $step['status'] === 'completed';
      $isPastActive = $index < $activeIndex;

      $step['is_focus'] = ! $isLocked && ! $isCompleted && $index === $activeIndex;
      $step['is_reviewable'] = ! $isLocked && ($isCompleted || $isPastActive);
    }

    return $steps;
  }

  /**
   * Vérifie si un chapitre est terminé.
   */
  private function isChapterCompleted(Chapter $chapter, Collection $progressMap): bool
  {
    $progress = $progressMap->get($chapter->id);

    return $progress?->completed_at !== null;
  }

  /**
   * Regroupe les étapes par module pour l'affichage.
   *
   * @param  array<int, array<string, mixed>>  $steps
   * @return array<int, array<string, mixed>>
   */
  private function groupStepsByModule(array $steps, User $user, Program $program, ?Enrollment $enrollment): array
  {
    $grouped = [];
    $moduleCountdowns = $program->slug === 'ecap'
      ? $this->moduleCountdownService->countdownsByModuleForUser($user)
      : [];
    $scheduleLabels = $program->slug === 'ecap'
      ? $this->scheduleLabelsByModuleId($user, $enrollment)
      : [];
    $scheduleMeta = $program->slug === 'ecap'
      ? $this->scheduleMetaByModuleId($user, $enrollment)
      : [];

    foreach ($steps as $step) {
      $moduleId = $step['course_module_id'] ?? null;
      $moduleName = ($moduleId !== null && isset($scheduleLabels[$moduleId]))
        ? $scheduleLabels[$moduleId]
        : $step['module'];
      $moduleKey = $moduleId ?? ('name:'.$moduleName);

      if (! isset($grouped[$moduleKey])) {
        $grouped[$moduleKey] = [
          'name' => $moduleName,
          'course_module_id' => $step['course_module_id'] ?? null,
          'steps' => [],
          'completed' => 0,
          'total' => 0,
          'has_quiz' => false,
          'has_tp' => false,
          'quiz_count' => 0,
          'tp_count' => 0,
          'module_exit_quiz' => null,
        ];
      }

      if ($moduleId !== null && isset($scheduleLabels[$moduleId])) {
        $grouped[$moduleKey]['name'] = $scheduleLabels[$moduleId];
      }

      $grouped[$moduleKey]['steps'][] = $step;
      $grouped[$moduleKey]['total']++;

      if ($step['has_quiz']) {
        $grouped[$moduleKey]['has_quiz'] = true;
        $grouped[$moduleKey]['quiz_count'] = ($grouped[$moduleKey]['quiz_count'] ?? 0) + ($step['quiz_count'] ?? 0);
      }

      if ($step['has_tp']) {
        $grouped[$moduleKey]['has_tp'] = true;
        $grouped[$moduleKey]['tp_count'] = ($grouped[$moduleKey]['tp_count'] ?? 0) + ($step['tp_count'] ?? 0);
      }

      if ($step['status'] === 'completed') {
        $grouped[$moduleKey]['completed']++;
      }
    }

    return array_values(array_map(function (array $module) use ($user, $moduleCountdowns, $scheduleMeta) {
      $module['progress'] = $module['total'] > 0
        ? (int) round(($module['completed'] / $module['total']) * 100)
        : 0;

      if ($module['course_module_id']) {
        $moduleId = (int) $module['course_module_id'];
        $module['module_exit_quiz'] = $this->moduleExitQuizService->summaryForModule(
          $user,
          $moduleId,
          $module,
        );
        $module['countdown'] = $moduleCountdowns[$moduleId] ?? null;
        $module = $this->applyScheduleMetaToModule($module, $scheduleMeta[$moduleId] ?? null);
      }

      return $module;
    }, $grouped));
  }

  /**
   * Métadonnées calendrier ECAP indexées par module de cours.
   *
   * @return array<int, array<string, mixed>>
   */
  private function scheduleMetaByModuleId(User $user, ?Enrollment $enrollment): array
  {
    $sessionId = $this->resolveAcademicSessionId($user, $enrollment);

    if ($sessionId === null) {
      return [];
    }

    $meta = [];

    SessionModuleSchedule::query()
      ->where('academic_session_id', $sessionId)
      ->where('item_type', CalendarItemType::Module)
      ->whereNotNull('course_module_id')
      ->orderBy('sort_order')
      ->get()
      ->each(function (SessionModuleSchedule $schedule) use (&$meta): void {
        $moduleId = (int) $schedule->course_module_id;
        $title = trim((string) ($schedule->title ?? ''));

        $meta[$moduleId] = [
          'title' => $title !== '' ? $title : ($schedule->courseModule?->name ?? 'Module ECAP'),
          'sort_order' => (int) $schedule->sort_order,
          'starts_on' => $schedule->starts_on,
          'ends_on' => $schedule->ends_on,
          'starts_on_label' => $schedule->starts_on?->format('d/m/Y'),
          'ends_on_label' => $schedule->ends_on?->format('d/m/Y'),
          'is_open' => $schedule->starts_on === null
            || $schedule->starts_on->copy()->startOfDay()->lte(now()->startOfDay()),
        ];
      });

    return $meta;
  }

  /**
   * Applique les dates et libellés calendrier à un module du parcours.
   *
   * @param  array<string, mixed>  $module
   * @param  array<string, mixed>|null  $scheduleMeta
   * @return array<string, mixed>
   */
  private function applyScheduleMetaToModule(array $module, ?array $scheduleMeta): array
  {
    if ($scheduleMeta === null) {
      return $module;
    }

    if (! empty($scheduleMeta['title'])) {
      $module['name'] = $scheduleMeta['title'];
    }

    $module['schedule_sort_order'] = $scheduleMeta['sort_order'];
    $module['schedule_starts_on'] = $scheduleMeta['starts_on_label'];
    $module['schedule_ends_on'] = $scheduleMeta['ends_on_label'];
    $module['schedule_is_open'] = $scheduleMeta['is_open'];

    return $module;
  }

  /**
   * Trie les modules ECAP selon le calendrier de session.
   *
   * @param  array<int, array<string, mixed>>  $modules
   * @return array<int, array<string, mixed>>
   */
  private function sortModulesBySchedule(array $modules, array $scheduleMeta): array
  {
    usort($modules, function (array $a, array $b) use ($scheduleMeta): int {
      $idA = (int) ($a['course_module_id'] ?? 0);
      $idB = (int) ($b['course_module_id'] ?? 0);
      $orderA = $a['schedule_sort_order']
        ?? ($scheduleMeta[$idA]['sort_order'] ?? PHP_INT_MAX);
      $orderB = $b['schedule_sort_order']
        ?? ($scheduleMeta[$idB]['sort_order'] ?? PHP_INT_MAX);

      if ($orderA !== $orderB) {
        return $orderA <=> $orderB;
      }

      return $idA <=> $idB;
    });

    return $modules;
  }

  /**
   * Libellés calendrier session indexés par module de cours.
   *
   * @return array<int, string>
   */
  private function scheduleLabelsByModuleId(User $user, ?Enrollment $enrollment): array
  {
    $sessionId = $this->resolveAcademicSessionId($user, $enrollment);

    if ($sessionId === null) {
      return [];
    }

    $labels = [];

    SessionModuleSchedule::query()
      ->where('academic_session_id', $sessionId)
      ->where('item_type', CalendarItemType::Module)
      ->whereNotNull('course_module_id')
      ->orderBy('sort_order')
      ->get()
      ->each(function (SessionModuleSchedule $schedule) use (&$labels): void {
        $title = trim((string) ($schedule->title ?? ''));

        if ($title !== '') {
          $labels[(int) $schedule->course_module_id] = $title;
        }
      });

    return $labels;
  }

  /**
   * Ajoute les modules planifiés au calendrier même sans chapitre publié encore.
   *
   * @param  array<int, array<string, mixed>>  $modules
   * @return array<int, array<string, mixed>>
   */
  private function mergeScheduledEcapModules(array $modules, User $user, Program $program, ?Enrollment $enrollment): array
  {
    if ($program->slug !== 'ecap') {
      return $modules;
    }

    $sessionId = $this->resolveAcademicSessionId($user, $enrollment);

    if ($sessionId === null) {
      return $modules;
    }

    $scheduleMeta = $this->scheduleMetaByModuleId($user, $enrollment);
    $moduleCountdowns = $this->moduleCountdownService->countdownsByModuleForUser($user);

    $indexed = collect($modules)->keyBy(function (array $module): string {
      $moduleId = $module['course_module_id'] ?? null;

      return $moduleId !== null ? 'id:'.$moduleId : 'name:'.$module['name'];
    });

    foreach ($scheduleMeta as $moduleId => $meta) {
      $key = 'id:'.$moduleId;

      if ($indexed->has($key)) {
        $indexed->put($key, $this->applyScheduleMetaToModule($indexed->get($key), $meta));

        continue;
      }

      $indexed->put($key, [
        'name' => $meta['title'],
        'course_module_id' => $moduleId,
        'steps' => [],
        'completed' => 0,
        'total' => 0,
        'has_quiz' => false,
        'has_tp' => false,
        'quiz_count' => 0,
        'tp_count' => 0,
        'progress' => 0,
        'module_exit_quiz' => $this->moduleExitQuizService->summaryForModule(
          $user,
          $moduleId,
          ['total' => 0, 'completed' => 0],
        ),
        'countdown' => $moduleCountdowns[$moduleId] ?? null,
        'schedule_sort_order' => $meta['sort_order'],
        'schedule_starts_on' => $meta['starts_on_label'],
        'schedule_ends_on' => $meta['ends_on_label'],
        'schedule_is_open' => $meta['is_open'],
      ]);
    }

    return $this->sortModulesBySchedule($indexed->values()->all(), $scheduleMeta);
  }

  /**
   * Données enseignant pour l'affichage sur une étape.
   *
   * @return array<string, mixed>|null
   */
  private function instructorPayload(?User $instructor): ?array
  {
    if (! $instructor) {
      return null;
    }

    $presentation = UserPresentation::for($instructor);

    return [
      'name' => $presentation['name'],
      'initials' => $presentation['initials'],
      'avatar_url' => $presentation['avatar_url'],
    ];
  }
}
