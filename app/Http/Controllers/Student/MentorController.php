<?php

namespace App\Http\Controllers\Student;

use App\Enums\AppointmentMenteeResponse;
use App\Http\Controllers\Controller;
use App\Models\MentorAppointment;
use App\Enums\PortalNotificationType;
use App\Services\Mentor\MentorAppointmentService;
use App\Services\Portal\PortalNotificationService;
use App\Services\Student\MentorPortalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Espace mentoré Métamorpho — profil mentor, messages et avis.
 */
class MentorController extends Controller
{
  /**
   * @param  MentorPortalService  $mentorService  Service portail mentorat
   * @param  PortalNotificationService  $notificationService  Notifications
   * @param  MentorAppointmentService  $appointmentService  Rendez-vous
   */
  public function __construct(
    private readonly MentorPortalService $mentorService,
    private readonly PortalNotificationService $notificationService,
    private readonly MentorAppointmentService $appointmentService,
  ) {}

  /**
   * Affiche le mentor assigné et la messagerie Métamorpho.
   */
  public function show(Request $request): Response|RedirectResponse
  {
    $user = $request->user('member');
    $assignment = $this->mentorService->assignmentForMenteePortal($user);

    if (! $assignment) {
      return redirect()
        ->route('dashboard', ['cursus' => 'metamorpho'])
        ->with('info', 'Aucun mentor ne vous est encore assigné pour Métamorpho.');
    }

    $accompanimentClosed = $this->mentorService->isAccompanimentClosed($assignment);

    return Inertia::render('Mentor/MenteeView', [
      'mentor' => $this->mentorService->mentorProfilePayload($assignment),
      'messages' => $this->mentorService->messagesPayload($assignment, $user),
      'chatEnabled' => $this->mentorService->canUseMentorChat($user) && ! $accompanimentClosed,
      'chatPollUrl' => route('mentor.chat.poll'),
      'chatSendUrl' => route('mentor.message'),
      'accompanimentClosed' => $accompanimentClosed,
      'closedAt' => $assignment->ended_at?->format('d/m/Y'),
      'canSubmitClosureFeedback' => $this->mentorService->canSubmitClosureFeedback($assignment, $user),
      'hasSubmittedClosureFeedback' => $this->mentorService->hasSubmittedClosureFeedback($assignment, $user),
      'hasSubmittedFeedback' => $this->mentorService->hasSubmittedFinalFeedback($assignment, $user),
      'reportUnlocked' => ! $accompanimentClosed && $this->mentorService->canSubmitProgressReport($assignment, $user),
      'reportBlockReason' => $accompanimentClosed
        ? 'Votre accompagnement est clôturé. Utilisez le formulaire d\'avis ci-dessous.'
        : $this->mentorService->progressReportBlockReason($assignment, $user),
      'appointments' => $this->appointmentService->historyForMentee($user)
        ->map(fn ($a) => $this->appointmentService->payload($a))
        ->values()
        ->all(),
      'feedbacks' => $assignment->feedbacks()
        ->where('feedback_type', 'mentee_progress_report')
        ->latest()
        ->get()
        ->map(fn ($feedback) => [
          'rating' => $feedback->rating,
          'body' => $feedback->body,
          'created_at' => $feedback->created_at?->format('d/m/Y'),
        ]),
    ]);
  }

  /**
   * Réponse du mentoré à un rendez-vous (accepter, refuser, reporter).
   */
  public function respondToAppointment(Request $request, MentorAppointment $appointment): JsonResponse|RedirectResponse
  {
    $user = $request->user('member');

    $validated = $request->validate([
      'response' => ['required', 'in:accepted,declined,postponed'],
      'proposed_reschedule_at' => ['nullable', 'date', 'after:now'],
      'response_note' => ['nullable', 'string', 'max:2000'],
    ]);

    try {
      $this->appointmentService->recordMenteeResponse(
        $user,
        $appointment,
        AppointmentMenteeResponse::from($validated['response']),
        $validated['proposed_reschedule_at'] ?? null,
        $validated['response_note'] ?? null,
      );
    } catch (\RuntimeException $exception) {
      if ($request->wantsJson()) {
        return response()->json(['error' => $exception->getMessage()], 422);
      }

      return back()->with('error', $exception->getMessage());
    }

    if ($request->wantsJson()) {
      return response()->json(['ok' => true]);
    }

    return back()->with('status', 'Votre réponse a été transmise à votre mentor.');
  }

  /**
   * Enregistre le rapport de progression du mentoré.
   */
  public function submitFeedback(Request $request): RedirectResponse
  {
    $user = $request->user('member');
    $assignment = $this->mentorService->metamorphoAssignmentForMentee($user);

    if (! $assignment) {
      return back()->with('error', 'Aucune assignation mentor active.');
    }

    $validated = $request->validate([
      'rating' => ['required', 'integer', 'min:1', 'max:5'],
      'comment' => ['required', 'string', 'min:10', 'max:5000'],
    ]);

    try {
      $this->mentorService->submitMenteeFeedback(
        $assignment,
        $user,
        (int) $validated['rating'],
        $validated['comment'],
      );
    } catch (\RuntimeException $exception) {
      return back()->with('error', $exception->getMessage());
    }

    $this->notificationService->notify(
      $assignment->mentor,
      PortalNotificationType::MenteeMessage,
      'Rapport de progression reçu',
      $user->name.' a soumis son rapport de passage de niveau.',
      '/mentor/mentore/'.$assignment->id,
      'Consulter',
    );

    return back()->with('status', 'Merci ! Votre rapport a été transmis à votre mentor et à l\'administration.');
  }

  /**
   * Enregistre l'avis du mentoré après clôture de l'accompagnement par le mentor.
   */
  public function submitClosureFeedback(Request $request): RedirectResponse
  {
    $user = $request->user('member');
    $assignment = $this->mentorService->assignmentForMenteePortal($user);

    if (! $assignment) {
      return back()->with('error', 'Aucune assignation mentor trouvée.');
    }

    $validated = $request->validate([
      'rating' => ['required', 'integer', 'min:1', 'max:5'],
      'comment' => ['required', 'string', 'min:10', 'max:5000'],
    ]);

    try {
      $this->mentorService->submitMenteeClosureFeedback(
        $assignment,
        $user,
        (int) $validated['rating'],
        $validated['comment'],
      );
    } catch (\RuntimeException $exception) {
      return back()->with('error', $exception->getMessage());
    }

    $this->notificationService->notify(
      $assignment->mentor,
      PortalNotificationType::MenteeMessage,
      'Avis après clôture',
      $user->name.' a laissé un avis sur l\'accompagnement clôturé.',
      '/mentor/mentore/'.$assignment->id,
      'Consulter',
    );

    return back()->with('status', 'Merci ! Votre avis a été enregistré.');
  }
}
