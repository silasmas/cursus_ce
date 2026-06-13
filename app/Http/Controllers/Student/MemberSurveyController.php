<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Portal\MemberSurveyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Réponses au sondage de satisfaction du portail fidèle.
 */
class MemberSurveyController extends Controller
{
  /**
   * Enregistre la réponse au sondage.
   */
  public function store(Request $request, MemberSurveyService $surveyService): RedirectResponse
  {
    $member = $request->user('member');

    if ($member === null) {
      abort(403);
    }

    $validated = $request->validate([
      'satisfaction' => ['required', 'integer', 'min:1', 'max:5'],
      'nps_score' => ['nullable', 'integer', 'min:0', 'max:10'],
      'comment' => ['nullable', 'string', 'max:2000'],
    ]);

    $surveyService->submit($member, $validated);

    return back()->with('status', 'Merci pour votre retour !');
  }

  /**
   * Reporte l'affichage du sondage.
   */
  public function snooze(Request $request, MemberSurveyService $surveyService): RedirectResponse
  {
    $member = $request->user('member');

    if ($member === null) {
      abort(403);
    }

    $surveyService->snooze($member);

    return back();
  }
}
