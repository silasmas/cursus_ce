<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use App\Services\Mentor\MentorStatsService;
use App\Services\Mentor\MentorAppointmentService;
use App\Services\Student\MentorPortalService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Tableau de bord du portail mentor.
 */
class DashboardController extends Controller
{
  /**
   * @param  MentorPortalService  $mentorService  Service portail mentorat
   * @param  MentorAppointmentService  $appointmentService  Rendez-vous
   * @param  MentorStatsService  $statsService  Statistiques
   */
  public function __construct(
    private readonly MentorPortalService $mentorService,
    private readonly MentorAppointmentService $appointmentService,
    private readonly MentorStatsService $statsService,
  ) {}

  /**
   * Statistiques mentor et aperçu de l'activité récente.
   */
  public function index(Request $request): Response
  {
    $user = $request->user('member');
    $user->load('mentorProfile');

    $pendingSubmissions = AssignmentSubmission::query()
      ->whereIn('user_id', $this->mentorService->activeMenteesForMentor($user)->pluck('mentee_id'))
      ->where('mentor_status', 'pending')
      ->whereNotNull('submitted_at')
      ->count();

    return Inertia::render('Mentor/Dashboard', [
      'mentor' => [
        'name' => $user->name,
        'email' => $user->email,
        'bio' => $user->mentorProfile?->bio,
        'whatsapp' => $user->mentorProfile?->whatsapp,
      ],
      'stats' => $this->statsService->dashboardStats($user),
      'pendingSubmissions' => $pendingSubmissions,
      'recentAppointments' => $this->appointmentService->upcomingForMentor($user)
        ->take(5)
        ->map(fn ($a) => $this->appointmentService->payload($a))
        ->values()
        ->all(),
    ]);
  }
}
