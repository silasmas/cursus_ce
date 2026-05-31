<?php

namespace App\Http\Controllers\Ecap;

use App\Enums\EcapVacationRole;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Ecap\Concerns\RespondsWithEcapAccessDenied;
use App\Models\AcademicSession;
use App\Models\EcapMeditationSubmission;
use App\Services\Ecap\EcapMeditationService;
use App\Services\Ecap\EcapStaffRoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Gestion des cahiers de méditation côté modérateur ECAP.
 */
class StaffMeditationController extends Controller
{
  use RespondsWithEcapAccessDenied;

  /**
   * @param  EcapMeditationService  $meditationService  Service méditation
   * @param  EcapStaffRoleService  $roleService  Rôles acteurs
   */
  public function __construct(
    private readonly EcapMeditationService $meditationService,
    private readonly EcapStaffRoleService $roleService,
  ) {}

  /**
   * Tableau modérateur : modèles et remises à corriger.
   */
  public function index(Request $request): Response|HttpResponse
  {
    $user = $request->user('member');

    if ($denied = $this->denyEcapStaffRole($request, $user, EcapVacationRole::Moderator, 'Cahiers de méditation')) {
      return $denied;
    }

    $dashboard = $this->meditationService->moderatorDashboard($user);

    $sessions = AcademicSession::query()
      ->whereHas('program', fn ($query) => $query->where('slug', 'ecap'))
      ->where('is_active', true)
      ->orderBy('name')
      ->get(['id', 'name']);

    return Inertia::render('Ecap/StaffMeditation', [
      ...$dashboard,
      'sessions' => $sessions,
    ]);
  }

  /**
   * Publie un modèle de cahier.
   */
  public function storeTemplate(Request $request): RedirectResponse|HttpResponse
  {
    $user = $request->user('member');

    if ($denied = $this->denyEcapStaffRole($request, $user, EcapVacationRole::Moderator, 'Publication d\'un cahier')) {
      return $denied;
    }

    $validated = $request->validate([
      'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
      'course_module_id' => ['nullable', 'integer', 'exists:course_modules,id'],
      'title' => ['required', 'string', 'max:255'],
      'instructions' => ['nullable', 'string', 'max:20000'],
      'due_on' => ['nullable', 'date'],
      'template_file' => ['nullable', 'file', 'max:10240'],
    ]);

    $this->meditationService->createTemplate(
      $user,
      (int) $validated['academic_session_id'],
      $validated['title'],
      $validated['instructions'] ?? null,
      isset($validated['course_module_id']) ? (int) $validated['course_module_id'] : null,
      $validated['due_on'] ?? null,
      $request->file('template_file'),
    );

    return redirect()
      ->route('ecap.staff.meditation.index')
      ->with('status', 'Modèle de cahier publié avec succès.');
  }

  /**
   * Corrige une remise fidèle.
   */
  public function review(Request $request, EcapMeditationSubmission $submission): RedirectResponse|HttpResponse
  {
    $user = $request->user('member');

    if ($denied = $this->denyEcapStaffRole($request, $user, EcapVacationRole::Moderator, 'Correction d\'un cahier')) {
      return $denied;
    }

    $validated = $request->validate([
      'status' => ['required', 'in:approved,rejected'],
      'moderator_notes' => ['nullable', 'string', 'max:5000'],
    ]);

    $this->meditationService->reviewSubmission(
      $user,
      $submission,
      $validated['status'],
      $validated['moderator_notes'] ?? null,
    );

    $label = $validated['status'] === 'approved' ? 'validée' : 'renvoyée pour correction';

    return redirect()
      ->route('ecap.staff.meditation.index')
      ->with('status', "Remise {$label}.");
  }
}
