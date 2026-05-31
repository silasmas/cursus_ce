<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Student\CursusProgressService;
use App\Services\Student\MentorPortalService;
use App\Support\UserPresentation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Tableau de bord personnel du fidèle (Mon Espace).
 */
class DashboardController extends Controller
{
  /**
   * @param  CursusProgressService  $cursusProgressService  Progression des 5 cursus
   * @param  MentorPortalService  $mentorPortalService  Mentor Métamorpho
   */
  public function __construct(
    private readonly CursusProgressService $cursusProgressService,
    private readonly MentorPortalService $mentorPortalService,
  ) {}

  /**
   * Affiche le tableau de bord avec les 5 cursus et le parcours actif.
   */
  public function index(Request $request): Response
  {
    $user = $request->user('member');
    $profile = $user->profile;
    $cursus = $this->cursusProgressService->forUser($user, $request->query('cursus'));

    $mentorAssignment = $this->mentorPortalService->metamorphoAssignmentForMentee($user);
    $assignedMentor = $mentorAssignment
      ? $this->mentorPortalService->mentorProfilePayload($mentorAssignment)
      : null;

    $activeModule = collect($cursus['modules'])
      ->firstWhere('slug', $cursus['active_slug']);

    $certificates = $user->certificates()
      ->latest()
      ->limit(6)
      ->get();

    $displayName = $profile?->prenom ?? explode(' ', $user->name)[0] ?? 'Fidèle';

    $totalSteps = collect($cursus['modules'])->sum(fn ($m) => $m['stats']['total']);
    $completedSteps = collect($cursus['modules'])->sum(fn ($m) => $m['stats']['completed']);
    $lockedSteps = collect($cursus['modules'])->sum(fn ($m) => $m['stats']['locked']);

    $userPresentation = UserPresentation::for($user);

    return Inertia::render('Dashboard/Index', [
      'user' => [
        'name' => $user->name,
        'email' => $user->email,
        'displayName' => $displayName,
        'initials' => $userPresentation['initials'],
        'avatar_url' => $userPresentation['avatar_url'],
      ],
      'cursusModules' => $cursus['modules'],
      'activeCursusSlug' => $cursus['active_slug'],
      'activeCursus' => $activeModule,
      'stats' => [
        'cursus' => count($cursus['modules']),
        'progress' => $cursus['global_progress'],
        'steps' => $totalSteps,
        'completed' => $completedSteps,
        'locked' => $lockedSteps,
        'certificates' => $certificates->count(),
      ],
      'certificates' => $certificates->map(fn ($c) => [
        'id' => $c->id,
        'title' => $c->number ? 'Certificat n° '.$c->number : 'Certificat',
        'issued_at' => $c->issued_at?->format('d/m/Y'),
      ]),
      'assignedMentor' => $assignedMentor,
    ]);
  }

  /**
   * Extrait les initiales d'un nom complet.
   *
   * @param  string  $name  Nom complet
   * @return string  Initiales (2 caractères max)
   */
  private function initials(string $name): string
  {
    $parts = array_filter(explode(' ', trim($name)));

    if (count($parts) === 0) {
      return 'PH';
    }

    if (count($parts) === 1) {
      return strtoupper(substr($parts[0], 0, 2));
    }

    return strtoupper(substr($parts[0], 0, 1).substr(end($parts), 0, 1));
  }
}
