<?php

namespace App\Services\Student;

use App\Models\Program;
use App\Models\ProgramAccess;
use App\Models\User;
use App\Services\Ecap\EcapPeriodAccessService;
use App\Services\ProgramAccess\ProgramAccessStateService;

/**
 * Agrège la progression des cursus PHILA en fonction des accès (ProgramAccess).
 */
class CursusProgressService
{
  /**
   * @param  FormationJourneyService  $journeyService  Service de parcours par programme
   * @param  EcapOnlineAccessService  $ecapOnlineAccessService  Mode en ligne ECAP
   * @param  ProgramAccessStateService  $accessStateService  Libellés d'accès
   */
  public function __construct(
    private readonly FormationJourneyService $journeyService,
    private readonly EcapOnlineAccessService $ecapOnlineAccessService,
    private readonly EcapPeriodAccessService $ecapPeriodAccessService,
    private readonly ProgramAccessStateService $accessStateService,
  ) {}

  /**
   * Retourne les cursus avec statut et étapes pour un fidèle.
   *
   * @param  User  $user  Fidèle connecté
   * @return array{modules: array<int, array>, global_progress: int, active_slug: string|null}
   */
  public function forUser(User $user, ?string $activeSlug = null): array
  {
    $definitions = config('cursus.modules', []);
    $modules = [];
    $totalProgress = 0;
    $defaultActive = null;

    $accessMap = ProgramAccess::query()
      ->where('user_id', $user->id)
      ->with('program')
      ->get()
      ->keyBy(fn (ProgramAccess $access) => $access->program?->slug ?? $access->program_id);

    foreach ($definitions as $definition) {
      $program = Program::query()
        ->where('slug', $definition['slug'])
        ->where('is_active', true)
        ->first();

      $journey = $program
        ? $this->journeyService->forProgram($user, $program)
        : $this->emptyJourney($definition);

      $progress = $journey['stats']['progress'];
      $hasSteps = $journey['stats']['total'] > 0;

      $access = $program ? $accessMap->get($program->slug) : null;
      $accessCode = $access ? $this->accessStateService->legacyCode($access) : null;

      $isEcapPresentiel = $definition['slug'] === 'ecap'
        && $this->ecapOnlineAccessService->isPresentiel($user);

      $status = match (true) {
        $isEcapPresentiel && in_array($accessCode, ['open', 'pending'], true) => 'presentiel_readonly',
        in_array($accessCode, ['completed', 'waived'], true) => 'completed',
        $accessCode === 'declared_completed' => 'pending_validation',
        $accessCode === 'pending' => 'locked',
        $accessCode === 'open' && $hasSteps && $progress >= 100 => 'completed',
        $accessCode === 'open' && $hasSteps && $progress > 0 => 'in_progress',
        $accessCode === 'open' => 'available',
        $access === null && $hasSteps && $progress > 0 => 'in_progress',
        $access === null => 'available',
        default => 'locked',
      };

      if (in_array($status, ['available', 'in_progress', 'presentiel_readonly'], true) && $defaultActive === null) {
        $defaultActive = $definition['slug'];
      }

      $module = array_merge($definition, [
        'program_id' => $program?->id,
        'status' => $status,
        'progress' => $progress,
        'stats' => $journey['stats'],
        'steps' => $journey['steps'],
        'content_modules' => $journey['modules'],
        'enrollment' => $journey['enrollment'],
        'has_quiz' => collect($journey['steps'])->contains(fn ($step) => $step['has_quiz'] ?? false),
        'has_tp' => collect($journey['steps'])->contains(fn ($step) => $step['has_tp'] ?? false),
        'ecap_online_mode' => $definition['slug'] === 'ecap'
          ? $this->ecapOnlineAccessService->modePayloadForUser($user)
          : null,
        'ecap_period' => $definition['slug'] === 'ecap'
          ? $this->ecapPeriodAccessService->periodPayloadForUser($user)
          : null,
        'access' => $access ? [
          'is_pending' => $access->is_pending,
          'is_open' => $access->is_open,
          'is_completed' => $access->is_completed,
          'is_waived' => $access->is_waived,
          'needs_admin_validation' => $access->needs_admin_validation,
          'label' => $this->accessStateService->label($access),
          'source' => $access->source,
          'validated_at' => $access->validated_at?->format('d/m/Y'),
        ] : null,
      ]);

      $modules[] = $module;
      $totalProgress += $progress;
    }

    $resolvedActive = $activeSlug ?? $defaultActive ?? ($modules[0]['slug'] ?? null);

    if ($resolvedActive) {
      $activeModule = collect($modules)->firstWhere('slug', $resolvedActive);
      if ($activeModule && $activeModule['status'] === 'locked') {
        $resolvedActive = collect($modules)->firstWhere('status', '!=', 'locked')['slug'] ?? $resolvedActive;
      }
    }

    return [
      'modules' => $modules,
      'global_progress' => count($modules) > 0
        ? (int) round($totalProgress / count($modules))
        : 0,
      'active_slug' => $resolvedActive,
    ];
  }

  /**
   * @param  array<string, mixed>  $definition
   * @return array<string, mixed>
   */
  private function emptyJourney(array $definition): array
  {
    return [
      'program' => [
        'id' => null,
        'name' => $definition['name'],
        'description' => $definition['description'],
      ],
      'enrollment' => null,
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
}
