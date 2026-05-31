<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Ecap\EcapMeditationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Cahiers de méditation ECAP côté fidèle.
 */
class EcapMeditationController extends Controller
{
  /**
   * @param  EcapMeditationService  $meditationService  Service méditation
   */
  public function __construct(
    private readonly EcapMeditationService $meditationService,
  ) {}

  /**
   * Liste des cahiers à travailler et remises.
   */
  public function index(Request $request): Response
  {
    return Inertia::render('Ecap/MeditationNotebooks', [
      'templates' => $this->meditationService->templatesForStudent($request->user('member')),
    ]);
  }

  /**
   * Remet un cahier travaillé au modérateur.
   */
  public function submit(Request $request, int $template): RedirectResponse
  {
    $validated = $request->validate([
      'answer_text' => ['required', 'string', 'max:50000'],
      'work_file' => ['nullable', 'file', 'max:10240'],
    ]);

    $this->meditationService->submitForStudent(
      $request->user('member'),
      $template,
      $validated['answer_text'],
      $request->file('work_file'),
    );

    return redirect()
      ->route('ecap.meditation.index')
      ->with('status', 'Votre cahier a été remis au modérateur.');
  }
}
