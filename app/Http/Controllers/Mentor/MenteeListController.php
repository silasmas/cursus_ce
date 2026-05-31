<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Services\Student\MentorPortalService;
use App\Support\UserPresentation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Liste des mentorés actifs du mentor.
 */
class MenteeListController extends Controller
{
  /**
   * @param  MentorPortalService  $mentorService  Service portail mentorat
   */
  public function __construct(
    private readonly MentorPortalService $mentorService,
  ) {}

  /**
   * Affiche la liste des mentorés avec filtres de base.
   */
  public function index(Request $request): Response
  {
    $user = $request->user('member');
    $mentees = $this->mentorService->activeMenteesForMentor($user);

    return Inertia::render('Mentor/Mentees', [
      'mentees' => $mentees->map(function ($assignment) {
        $presentation = UserPresentation::for($assignment->mentee);

        return [
        'assignment_id' => $assignment->id,
        'name' => $presentation['name'],
        'email' => $assignment->mentee?->email,
        'program' => $assignment->program?->name,
        'started_at' => $assignment->started_at?->format('d/m/Y'),
        'gender' => $this->mentorService->formatGenre($assignment->mentee?->profile?->genre),
        'age' => $assignment->mentee?->profile?->date_naissance?->age,
        'country' => $assignment->mentee?->profile?->nationalite,
        'avatar_url' => $presentation['avatar_url'],
        'initials' => $presentation['initials'],
        'pending_submissions' => \App\Models\AssignmentSubmission::query()
          ->where('user_id', $assignment->mentee_id)
          ->where('mentor_status', 'pending')
          ->whereNotNull('submitted_at')
          ->count(),
        ];
      }),
    ]);
  }
}
