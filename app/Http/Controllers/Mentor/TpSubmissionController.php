<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Services\Mentor\MentorTpSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Remise de TP mentor pour un ou plusieurs mentorés (hors fiche détail).
 */
class TpSubmissionController extends Controller
{
  /**
   * @param  MentorTpSubmissionService  $tpSubmissionService  Service remise TP
   */
  public function __construct(
    private readonly MentorTpSubmissionService $tpSubmissionService,
  ) {}

  /**
   * Remet un TP pour un ou plusieurs mentorés sélectionnés.
   */
  public function store(Request $request): RedirectResponse
  {
    $user = $request->user('member');

    $validated = $request->validate([
      'assignment_ids' => ['required', 'array', 'min:1'],
      'assignment_ids.*' => ['integer', 'exists:mentor_assignments,id'],
      'assessment_id' => ['required', 'exists:assessments,id'],
      'answer_text' => ['nullable', 'string', 'max:20000'],
      'file' => ['nullable', 'file', 'max:10240'],
    ]);

    if (empty($validated['answer_text']) && ! $request->hasFile('file')) {
      return back()->with('error', 'Ajoutez un texte ou un fichier pour le TP.');
    }

    $assessment = \App\Models\Assessment::query()->findOrFail($validated['assessment_id']);

    try {
      $submissions = $this->tpSubmissionService->submitForMany(
        $user,
        $validated['assignment_ids'],
        $assessment,
        $validated['answer_text'] ?? null,
        $request->file('file'),
      );
    } catch (\RuntimeException $exception) {
      return back()->with('error', $exception->getMessage());
    }

    $count = $submissions->count();

    return back()->with(
      'status',
      $count > 1
        ? "TP remis pour {$count} mentorés. L'administration doit valider chaque remise."
        : 'TP remis pour votre mentoré. L\'administration doit le publier avant qu\'il ne le voie.',
    );
  }
}
