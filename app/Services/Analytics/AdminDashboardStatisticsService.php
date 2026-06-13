<?php

namespace App\Services\Analytics;

use App\Models\AcademicSession;
use App\Models\AssessmentAttempt;
use App\Models\AssignmentSubmission;
use App\Models\Certificate;
use App\Models\Chapter;
use App\Models\ChapterProgress;
use App\Models\Enrollment;
use App\Models\LoginEvent;
use App\Models\Program;
use App\Models\ProgramAccess;
use App\Models\User;
use App\Models\VacationQuestion;
use Illuminate\Support\Collection;

/**
 * Agrège les indicateurs du tableau de bord admin par cursus et globalement.
 */
class AdminDashboardStatisticsService
{
  /**
   * Palette de couleurs cohérente avec la charte PHILA-CE (graphiques).
   *
   * @var array<int, string>
   */
  private const CHART_COLORS = [
    '#F39200',
    '#3B82F6',
    '#10B981',
    '#8B5CF6',
    '#EF4444',
    '#06B6D4',
    '#84CC16',
    '#EC4899',
  ];

  /**
   * Retourne les indicateurs globaux affichés en tête de dashboard.
   *
   * @return array{
   *   members: int,
   *   active_enrollments: int,
   *   certificates_year: int,
   *   pending_validations: int,
   *   open_vacation_questions: int,
   *   tp_pending_grading: int,
   *   logins_30_days: int,
   *   active_ecap_sessions: int
   * }
   */
  public function globalStats(): array
  {
    $yearStart = now()->startOfYear();

    return [
      'members' => User::query()->count(),
      'active_enrollments' => Enrollment::query()->where('status', 'active')->count(),
      'certificates_year' => Certificate::query()->where('issued_at', '>=', $yearStart)->count(),
      'pending_validations' => ProgramAccess::query()->where('needs_admin_validation', true)->count(),
      'open_vacation_questions' => VacationQuestion::query()
        ->where('status', 'pending')
        ->count(),
      'tp_pending_grading' => AssignmentSubmission::query()
        ->whereNull('graded_at')
        ->whereNotNull('submitted_at')
        ->count(),
      'logins_30_days' => LoginEvent::query()
        ->where('guard', 'member')
        ->where('logged_in_at', '>=', now()->subDays(30))
        ->count(),
      'active_ecap_sessions' => AcademicSession::query()
        ->whereHas('program', fn ($query) => $query->where('slug', 'ecap'))
        ->where('is_active', true)
        ->count(),
    ];
  }

