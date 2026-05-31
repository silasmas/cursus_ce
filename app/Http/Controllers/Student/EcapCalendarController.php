<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Ecap\EcapSessionTimelineService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Calendrier timeline ECAP côté fidèle.
 */
class EcapCalendarController extends Controller
{
  /**
   * @param  EcapSessionTimelineService  $timelineService  Construction de la timeline
   */
  public function __construct(
    private readonly EcapSessionTimelineService $timelineService,
  ) {}

  /**
   * Affiche la timeline de la session ECAP du fidèle.
   */
  public function index(Request $request): Response
  {
    $timeline = $this->timelineService->forUser($request->user('member'));

    return Inertia::render('Ecap/Calendar', [
      'timeline' => $timeline,
    ]);
  }
}
