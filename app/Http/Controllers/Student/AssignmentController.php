<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Chapter;
use App\Services\Student\AssignmentSubmissionService;
use App\Services\Student\ChapterGateService;
use App\Services\Student\ChapterProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Remise et suivi des travaux pratiques (TP) par les fidèles.
 */
class AssignmentController extends Controller
{
  /**
   * @param  AssignmentSubmissionService  $submissionService  Gestion des remises
   * @param  ChapterGateService  $gateService  État des TP par chapitre
   * @param  ChapterProgressService  $progressService  Accès aux chapitres
   */
  public function __construct(
    private readonly AssignmentSubmissionService $submissionService,
    private readonly ChapterGateService $gateService,
    private readonly ChapterProgressService $progressService,
  ) {}

  /**
   * Liste les TP d'un chapitre et le formulaire de remise.
   */
  public function index(Request $request, Chapter $chapter): Response|RedirectResponse
  {
    $user = $request->user('member');

    if (! $this->progressService->canAccess($user, $chapter)) {
      return redirect()->route('dashboard')->with('error', 'Étape non accessible.');
    }

    $requirements = $this->gateService->requirementsSummary($user, $chapter);

    return Inertia::render('Assignment/Index', [
      'chapter' => [
        'id' => $chapter->id,
        'title' => $chapter->title,
      ],
      'tps' => $requirements['tps'],
      'readOnlyOnline' => ! $this->progressService->canInteractOnline($user, $chapter),
    ]);
  }

  /**
   * Soumet un TP pour validation par le formateur.
   */
  public function store(Request $request, Assessment $assessment): RedirectResponse
  {
    $user = $request->user('member');
    $chapter = $assessment->chapter;

    if ($chapter && ! $this->progressService->canAccess($user, $chapter)) {
      return back()->with('error', 'Étape non accessible.');
    }

    if ($chapter && ! $this->progressService->canInteractOnline($user, $chapter)) {
      return back()->with('error', 'Les TP en ligne sont désactivés pour votre mode ECAP présentiel.');
    }

    $validated = $request->validate([
      'answer_text' => ['nullable', 'string', 'max:10000'],
      'file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
    ]);

    if (empty($validated['answer_text']) && ! $request->hasFile('file')) {
      return back()->with('error', 'Rédigez une réponse ou joignez un fichier.');
    }

    try {
      $this->submissionService->submit(
        $user,
        $assessment,
        $validated['answer_text'] ?? null,
        $request->file('file'),
      );
    } catch (\RuntimeException $exception) {
      return back()->with('error', $exception->getMessage());
    }

    $redirect = $chapter
      ? redirect()->route('chapter.show', $chapter)
      : redirect()->route('dashboard');

    return $redirect->with('status', 'TP remis avec succès. En attente de validation par votre formateur.');
  }
}