  /**
   * Synthèse détaillée par cursus actif (tableau dashboard).
   *
   * @return Collection<int, array{
   *   id: int,
   *   name: string,
   *   slug: string,
   *   is_open: bool,
   *   is_active: bool,
   *   enrollments_total: int,
   *   enrollments_active: int,
   *   access_open: int,
   *   access_completed: int,
   *   access_pending_validation: int,
   *   published_chapters: int,
   *   avg_progress_percent: float,
   *   certificates: int,
   *   assessments_month: int,
   *   tp_submissions_month: int,
   *   ecap_online: int|null,
   *   ecap_presentiel: int|null
   * }>
   */
  public function programSummaries(): Collection
  {
    $programs = Program::query()
      ->where('is_active', true)
      ->orderBy('sort_order')
      ->get();

    if ($programs->isEmpty()) {
      return collect();
    }

    $programIds = $programs->pluck('id');

    $enrollmentStats = Enrollment::query()
      ->select('program_id')
      ->selectRaw('COUNT(*) as total')
      ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active")
      ->whereIn('program_id', $programIds)
      ->groupBy('program_id')
      ->get()
      ->keyBy('program_id');

    $accessStats = ProgramAccess::query()
      ->select('program_id')
      ->selectRaw('SUM(CASE WHEN is_open = 1 THEN 1 ELSE 0 END) as open_count')
      ->selectRaw('SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed_count')
      ->selectRaw('SUM(CASE WHEN needs_admin_validation = 1 THEN 1 ELSE 0 END) as pending_validation')
      ->whereIn('program_id', $programIds)
      ->groupBy('program_id')
      ->get()
      ->keyBy('program_id');

    $chapterCounts = Chapter::query()
      ->select('courses.program_id')
      ->join('courses', 'courses.id', '=', 'chapters.course_id')
      ->where('chapters.is_published', true)
      ->whereIn('courses.program_id', $programIds)
      ->groupBy('courses.program_id')
      ->selectRaw('courses.program_id as program_id')
      ->selectRaw('COUNT(chapters.id) as total')
      ->get()
      ->keyBy('program_id');

    $certificateCounts = Certificate::query()
      ->select('program_id')
      ->selectRaw('COUNT(*) as total')
      ->whereIn('program_id', $programIds)
      ->groupBy('program_id')
      ->get()
      ->keyBy('program_id');

    $monthStart = now()->startOfMonth();

    $assessmentCounts = AssessmentAttempt::query()
      ->select('assessments.program_id')
      ->join('assessments', 'assessments.id', '=', 'assessment_attempts.assessment_id')
      ->whereIn('assessments.program_id', $programIds)
      ->whereNotNull('assessment_attempts.submitted_at')
      ->where('assessment_attempts.submitted_at', '>=', $monthStart)
      ->groupBy('assessments.program_id')
      ->selectRaw('assessments.program_id as program_id')
      ->selectRaw('COUNT(assessment_attempts.id) as total')
      ->get()
      ->keyBy('program_id');

    $tpCounts = AssignmentSubmission::query()
      ->select('assessments.program_id')
      ->join('assessments', 'assessments.id', '=', 'assignment_submissions.assessment_id')
      ->whereIn('assessments.program_id', $programIds)
      ->whereNotNull('assignment_submissions.submitted_at')
      ->where('assignment_submissions.submitted_at', '>=', $monthStart)
      ->groupBy('assessments.program_id')
      ->selectRaw('assessments.program_id as program_id')
      ->selectRaw('COUNT(assignment_submissions.id) as total')
      ->get()
      ->keyBy('program_id');

    $ecapProgramId = $programs->firstWhere('slug', 'ecap')?->id;

    $ecapModeStats = $ecapProgramId
      ? Enrollment::query()
        ->select('program_id')
        ->selectRaw('SUM(CASE WHEN is_online = 1 THEN 1 ELSE 0 END) as online')
        ->selectRaw('SUM(CASE WHEN is_online = 0 THEN 1 ELSE 0 END) as presentiel')
        ->where('program_id', $ecapProgramId)
        ->where('status', 'active')
        ->groupBy('program_id')
        ->first()
      : null;

    $progressRates = $this->averageProgressPercentByProgram($programIds);

    return $programs->map(function (Program $program) use (
      $enrollmentStats,
      $accessStats,
      $chapterCounts,
      $certificateCounts,
      $assessmentCounts,
      $tpCounts,
      $ecapModeStats,
      $progressRates,
    ): array {
      $enrollment = $enrollmentStats->get($program->id);
      $access = $accessStats->get($program->id);

      return [
        'id' => $program->id,
        'name' => $program->name,
        'slug' => $program->slug,
        'is_open' => (bool) $program->is_open,
        'is_active' => (bool) $program->is_active,
        'enrollments_total' => (int) ($enrollment->total ?? 0),
        'enrollments_active' => (int) ($enrollment->active ?? 0),
        'access_open' => (int) ($access->open_count ?? 0),
        'access_completed' => (int) ($access->completed_count ?? 0),
        'access_pending_validation' => (int) ($access->pending_validation ?? 0),
        'published_chapters' => (int) ($chapterCounts->get($program->id)?->total ?? 0),
        'avg_progress_percent' => (float) ($progressRates[$program->id] ?? 0),
        'certificates' => (int) ($certificateCounts->get($program->id)?->total ?? 0),
        'assessments_month' => (int) ($assessmentCounts->get($program->id)?->total ?? 0),
        'tp_submissions_month' => (int) ($tpCounts->get($program->id)?->total ?? 0),
        'ecap_online' => $program->slug === 'ecap' ? (int) ($ecapModeStats?->online ?? 0) : null,
        'ecap_presentiel' => $program->slug === 'ecap' ? (int) ($ecapModeStats?->presentiel ?? 0) : null,
      ];
    });
  }

  /**
   * Données pour le graphique en barres « inscriptions actives par cursus ».
   *
   * @return array{labels: array<int, string>, data: array<int, int>, colors: array<int, string>}
   */
  public function activeEnrollmentsChart(): array
  {
    $summaries = $this->programSummaries();

    return [
      'labels' => $summaries->pluck('name')->all(),
      'data' => $summaries->pluck('enrollments_active')->all(),
      'colors' => $this->colorsForCount($summaries->count()),
    ];
  }

  /**
   * Données pour le graphique en anneau « répartition des accès ouverts ».
   *
   * @return array{labels: array<int, string>, data: array<int, int>, colors: array<int, string>}
   */
  public function openAccessDistributionChart(): array
  {
    $summaries = $this->programSummaries()->filter(fn (array $row): bool => $row['access_open'] > 0);

    if ($summaries->isEmpty()) {
      return [
        'labels' => ['Aucun accès ouvert'],
        'data' => [1],
        'colors' => ['#D1D5DB'],
      ];
    }

    return [
      'labels' => $summaries->pluck('name')->all(),
      'data' => $summaries->pluck('access_open')->all(),
      'colors' => $this->colorsForCount($summaries->count()),
    ];
  }

  /**
   * Données pour le graphique « progression moyenne par cursus » (barres horizontales).
   *
   * @return array{labels: array<int, string>, data: array<int, float>, colors: array<int, string>}
   */
  public function progressByProgramChart(): array
  {
    $summaries = $this->programSummaries();

    return [
      'labels' => $summaries->pluck('name')->all(),
      'data' => $summaries->pluck('avg_progress_percent')->map(fn (float $value): float => round($value, 1))->all(),
      'colors' => $this->colorsForCount($summaries->count()),
    ];
  }

