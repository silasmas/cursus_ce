<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Services\Mentor\MentorAssignmentClosureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Clôture d'accompagnement et rapport à l'administration.
 */
class AssignmentClosureController extends Controller
{
  /**
   * @param  MentorAssignmentClosureService  $closureService  Service clôture
   */
  public function __construct(
    private readonly MentorAssignmentClosureService $closureService,
  ) {}

  /**
   * Clôture un ou plusieurs accompagnements avec rapport mentor.
   */
  public function store(Request $request): RedirectResponse
  {
    $user = $request->user('member');

    $validated = $request->validate([
      'assignment_ids' => ['required', 'array', 'min:1'],
      'assignment_ids.*' => ['integer', 'exists:mentor_assignments,id'],
      'report_body' => ['required', 'string', 'min:20', 'max:50000'],
      'confirm' => ['accepted'],
    ], [
      'confirm.accepted' => 'Vous devez confirmer la clôture de l\'accompagnement.',
    ]);

    try {
      $closed = $this->closureService->closeForMany(
        $user,
        $validated['assignment_ids'],
        $validated['report_body'],
      );
    } catch (\RuntimeException $exception) {
      return back()->with('error', $exception->getMessage());
    }

    $count = $closed->count();

    return back()->with(
      'status',
      $count > 1
        ? "{$count} accompagnements clôturés. Rapport transmis à l'administration."
        : 'Accompagnement clôturé. Rapport transmis à l\'administration. Le mentoré peut laisser son avis.',
    );
  }
}
