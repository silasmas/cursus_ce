<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use App\Services\Mentor\MentorReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Correction des TP soumis par les mentorés.
 */
class SubmissionController extends Controller
{
  /**
   * @param  MentorReviewService  $reviewService  Validation mentor
   */
  public function __construct(
    private readonly MentorReviewService $reviewService,
  ) {}

  /**
   * Liste les soumissions en attente de validation mentor.
   */
  public function index(Request $request): Response
  {
    $mentor = $request->user('member');
    $pending = $this->reviewService->pendingSubmissionsForMentor($mentor);

    return Inertia::render('Mentor/Submissions', [
      'submissions' => $pending->map(fn ($s) => $this->reviewService->submissionPayload($s)),
      'pendingCount' => $pending->count(),
    ]);
  }

  /**
   * Valide ou refuse une soumission avec avis pour l'administration.
   */
  public function review(Request $request, AssignmentSubmission $submission): RedirectResponse
  {
    $validated = $request->validate([
      'decision' => ['required', 'in:approved,rejected'],
      'notes' => ['required', 'string', 'min:10', 'max:5000'],
    ]);

    try {
      $this->reviewService->reviewSubmission(
        $request->user('member'),
        $submission,
        $validated['decision'],
        $validated['notes'],
      );
    } catch (\RuntimeException $exception) {
      return back()->with('error', $exception->getMessage());
    }

    $message = $validated['decision'] === 'approved'
      ? 'Travail validé. L\'administration peut finaliser ; le mentoré pourra progresser.'
      : 'Travail refusé. Le mentoré a été notifié via vos commentaires.';

    return redirect()->route('mentor.submissions.index')->with('status', $message);
  }
}