  /**
   * Tendance mensuelle des nouvelles inscriptions sur les N derniers mois.
   *
   * @param  int  $months  Nombre de mois glissants
   * @return array{
   *   labels: array<int, string>,
   *   datasets: array<int, array{label: string, data: array<int, int>, borderColor: string, backgroundColor: string}>
   * }
   */
  public function monthlyEnrollmentTrend(int $months = 6): array
  {
    $programs = Program::query()
      ->where('is_active', true)
      ->orderBy('sort_order')
      ->get();

    $start = now()->subMonths($months - 1)->startOfMonth();
    $labels = [];
    $monthKeys = [];

    for ($index = 0; $index < $months; $index++) {
      $month = $start->copy()->addMonths($index);
      $labels[] = ucfirst($month->locale('fr')->translatedFormat('M Y'));
      $monthKeys[] = $month->format('Y-m');
    }

    if ($programs->isEmpty()) {
      return [
        'labels' => $labels,
        'datasets' => [],
      ];
    }

    $enrollments = Enrollment::query()
      ->select('program_id', 'enrolled_at')
      ->where('enrolled_at', '>=', $start)
      ->whereIn('program_id', $programs->pluck('id'))
      ->get();

    $countsByProgramMonth = [];

    foreach ($enrollments as $enrollment) {
      if ($enrollment->enrolled_at === null) {
        continue;
      }

      $key = $enrollment->program_id.'|'.$enrollment->enrolled_at->format('Y-m');
      $countsByProgramMonth[$key] = ($countsByProgramMonth[$key] ?? 0) + 1;
    }

    $datasets = [];

    foreach ($programs as $index => $program) {
      $data = [];

      foreach ($monthKeys as $monthKey) {
        $data[] = (int) ($countsByProgramMonth[$program->id.'|'.$monthKey] ?? 0);
      }

      $color = self::CHART_COLORS[$index % count(self::CHART_COLORS)];

      $datasets[] = [
        'label' => $program->name,
        'data' => $data,
        'borderColor' => $color,
        'backgroundColor' => $color.'33',
        'fill' => true,
        'tension' => 0.3,
      ];
    }

    return [
      'labels' => $labels,
      'datasets' => $datasets,
    ];
  }

  /**
   * Calcule la progression moyenne (chapitres terminés / chapitres publiés) par cursus.
   *
   * @param  Collection<int, int>  $programIds  Identifiants des cursus
   * @return array<int, float> Pourcentage moyen par program_id
   */
  private function averageProgressPercentByProgram(Collection $programIds): array
  {
    if ($programIds->isEmpty()) {
      return [];
    }

    $publishedChapters = Chapter::query()
      ->select('courses.program_id')
      ->join('courses', 'courses.id', '=', 'chapters.course_id')
      ->where('chapters.is_published', true)
      ->whereIn('courses.program_id', $programIds)
      ->groupBy('courses.program_id')
      ->selectRaw('courses.program_id as program_id')
      ->selectRaw('COUNT(chapters.id) as total')
      ->pluck('total', 'program_id');

    $completedProgress = ChapterProgress::query()
      ->select('enrollments.program_id')
      ->join('enrollments', 'enrollments.id', '=', 'chapter_progress.enrollment_id')
      ->whereNotNull('chapter_progress.completed_at')
      ->whereIn('enrollments.program_id', $programIds)
      ->where('enrollments.status', 'active')
      ->groupBy('enrollments.program_id')
      ->selectRaw('enrollments.program_id as program_id')
      ->selectRaw('COUNT(chapter_progress.id) as total')
      ->pluck('total', 'program_id');

    $activeEnrollments = Enrollment::query()
      ->select('program_id')
      ->selectRaw('COUNT(*) as total')
      ->whereIn('program_id', $programIds)
      ->where('status', 'active')
      ->groupBy('program_id')
      ->pluck('total', 'program_id');

    $rates = [];

    foreach ($programIds as $programId) {
      $chapters = (int) ($publishedChapters[$programId] ?? 0);
      $enrollments = (int) ($activeEnrollments[$programId] ?? 0);
      $completed = (int) ($completedProgress[$programId] ?? 0);

      if ($chapters === 0 || $enrollments === 0) {
        $rates[$programId] = 0.0;

        continue;
      }

      $rates[$programId] = min(100, round(($completed / ($chapters * $enrollments)) * 100, 1));
    }

    return $rates;
  }

  /**
   * Retourne N couleurs pour les graphiques Chart.js.
   *
   * @param  int  $count  Nombre de couleurs souhaitées
   * @return array<int, string>
   */
  private function colorsForCount(int $count): array
  {
    $colors = [];

    for ($index = 0; $index < max(1, $count); $index++) {
      $colors[] = self::CHART_COLORS[$index % count(self::CHART_COLORS)];
    }

    return $colors;
  }
}
