<?php

namespace App\Http\Controllers\Mentor;

use App\Enums\AppointmentChannel;
use App\Http\Controllers\Controller;
use App\Models\MentorAssignment;
use App\Services\Mentor\MentorAppointmentService;
use App\Services\Student\MentorPortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Programmation de rendez-vous côté mentor.
 */
class AppointmentController extends Controller
{
  /**
   * @param  MentorAppointmentService  $appointmentService  Service rendez-vous
   * @param  MentorPortalService  $mentorService  Portail mentorat
   */
  public function __construct(
    private readonly MentorAppointmentService $appointmentService,
    private readonly MentorPortalService $mentorService,
  ) {}

  /**
   * Programme un ou plusieurs rendez-vous avec des mentorés.
   */
  public function store(Request $request): RedirectResponse
  {
    $user = $request->user('member');

    $validated = $request->validate([
      'assignment_ids' => ['required', 'array', 'min:1'],
      'assignment_ids.*' => ['integer', 'exists:mentor_assignments,id'],
      'scheduled_at' => ['required', 'date', 'after:now'],
      'channel' => ['required', 'in:whatsapp,zoom,google_meet'],
      'meeting_url' => ['nullable', 'url', 'max:500'],
      'notes' => ['nullable', 'string', 'max:2000'],
    ]);

    $channel = AppointmentChannel::from($validated['channel']);

    $this->appointmentService->scheduleForMany(
      $user,
      $validated['assignment_ids'],
      $validated['scheduled_at'],
      $channel,
      $validated['meeting_url'] ?? null,
      $validated['notes'] ?? null,
    );

    $count = count($validated['assignment_ids']);

    return back()->with('status', $count > 1
      ? "Rendez-vous programmé pour {$count} mentorés."
      : 'Rendez-vous programmé avec votre mentoré.');
  }

  /**
   * Programme un rendez-vous pour un mentoré précis.
   */
  public function storeForMentee(Request $request, MentorAssignment $assignment): RedirectResponse
  {
    $user = $request->user('member');

    if ($assignment->mentor_id !== $user->id) {
      abort(403);
    }

    $validated = $request->validate([
      'scheduled_at' => ['required', 'date', 'after:now'],
      'channel' => ['required', 'in:whatsapp,zoom,google_meet'],
      'meeting_url' => ['nullable', 'url', 'max:500'],
      'notes' => ['nullable', 'string', 'max:2000'],
    ]);

    $this->appointmentService->scheduleForAssignment(
      $user,
      $assignment,
      $validated['scheduled_at'],
      AppointmentChannel::from($validated['channel']),
      $validated['meeting_url'] ?? null,
      $validated['notes'] ?? null,
    );

    return back()->with('status', 'Rendez-vous programmé.');
  }

  /**
   * Met à jour un rendez-vous existant.
   */
  public function update(Request $request, \App\Models\MentorAppointment $appointment): RedirectResponse
  {
    $user = $request->user('member');

    if ($appointment->mentor_id !== $user->id) {
      abort(403);
    }

    $validated = $request->validate([
      'scheduled_at' => ['required', 'date', 'after:now'],
      'channel' => ['required', 'in:whatsapp,zoom,google_meet'],
      'meeting_url' => ['nullable', 'url', 'max:500'],
      'notes' => ['nullable', 'string', 'max:2000'],
    ]);

    $this->appointmentService->updateAppointment(
      $user,
      $appointment,
      $validated['scheduled_at'],
      AppointmentChannel::from($validated['channel']),
      $validated['meeting_url'] ?? null,
      $validated['notes'] ?? null,
    );

    return back()->with('status', 'Rendez-vous modifié. Le mentoré a été notifié.');
  }
}
