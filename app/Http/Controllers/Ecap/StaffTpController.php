<?php

namespace App\Http\Controllers\Ecap;

use App\Enums\EcapVacationRole;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Ecap\Concerns\RespondsWithEcapAccessDenied;
use App\Models\AssignmentSubmission;
use App\Services\Ecap\EcapModuleTpService;
use App\Services\Ecap\EcapStaffRoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * TP modèle enseignant et correction superviseur ECAP.
 */
class StaffTpController extends Controller
{
  use RespondsWithEcapAccessDenied;

  /**
   * @param  EcapModuleTpService  $tpService  Service TP module
   * @param  EcapStaffRoleService  $roleService  Rôles acteurs
   */
  public function __construct(
    private readonly EcapModuleTpService $tpService,
    private readonly EcapStaffRoleService $roleService,
  ) {}

  /**
   * Liste des TP déposés par l'enseignant.
   */
  public function teacherIndex(Request $request): Response|HttpResponse
  {
    $user = $request->user('member');

    if ($denied = $this->denyEcapStaffRole($request, $user, EcapVacationRole::Teacher, 'Dépôt de TP modèle')) {
      return $denied;
    }

    $modules = $this->tpService->teacherModules($user)->load([
      'chapters' => fn ($query) => $query->where('is_published', true)->orderBy('sort_order'),
    ]);

    return Inertia::render('Ecap/StaffModuleTps', [
      'modules' => $modules->map(fn ($module) => [
        'id' => $module->id,
        'name' => $module->name,
        'course' => $module->course?->name,
        'chapters' => $module->chapters()->where('is_published', true)->orderBy('sort_order')->get(['id', 'title']),
      ]),
      'tps' => $this->tpService->teacherTpList($user),
    ]);
  }

  /**
   * Dépose un TP modèle pour un module enseigné.
   */
  public function store(Request $request): RedirectResponse|HttpResponse
  {
    $user = $request->user('member');

    if ($denied = $this->denyEcapStaffRole($request, $user, EcapVacationRole::Teacher, 'Publication d\'un TP')) {
      return $denied;
    }

    $validated = $request->validate([
      'course_module_id' => ['required', 'integer', 'exists:course_modules,id'],
      'chapter_id' => ['nullable', 'integer', 'exists:chapters,id'],
      'title' => ['required', 'string', 'max:255'],
      'instructions' => ['nullable', 'string', 'max:10000'],
    ]);

    $this->tpService->createTeacherTp(
      $user,
      (int) $validated['course_module_id'],
      $validated['title'],
      $validated['instructions'] ?? '',
      isset($validated['chapter_id']) ? (int) $validated['chapter_id'] : null,
    );

    return redirect()
      ->route('ecap.staff.tps.index')
      ->with('status', 'TP modèle publié pour le module.');
  }

  /**
   * Remises en attente de correction (superviseur).
   */
  public function supervisorIndex(Request $request): Response|HttpResponse
  {
    $user = $request->user('member');

    if ($denied = $this->denyEcapStaffRole($request, $user, EcapVacationRole::Supervisor, 'Corrections des TP')) {
      return $denied;
    }

    return Inertia::render('Ecap/StaffTpCorrections', [
      'submissions' => $this->tpService->pendingSubmissionsForSupervisor($user),
    ]);
  }

  /**
   * Corrige une remise de TP.
   */
  public function grade(Request $request, AssignmentSubmission $submission): RedirectResponse|JsonResponse|HttpResponse
  {
    $user = $request->user('member');

    if ($denied = $this->denyEcapStaffRole($request, $user, EcapVacationRole::Supervisor, 'Correction d\'un TP')) {
      return $denied;
    }

    $validated = $request->validate([
      'grade' => ['required', 'numeric', 'min:0', 'max:100'],
      'grader_notes' => ['nullable', 'string', 'max:5000'],
    ]);

    $this->tpService->gradeSubmission(
      $user,
      $submission,
      (float) $validated['grade'],
      $validated['grader_notes'] ?? null,
    );

    if ($request->wantsJson()) {
      return response()->json(['ok' => true]);
    }

    return redirect()
      ->route('ecap.staff.tp-corrections.index')
      ->with('status', 'Correction enregistrée.');
  }
}
