<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Enums\PortalNotificationType;
use App\Services\Ecap\EcapModuleCalendarAccessService;
use App\Services\Portal\PortalNotificationService;
use App\Services\Student\ChapterGateService;
use App\Services\Student\ChapterProgressService;
use App\Services\Student\MentorPortalService;
use App\Support\UserPresentation;
use App\Support\YouTubeUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Lecteur de cours pour les fidèles.
 */
class ChapterController extends Controller
{
  /**
   * @param  ChapterProgressService  $progressService  Gestion de la progression
   * @param  ChapterGateService  $gateService  Prérequis tests / TP
   * @param  MentorPortalService  $mentorService  Mentor Métamorpho
   * @param  PortalNotificationService  $notificationService  Notifications portail
   */
  public function __construct(
    private readonly ChapterProgressService $progressService,
    private readonly ChapterGateService $gateService,
    private readonly MentorPortalService $mentorService,
    private readonly PortalNotificationService $notificationService,
    private readonly EcapModuleCalendarAccessService $moduleCalendarAccessService,
  ) {}

  /**
   * Affiche le lecteur de cours d'un chapitre.
   */
  public function show(Request $request, Chapter $chapter): Response|RedirectResponse
  {
    $chapter->load([
      'course.program',
      'courseModule',
      'instructor.mentorProfile',
      'contentBlocks' => fn ($query) => $query->orderBy('sort_order'),
      'contentBlocks.mediaAsset',
    ]);

    $user = $request->user('member');

    if (! $this->progressService->canAccess($user, $chapter)) {
      $message = $chapter->course_module_id
        && $this->moduleCalendarAccessService->isModuleClosed($user, (int) $chapter->course_module_id)
        ? $this->moduleCalendarAccessService->closedModuleMessage()
        : 'Cette étape n\'est pas encore accessible. Terminez les étapes précédentes.';

      return redirect()
        ->route('dashboard')
        ->with('error', $message);
    }

    $readOnlyOnline = ! $this->progressService->canInteractOnline($user, $chapter);

    if ($this->progressService->canInteractOnline($user, $chapter)) {
      $this->progressService->startOrResume($user, $chapter);
    }

    $curriculum = $this->progressService->curriculumFor($user, $chapter);
    $nextChapter = $this->progressService->nextChapterFor($user, $chapter);
    $program = $chapter->course?->program;
    $cursusDef = collect(config('cursus.modules', []))->firstWhere('slug', $program?->slug);
    $requirements = $this->gateService->requirementsSummary($user, $chapter);

    $mentorPayload = null;
    $instructorPayload = null;

    if ($chapter->instructor) {
      $instructorPresentation = UserPresentation::for($chapter->instructor);

      $instructorPayload = [
        'name' => $instructorPresentation['name'],
        'initials' => $instructorPresentation['initials'],
        'avatar_url' => $instructorPresentation['avatar_url'],
      ];
    }

    if ($program?->slug === 'metamorpho') {
      $assignment = $this->mentorService->metamorphoAssignmentForMentee($user);

      if ($assignment) {
        $mentorPayload = $this->mentorService->mentorProfilePayload($assignment);
        $mentorPayload['has_feedback'] = $this->mentorService->hasSubmittedFinalFeedback($assignment, $user);
      }
    }

    return Inertia::render('Course/Show', [
      'chapter' => [
        'id' => $chapter->id,
        'title' => $chapter->title,
        'module' => $chapter->courseModule?->name,
        'course' => $chapter->course?->name,
        'status' => $this->progressService->statusFor($user, $chapter),
        'is_completed' => $requirements['isReviewMode'],
      ],
      'cursus' => $cursusDef ? [
        'name' => $cursusDef['name'],
        'slug' => $cursusDef['slug'],
      ] : null,
      'contentBlocks' => $chapter->contentBlocks->map(function ($block) use ($request) {
        $videoId = YouTubeUrl::extractVideoId($block->url);
        $hasHostedVideo = $block->type === 'video' && $block->media_asset_id !== null;

        return [
          'id' => $block->id,
          'type' => $block->type,
          'title' => $block->title,
          'body' => $block->body,
          'url' => $block->url,
          'youtube_video_id' => $videoId,
          'stream_url' => $hasHostedVideo ? route('chapter.video.stream', $block) : null,
          'poster_url' => $videoId ? YouTubeUrl::thumbnailUrl($videoId) : null,
          'media_url' => $block->mediaAsset
            ? asset('storage/'.$block->mediaAsset->path)
            : null,
        ];
      }),
      'curriculum' => $curriculum,
      'nextChapter' => $nextChapter,
      'requirements' => $requirements,
      'readOnlyOnline' => $readOnlyOnline,
      'mentor' => $mentorPayload,
      'instructor' => $instructorPayload,
    ]);
  }

  /**
   * Marque le chapitre comme terminé et reste sur le lecteur (chapitre suivant débloqué).
   */
  public function complete(Request $request, Chapter $chapter): RedirectResponse
  {
    $user = $request->user('member');

    if (! $this->progressService->canAccess($user, $chapter)) {
      return redirect()
        ->route('dashboard')
        ->with('error', 'Impossible de valider cette étape.');
    }

    try {
      $this->progressService->markCompleted($user, $chapter);
    } catch (\RuntimeException $exception) {
      return redirect()
        ->route('chapter.show', $chapter)
        ->with('error', $exception->getMessage());
    }

    $program = $chapter->course?->program;
    $cursusSlug = $program?->slug;
    $nextChapter = $this->progressService->nextChapterFor($user, $chapter->fresh());

    $notificationUrl = $nextChapter
      ? route('chapter.show', $nextChapter['id'])
      : route('dashboard', $cursusSlug ? ['cursus' => $cursusSlug] : []);

    $notificationLabel = $nextChapter ? 'Chapitre suivant' : 'Continuer';

    $this->notificationService->notify(
      $user,
      PortalNotificationType::LevelUnlocked,
      'Niveau suivant débloqué',
      'Bravo ! L\'étape « '.$chapter->title.' » est terminée.'
        .($nextChapter ? ' Poursuivez avec « '.$nextChapter['title'].' ».' : ' Continuez votre parcours.'),
      $notificationUrl,
      $notificationLabel,
      ['chapter_id' => $chapter->id, 'next_chapter_id' => $nextChapter['id'] ?? null],
    );

    $statusMessage = $nextChapter
      ? 'Bravo ! L\'étape « '.$chapter->title.' » est terminée. Le chapitre suivant est débloqué.'
      : 'Bravo ! Vous avez terminé la dernière étape « '.$chapter->title.' ». Retournez à l\'accueil pour poursuivre votre parcours.';

    return redirect()
      ->route('chapter.show', $chapter)
      ->with('status', $statusMessage);
  }
}
