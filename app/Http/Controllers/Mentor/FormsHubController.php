<?php

namespace App\Http\Controllers\Mentor;

use App\Enums\AssessmentType;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Services\Mentor\MentorStatsService;
use App\Services\Student\MentorPortalService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Hub des formulaires et démarches mentor (RDV, TP, corrections).
 */
class FormsHubController extends Controller
{
  /**
   * @param  MentorStatsService  $statsService  Statistiques mentor
   * @param  MentorPortalService  $mentorService  Portail mentorat
   */
  public function __construct(
    private readonly MentorStatsService $statsService,
    private readonly MentorPortalService $mentorService,
  ) {}

  /**
   * Page centrale des formulaires et actions mentor.
   */
  public function index(Request $request): Response
  {
    $user = $request->user('member');
    $mentees = $this->mentorService->activeMenteesForMentor($user);
    $programIds = $mentees->pluck('program_id')->unique()->filter()->values();

    $assessments = Assessment::query()
      ->where('type', AssessmentType::Tp->value)
      ->where('is_published', true)
      ->whereIn('program_id', $programIds)
      ->with(['chapter', 'program'])
      ->orderBy('title')
      ->get()
      ->map(fn ($assessment) => [
        'id' => $assessment->id,
        'title' => $assessment->title,
        'chapter' => $assessment->chapter?->title,
        'program_id' => $assessment->program_id,
        'program_name' => $assessment->program?->name,
      ])
      ->values()
      ->all();

    return Inertia::render('Mentor/FormsHub', [
      'summary' => $this->statsService->formsHubSummary($user),
      'mentees' => $mentees->map(fn ($assignment) => [
        'assignment_id' => $assignment->id,
        'name' => $assignment->mentee?->name,
        'program_id' => $assignment->program_id,
        'program_name' => $assignment->program?->name,
      ])->values()->all(),
      'tpAssessments' => $assessments,
    ]);
  }
}
