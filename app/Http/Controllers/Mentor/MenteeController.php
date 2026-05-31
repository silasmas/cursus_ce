<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\MentorAssignment;
use App\Services\Mentor\MentorAppointmentService;
use App\Services\Mentor\MentorReviewService;
use App\Services\Mentor\MentorTpSubmissionService;
use App\Services\Student\MentorPortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Fiche mentoré et messagerie côté mentor.
 */
class MenteeController extends Controller
{
  /**
   * @param  MentorPortalService  $mentorService  Service portail mentorat
   * @param  MentorReviewService  $reviewService  Validation des TP mentor
   * @param  MentorTpSubmissionService  $tpSubmissionService  Remise TP pour mentorés
   * @param  MentorAppointmentService  $appointmentService  Rendez-vous en ligne
   */
  public function __construct(
    private readonly MentorPortalService $mentorService,
    private readonly MentorReviewService $reviewService,
    private readonly MentorTpSubmissionService $tpSubmissionService,
    private readonly MentorAppointmentService $appointmentService,
  ) {}

  /**
   * Affiche le détail d'un mentoré et l'historique des messages.
   */
  public function show(Request $request, MentorAssignment $assignment): Response|RedirectResponse
  {
    $user = $request->user('member');

    if ($assignment->mentor_id !== $user->id) {
      abort(403);
    }

    $assignment->load(['mentee.profile', 'program', 'feedbacks.author']);

    return Inertia::render('Mentor/MenteeDetail', [
      'mentee' => $this->mentorService->menteeProfilePayloadForMentor($assignment->mentee, $assignment),
      'assignmentId' => $assignment->id,
      'messages' => $this->mentorService->messagesPayload($assignment, $user),
      'chatPollUrl' => route('mentor.mentee.chat.poll', $assignment),
      'chatSendUrl' => route('mentor.mentee.message', $assignment),
      'appointments' => $this->appointmentService->forAssignment($assignment)
        ->map(fn ($a) => $this->appointmentService->payload($a))
        ->values()
        ->all(),
      'assignmentStatus' => $assignment->status,
      'feedbacks' => $assignment->feedbacks->map(fn ($feedback) => [
        'rating' => $feedback->rating,
        'body' => $feedback->body,
        'author' => $feedback->author?->name,
        'type' => $feedback->feedback_type,
        'type_label' => match ($feedback->feedback_type) {
          'mentee_closure_feedback' => 'Avis après clôture',
          'mentee_progress_report' => 'Rapport de progression',
          default => 'Avis',
        },
        'created_at' => $feedback->created_at?->format('d/m/Y'),
      ]),
      'submissions' => $this->reviewService
        ->submissionsForMentee($user, $assignment->mentee)
        ->map(fn ($submission) => $this->reviewService->submissionPayload($submission))
        ->values()
        ->all(),
        'mentorSubmissions' => $this->tpSubmissionService
        ->mentorSubmissionsForMentee($user, $assignment->mentee)
        ->map(fn ($submission) => [
          'id' => $submission->id,
          'title' => $submission->assessment?->title,
          'chapter' => $submission->assessment?->chapter?->title,
          'submitted_at' => $submission->submitted_at?->format('d/m/Y H:i'),
          'admin_publication_status' => $submission->admin_publication_status,
          'visible_to_mentee' => $submission->visible_to_mentee,
          'answer_text' => $submission->answer_text,
          'can_edit' => $submission->admin_publication_status === 'pending_review',
        ])
        ->values()
        ->all(),
    ]);
  }

  /**
   * Remet un TP au nom du mentoré (publication admin requise).
   */
  public function submitTp(Request $request, MentorAssignment $assignment): RedirectResponse
  {
    $user = $request->user('member');

    if ($assignment->mentor_id !== $user->id) {
      abort(403);
    }

    $validated = $request->validate([
      'assessment_id' => ['required', 'exists:assessments,id'],
      'answer_text' => ['nullable', 'string', 'max:20000'],
      'file' => ['nullable', 'file', 'max:10240'],
    ]);

    if (empty($validated['answer_text']) && ! $request->hasFile('file')) {
      return back()->with('error', 'Ajoutez un texte ou un fichier pour le TP.');
    }

    $assessment = \App\Models\Assessment::query()->findOrFail($validated['assessment_id']);

    try {
      $this->tpSubmissionService->submitForMentee(
        $user,
        $assignment,
        $assessment,
        $validated['answer_text'] ?? null,
        $request->file('file'),
      );
    } catch (\RuntimeException $exception) {
      return back()->with('error', $exception->getMessage());
    }

    return back()->with('status', 'TP remis pour votre mentoré. L\'administration doit le publier avant qu\'il ne le voie.');
  }

  /**
   * Modifie un TP remis par le mentor (en attente admin).
   */
  public function updateTp(
    Request $request,
    MentorAssignment $assignment,
    \App\Models\AssignmentSubmission $submission,
  ): RedirectResponse {
    $user = $request->user('member');

    if ($assignment->mentor_id !== $user->id) {
      abort(403);
    }

    if ($submission->user_id !== $assignment->mentee_id) {
      abort(404);
    }

    $validated = $request->validate([
      'answer_text' => ['nullable', 'string', 'max:20000'],
      'file' => ['nullable', 'file', 'max:10240'],
    ]);

    try {
      $this->tpSubmissionService->updateSubmission(
        $user,
        $submission,
        $validated['answer_text'] ?? null,
        $request->file('file'),
      );
    } catch (\RuntimeException $exception) {
      return back()->with('error', $exception->getMessage());
    }

    return back()->with('status', 'TP modifié. Le mentoré et l\'administration ont été notifiés.');
  }
}
