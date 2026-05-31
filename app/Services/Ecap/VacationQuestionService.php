<?php

namespace App\Services\Ecap;

use App\Support\UserPresentation;
use App\Enums\EcapVacationRole;
use App\Enums\PortalNotificationType;
use App\Enums\VacationQuestionReplyType;
use App\Enums\VacationQuestionStatus;
use App\Models\AcademicSession;
use App\Models\Chapter;
use App\Models\CourseModule;
use App\Models\EcapStaffAssignment;
use App\Models\User;
use App\Models\VacationQuestion;
use App\Models\VacationQuestionReply;
use App\Models\VacationQuestionReplyLike;
use App\Services\Admin\AdminNotificationService;
use App\Services\Portal\PortalNotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Fil Q&R ECAP par module de cours : mentions @prof / @tous, #module, réponses type Facebook.
 */
class VacationQuestionService
{
  /**
   * @param  PortalNotificationService  $notificationService  Notifications portail
   * @param  AdminNotificationService  $adminNotificationService  Alertes admin
   */
  public function __construct(
    private readonly PortalNotificationService $notificationService,
    private readonly AdminNotificationService $adminNotificationService,
  ) {}

  /**
   * Session ECAP active du fidèle.
   */
  public function studentSession(User $user): ?AcademicSession
  {
    $sessionId = $user->profile?->academic_session_id;

    if ($sessionId === null) {
      return null;
    }

    return AcademicSession::query()
      ->whereKey($sessionId)
      ->where('is_active', true)
      ->whereHas('program', fn ($query) => $query->where('slug', 'ecap'))
      ->first();
  }

  /**
   * Modules de cours ECAP disponibles pour les mentions #.
   *
   * @return Collection<int, CourseModule>
   */
  public function ecapCourseModules(): Collection
  {
    return CourseModule::query()
      ->whereHas('course.program', fn ($query) => $query->where('slug', 'ecap'))
      ->with('course')
      ->orderBy('sort_order')
      ->get();
  }

  /**
   * Enseignants actifs sur la session du fidèle (mentions @).
   *
   * @return Collection<int, User>
   */
  public function teachersForSession(AcademicSession $session, ?int $sessionVacationId = null): Collection
  {
    return User::query()
      ->whereIn('id', $this->teacherAssignmentQuery($session->id, $sessionVacationId)->pluck('user_id'))
      ->orderBy('name')
      ->get();
  }

  /**
   * Crée une question depuis le portail fidèle.
   */
  public function ask(
    User $user,
    int $courseModuleId,
    string $body,
    bool $addressAllTeachers,
    ?int $addressedToUserId = null,
  ): VacationQuestion {
    $session = $this->studentSession($user);

    if ($session === null) {
      throw ValidationException::withMessages([
        'body' => 'Vous devez être inscrit à une session ECAP active pour poser une question.',
      ]);
    }

    $module = $this->ecapCourseModules()->firstWhere('id', $courseModuleId);

    if ($module === null) {
      throw ValidationException::withMessages([
        'course_module_id' => 'Ce module de cours n\'existe pas ou n\'appartient pas au cursus ECAP.',
      ]);
    }

    if (! $addressAllTeachers && $addressedToUserId === null) {
      $addressAllTeachers = true;
    }

    if (! $addressAllTeachers) {
      $teacherIds = $this->teachersForSession($session, $user->profile?->session_vacation_id)
        ->pluck('id');

      if (! $teacherIds->contains($addressedToUserId)) {
        throw ValidationException::withMessages([
          'addressee' => 'L\'enseignant choisi n\'est pas affecté à votre session ECAP.',
        ]);
      }
    }

    $subject = Str::limit(trim(preg_replace('/\s+/', ' ', $body)), 120, '…');

    return VacationQuestion::query()->create([
      'academic_session_id' => $session->id,
      'session_vacation_id' => $user->profile?->session_vacation_id,
      'course_module_id' => $module->id,
      'asked_by_user_id' => $user->id,
      'addressed_to_role' => EcapVacationRole::Teacher,
      'addressed_to_user_id' => $addressAllTeachers ? null : $addressedToUserId,
      'is_addressed_to_all_teachers' => $addressAllTeachers,
      'subject' => $subject !== '' ? $subject : 'Question ECAP',
      'body' => $body,
      'status' => VacationQuestionStatus::Pending,
    ]);
  }

  /**
   * Ajoute une réponse officielle ou un avis au fil.
   */
  public function reply(
    VacationQuestion $question,
    User $staff,
    string $body,
    VacationQuestionReplyType $replyType = VacationQuestionReplyType::Answer,
    ?int $parentReplyId = null,
  ): VacationQuestionReply {
    if ($replyType === VacationQuestionReplyType::Comment) {
      if (! $this->canCommentOnQuestion($staff, $question)) {
        throw ValidationException::withMessages([
          'body' => 'Vous n\'êtes pas habilité à commenter cette question.',
        ]);
      }
    } elseif (! $this->canReply($staff, $question)) {
      throw ValidationException::withMessages([
        'body' => 'Vous n\'êtes pas habilité à répondre à cette question.',
      ]);
    } else {
      $existingAnswer = $this->officialAnswerFromUser($question, $staff);

      if ($existingAnswer !== null) {
        throw ValidationException::withMessages([
          'body' => 'Vous avez déjà publié une réponse. Utilisez « Modifier » pour l\'améliorer.',
        ]);
      }
    }

    if ($parentReplyId !== null) {
      $parent = VacationQuestionReply::query()
        ->whereKey($parentReplyId)
        ->where('vacation_question_id', $question->id)
        ->firstOrFail();

      if ($parent->reply_type !== VacationQuestionReplyType::Answer) {
        throw ValidationException::withMessages([
          'parent_reply_id' => 'Un avis ne peut être rattaché qu\'à une réponse officielle.',
        ]);
      }
    }

    $reply = VacationQuestionReply::query()->create([
      'vacation_question_id' => $question->id,
      'user_id' => $staff->id,
      'body' => $body,
      'reply_type' => $replyType->value,
      'parent_reply_id' => $parentReplyId,
    ]);

    if ($replyType === VacationQuestionReplyType::Answer && $question->status === VacationQuestionStatus::Pending) {
      $question->update([
        'status' => VacationQuestionStatus::Answered,
        'answered_by_user_id' => $staff->id,
        'answer_body' => $body,
        'answered_at' => now(),
      ]);
    }

    $question->loadMissing('askedBy');

    if ($question->askedBy) {
      $title = $replyType === VacationQuestionReplyType::Comment
        ? 'Nouvel avis sur votre question ECAP'
        : 'Réponse à votre question ECAP';

      $this->notificationService->notify(
        $question->askedBy,
        PortalNotificationType::EcapQuestionReply,
        $title,
        $staff->name.' a contribué sur #'.($question->courseModule?->name ?? 'module').'.',
        route('ecap.questions.index'),
        'Voir la discussion',
        ['question_id' => $question->id],
      );
    }

    return $reply->load('author');
  }

  /**
   * Modifie une réponse officielle (remplace le texte visible, sans historique public).
   */
  public function updateReply(VacationQuestionReply $reply, User $staff, string $body): VacationQuestionReply
  {
    if (! $this->canEditReply($staff, $reply)) {
      throw ValidationException::withMessages([
        'body' => 'Vous ne pouvez modifier que vos propres réponses officielles.',
      ]);
    }

    $reply->update([
      'body' => $body,
      'edited_at' => now(),
      'version' => (int) $reply->version + 1,
    ]);

    $question = $reply->question;

    if ($question && (int) $question->answered_by_user_id === (int) $staff->id) {
      $question->update(['answer_body' => $body]);
    }

    if ($question?->askedBy) {
      $this->notificationService->notify(
        $question->askedBy,
        PortalNotificationType::EcapQuestionReply,
        'Réponse mise à jour',
        $staff->name.' a mis à jour sa réponse sur #'.($question->courseModule?->name ?? 'module').'.',
        route('ecap.questions.index'),
        'Voir la discussion',
        ['question_id' => $question->id],
      );
    }

    return $reply->fresh(['author']);
  }

  /**
   * Indique si l'acteur peut commenter (avis) sur une question.
   */
  public function canCommentOnQuestion(User $user, VacationQuestion $question): bool
  {
    if (! $this->staffSessionIds($user)->contains($question->academic_session_id)) {
      return false;
    }

    if ($question->relationLoaded('replies')) {
      return $question->replies->contains(
        fn (VacationQuestionReply $reply) => $this->isAnswerReply($reply),
      );
    }

    return $question->replies()
      ->where('reply_type', VacationQuestionReplyType::Answer->value)
      ->exists()
      || filled($question->answer_body);
  }

  /**
   * Indique si l'auteur peut modifier sa réponse officielle.
   */
  public function canEditReply(User $user, VacationQuestionReply $reply): bool
  {
    if (! $this->isAnswerReply($reply)) {
      return false;
    }

    return (int) $reply->user_id === (int) $user->id;
  }

  /**
   * Questions visibles dans le fil (tous les acteurs de la session voient tout).
   *
   * @return Collection<int, VacationQuestion>
   */
  public function feedForSession(
    int $academicSessionId,
    ?int $sessionVacationId = null,
    ?int $courseModuleId = null,
    ?int $addresseeUserId = null,
    ?int $authorUserId = null,
  ): Collection {
    $query = $this->baseFeedQuery($academicSessionId, $sessionVacationId, $courseModuleId);

    if ($addresseeUserId !== null) {
      $query->where(function ($inner) use ($addresseeUserId): void {
        $inner->where('addressed_to_user_id', $addresseeUserId)
          ->orWhere('is_addressed_to_all_teachers', true);
      });
    }

    if ($authorUserId !== null) {
      $query->where('asked_by_user_id', $authorUserId);
    }

    return $query->get();
  }

  /**
   * Bascule le pouce « utile » sur une réponse (fidèle).
   *
   * @return array{liked: bool, likes_count: int}
   */
  public function toggleReplyLike(User $user, VacationQuestionReply $reply): array
  {
    $existing = VacationQuestionReplyLike::query()
      ->where('vacation_question_reply_id', $reply->id)
      ->where('user_id', $user->id)
      ->first();

    if ($existing) {
      $existing->delete();
      $liked = false;
    } else {
      VacationQuestionReplyLike::query()->create([
        'vacation_question_reply_id' => $reply->id,
        'user_id' => $user->id,
      ]);
      $liked = true;
    }

    return [
      'liked' => $liked,
      'likes_count' => $reply->likes()->count(),
    ];
  }

  /**
   * Catalogue des mentions @ et # pour liens cliquables et autocomplétion.
   *
   * @return array<string, mixed>
   */
  public function mentionCatalog(string $feedBasePath, ?AcademicSession $session, ?int $sessionVacationId = null): array
  {
    $moduleChapterUrls = $this->firstChapterUrlsByModule();

    $modules = $this->ecapCourseModules()->map(fn (CourseModule $module) => [
      'id' => $module->id,
      'tag' => $this->moduleTag($module),
      'name' => $module->name,
      'url' => $moduleChapterUrls[$module->id] ?? null,
      'filter_url' => $feedBasePath.'?module='.$module->id,
    ])->values();

    $chapters = Chapter::query()
      ->whereHas('courseModule.course.program', fn ($query) => $query->where('slug', 'ecap'))
      ->where('is_published', true)
      ->with('courseModule')
      ->orderBy('sort_order')
      ->get()
      ->map(fn (Chapter $chapter) => [
        'id' => $chapter->id,
        'tag' => $this->chapterTag($chapter),
        'name' => $chapter->title,
        'module_id' => $chapter->course_module_id,
        'url' => route('chapter.show', $chapter),
        'filter_url' => $feedBasePath.'?module='.$chapter->course_module_id,
      ])->values();

    $users = collect();

    if ($session !== null) {
      foreach ($this->staffMembersForSession($session, $sessionVacationId) as $staffMember) {
        $users->push($this->mapCatalogUser($staffMember, $session->id, $feedBasePath));
      }

      $members = User::query()
        ->whereHas('profile', fn ($query) => $query->where('academic_session_id', $session->id))
        ->orderBy('name')
        ->limit(200)
        ->get();

      foreach ($members as $member) {
        $users->push([
          'id' => $member->id,
          'name' => $member->name,
          'mention' => $this->userMention($member),
          'role' => 'member',
          'profile_url' => route('members.show', $member),
          'filter_url' => $feedBasePath.'?author='.$member->id,
        ]);
      }
    }

    return [
      'modules' => $modules,
      'chapters' => $chapters,
      'users' => $users->unique('id')->values(),
    ];
  }

  /**
   * Indique si un fidèle peut consulter le profil d'un autre membre ECAP.
   */
  public function canViewMemberProfile(User $viewer, User $target): bool
  {
    if ((int) $viewer->id === (int) $target->id) {
      return true;
    }

    $viewerSessionId = $this->resolveViewerSessionId($viewer);

    if ($viewerSessionId === null) {
      return false;
    }

    if ($this->isStaffOnSession($target, $viewerSessionId)) {
      return true;
    }

    $target->loadMissing('profile');

    return (int) ($target->profile?->academic_session_id ?? 0) === $viewerSessionId;
  }

  /**
   * Payload profil public d'un membre ECAP.
   *
   * @return array<string, mixed>
   */
  public function memberProfilePayload(User $target, ?int $sessionId = null): array
  {
    $target->loadMissing('profile');
    $presentation = UserPresentation::for($target);
    $roleLabels = [];

    if ($sessionId !== null) {
      $roleLabels = collect(app(EcapStaffRoleService::class)->rolesForSession($target, $sessionId))
        ->map(fn (EcapVacationRole $role) => $role->label())
        ->values()
        ->all();
    }

    return [
      'id' => $target->id,
      'name' => $presentation['name'],
      'initials' => $presentation['initials'],
      'avatar_url' => $presentation['avatar_url'],
      'profession' => $target->profile?->profession,
      'commune_habitation' => $target->profile?->commune_habitation,
      'quartier_habitation' => $target->profile?->quartier_habitation,
      'bio' => $target->profile?->bio,
      'ecap_roles' => $roleLabels,
      'is_ecap_staff' => $roleLabels !== [],
    ];
  }

  /**
   * Questions du fidèle connecté.
   *
   * @return Collection<int, VacationQuestion>
   */
  public function questionsForStudent(User $user, ?int $courseModuleId = null): Collection
  {
    $session = $this->studentSession($user);

    if ($session === null) {
      return collect();
    }

    return $this->baseFeedQuery(
      $session->id,
      $user->profile?->session_vacation_id,
      $courseModuleId,
    )
      ->where('asked_by_user_id', $user->id)
      ->get();
  }

  /**
   * Fil pour un acteur ECAP (filtré par module optionnel).
   *
   * @return Collection<int, VacationQuestion>
   */
  public function questionsForStaff(User $staff, ?int $courseModuleId = null): Collection
  {
    $assignments = EcapStaffAssignment::query()
      ->where('user_id', $staff->id)
      ->where('is_active', true)
      ->get();

    if ($assignments->isEmpty()) {
      return collect();
    }

    $query = VacationQuestion::query()
      ->with([
        'askedBy.profile',
        'academicSession',
        'sessionVacation',
        'courseModule.course',
        'addressedToUser',
        'replies.author.profile',
        'replies.author.mentorProfile',
        'replies.likes',
        'replies.parentReply',
        'answeredBy.profile',
        'answeredBy.mentorProfile',
      ])
      ->latest();

    $query->where(function ($outer) use ($assignments): void {
      foreach ($assignments as $assignment) {
        $outer->orWhere(function ($inner) use ($assignment): void {
          $inner->where('academic_session_id', $assignment->academic_session_id);

          if ($assignment->session_vacation_id !== null) {
            $inner->where(function ($vacationQuery) use ($assignment): void {
              $vacationQuery->whereNull('session_vacation_id')
                ->orWhere('session_vacation_id', $assignment->session_vacation_id);
            });
          }
        });
      }
    });

    if ($courseModuleId !== null) {
      $query->where('course_module_id', $courseModuleId);
    }

    return $query->get();
  }

  /**
   * Indique si l'utilisateur peut voir la question.
   */
  public function canView(User $user, VacationQuestion $question): bool
  {
    if ($question->asked_by_user_id === $user->id) {
      return true;
    }

    return $this->staffSessionIds($user)->contains($question->academic_session_id);
  }

  /**
   * Indique si l'utilisateur peut répondre.
   */
  public function canReply(User $user, VacationQuestion $question): bool
  {
    if (! $this->hasTeacherRole($user, $question->academic_session_id, $question->session_vacation_id)) {
      return false;
    }

    if ($question->is_addressed_to_all_teachers) {
      return true;
    }

    if ($question->addressed_to_user_id === null) {
      return true;
    }

    return (int) $question->addressed_to_user_id === (int) $user->id;
  }

  /**
   * Nombre de questions en attente pour un enseignant (destinataire ou @tous).
   */
  public function pendingCountForStaff(User $staff): int
  {
    return $this->questionsForStaff($staff)
      ->filter(fn (VacationQuestion $question) => $this->isPendingForStaff($staff, $question))
      ->count();
  }

  /**
   * Escalade : sans réponse après 1 h → admin + enseignant(s) concernés.
   */
  public function escalateUnanswered(): int
  {
    $threshold = now()->subHour();
    $count = 0;

    $questions = VacationQuestion::query()
      ->where('status', VacationQuestionStatus::Pending)
      ->whereNull('escalation_notified_at')
      ->where('created_at', '<=', $threshold)
      ->whereDoesntHave('replies')
      ->with(['courseModule', 'addressedToUser', 'academicSession'])
      ->get();

    foreach ($questions as $question) {
      $question->loadMissing('academicSession');
      $this->notifyEscalation($question);
      $question->update(['escalation_notified_at' => now()]);
      $count++;
    }

    return $count;
  }

  /**
   * Payload portail fidèle (fil Facebook).
   *
   * @return array<string, mixed>
   */
  public function studentPortalPayload(
    User $user,
    ?int $courseModuleId = null,
    ?int $addresseeId = null,
    ?int $authorId = null,
  ): array {
    $session = $this->studentSession($user);
    $modules = $this->ecapCourseModules();
    $teachers = $session
      ? $this->teachersForSession($session, $user->profile?->session_vacation_id)
      : collect();

    $feedBase = '/mon-espace/ecap/questions';

    $feed = $session
      ? $this->feedForSession(
        $session->id,
        $user->profile?->session_vacation_id,
        $courseModuleId,
        $addresseeId,
        $authorId,
      )
      : collect();

    $mentionCatalog = $this->mentionCatalog($feedBase, $session, $user->profile?->session_vacation_id);

    return [
      'hasEcapSession' => $session !== null,
      'sessionName' => $session?->name,
      'visibilityNotice' => 'Toutes les questions sont visibles par l\'ensemble des acteurs ECAP de votre session. Sans @ précis, la question est adressée à @tous les enseignants.',
      'courseModules' => $modules->map(fn (CourseModule $module) => [
        'id' => $module->id,
        'name' => $module->name,
        'course_name' => $module->course?->name,
        'tag' => $this->moduleTag($module),
      ])->values(),
      'teachers' => $teachers->map(fn (User $teacher) => [
        'id' => $teacher->id,
        'name' => $teacher->name,
        'mention' => $this->userMention($teacher),
      ])->values(),
      'mentionCatalog' => $mentionCatalog,
      'mentionUsers' => $mentionCatalog['users'],
      'hashTags' => $modules->map(fn (CourseModule $module) => [
        'id' => $module->id,
        'tag' => $this->moduleTag($module),
        'name' => $module->name,
        'kind' => 'module',
      ])->merge(
        collect($mentionCatalog['chapters'])->map(fn (array $chapter) => [
          'id' => $chapter['id'],
          'tag' => $chapter['tag'],
          'name' => $chapter['name'],
          'kind' => 'chapter',
        ]),
      )->values(),
      'activeModuleId' => $courseModuleId,
      'activeAddresseeId' => $addresseeId,
      'activeAuthorId' => $authorId,
      'feedUrl' => $feedBase.'/feed',
      'posts' => $feed->map(fn (VacationQuestion $question) => $this->mapPost($question, $user, $feedBase))->values(),
    ];
  }

  /**
   * Payload portail acteur ECAP.
   *
   * @return array<string, mixed>
   */
  public function staffPortalPayload(
    User $staff,
    ?int $courseModuleId = null,
    ?int $addresseeId = null,
    ?int $authorId = null,
  ): array {
    $roleService = app(EcapStaffRoleService::class);
    $roles = [];
    $sessionIds = EcapStaffAssignment::query()
      ->where('user_id', $staff->id)
      ->where('is_active', true)
      ->pluck('academic_session_id')
      ->unique();

    foreach ($sessionIds as $sessionId) {
      foreach ($roleService->rolesForSession($staff, (int) $sessionId) as $role) {
        $roles[$role->value] = $role->label();
      }
    }

    $feedBase = '/ecap/acteurs/questions';
    $session = $sessionIds->isNotEmpty()
      ? AcademicSession::query()->find($sessionIds->first())
      : null;
    $mentionCatalog = $this->mentionCatalog($feedBase, $session);

    $questions = $this->questionsForStaff($staff, $courseModuleId)
      ->when($addresseeId, fn ($collection) => $collection->filter(
        fn (VacationQuestion $question) => $question->is_addressed_to_all_teachers
          || (int) $question->addressed_to_user_id === $addresseeId,
      ))
      ->when($authorId, fn ($collection) => $collection->filter(
        fn (VacationQuestion $question) => (int) $question->asked_by_user_id === $authorId,
      ))
      ->values();

    $modules = $this->ecapCourseModules();

    return [
      'roles' => $roles,
      'pendingCount' => $this->pendingCountForStaff($staff),
      'visibilityNotice' => 'Toutes les questions du module sont visibles par tous les acteurs ECAP. Utilisez @ et # dans vos réponses comme les fidèles.',
      'courseModules' => $modules->map(fn (CourseModule $module) => [
        'id' => $module->id,
        'name' => $module->name,
        'tag' => $this->moduleTag($module),
      ])->values(),
      'teachers' => $session
        ? $this->teachersForSession($session)->map(fn (User $teacher) => [
          'id' => $teacher->id,
          'name' => $teacher->name,
          'mention' => $this->userMention($teacher),
        ])->values()
        : collect(),
      'mentionCatalog' => $mentionCatalog,
      'mentionUsers' => $mentionCatalog['users'],
      'hashTags' => $modules->map(fn (CourseModule $module) => [
        'id' => $module->id,
        'tag' => $this->moduleTag($module),
        'name' => $module->name,
        'kind' => 'module',
      ])->merge(
        collect($mentionCatalog['chapters'])->map(fn (array $chapter) => [
          'id' => $chapter['id'],
          'tag' => $chapter['tag'],
          'name' => $chapter['name'],
          'kind' => 'chapter',
        ]),
      )->values(),
      'activeModuleId' => $courseModuleId,
      'activeAddresseeId' => $addresseeId,
      'activeAuthorId' => $authorId,
      'feedUrl' => $feedBase.'/feed',
      'posts' => $questions->map(fn (VacationQuestion $question) => $this->mapPost($question, $staff, $feedBase))->values(),
    ];
  }

  /**
   * Posts du fil uniquement (API JSON partielle).
   *
   * @return array<string, mixed>
   */
  public function studentFeedPosts(User $user, ?int $moduleId, ?int $addresseeId, ?int $authorId): array
  {
    $session = $this->studentSession($user);

    if ($session === null) {
      return ['posts' => []];
    }

    $feed = $this->feedForSession(
      $session->id,
      $user->profile?->session_vacation_id,
      $moduleId,
      $addresseeId,
      $authorId,
    );

    return [
      'posts' => $feed->map(fn (VacationQuestion $question) => $this->mapPost($question, $user, '/mon-espace/ecap/questions'))->values(),
    ];
  }

  /**
   * Posts du fil acteur (API JSON partielle).
   *
   * @return array<string, mixed>
   */
  public function staffFeedPosts(User $staff, ?int $moduleId, ?int $addresseeId, ?int $authorId): array
  {
    $questions = $this->questionsForStaff($staff, $moduleId)
      ->when($addresseeId, fn ($collection) => $collection->filter(
        fn (VacationQuestion $question) => $question->is_addressed_to_all_teachers
          || (int) $question->addressed_to_user_id === $addresseeId,
      ))
      ->when($authorId, fn ($collection) => $collection->filter(
        fn (VacationQuestion $question) => (int) $question->asked_by_user_id === $authorId,
      ))
      ->values();

    return [
      'posts' => $questions->map(fn (VacationQuestion $question) => $this->mapPost($question, $staff, '/ecap/acteurs/questions'))->values(),
    ];
  }

  /**
   * @return Builder<VacationQuestion>
   */
  private function baseFeedQuery(
    int $academicSessionId,
    ?int $sessionVacationId,
    ?int $courseModuleId,
  ): Builder {
    $query = VacationQuestion::query()
      ->with([
        'askedBy.profile',
        'askedBy.mentorProfile',
        'courseModule.course',
        'addressedToUser.profile',
        'replies.author.profile',
        'replies.author.mentorProfile',
        'replies.likes',
        'replies.parentReply',
        'answeredBy.profile',
        'answeredBy.mentorProfile',
      ])
      ->where('academic_session_id', $academicSessionId)
      ->latest();

    if ($sessionVacationId !== null) {
      $query->where(function ($inner) use ($sessionVacationId): void {
        $inner->whereNull('session_vacation_id')
          ->orWhere('session_vacation_id', $sessionVacationId);
      });
    }

    if ($courseModuleId !== null) {
      $query->where('course_module_id', $courseModuleId);
    }

    return $query;
  }

  /**
   * Identifiants des sessions ECAP où l'utilisateur est acteur actif.
   *
   * @return SupportCollection<int, int>
   */
  private function staffSessionIds(User $user): SupportCollection
  {
    return EcapStaffAssignment::query()
      ->where('user_id', $user->id)
      ->where('is_active', true)
      ->pluck('academic_session_id')
      ->unique()
      ->values();
  }

  /**
   * @return Builder<EcapStaffAssignment>
   */
  private function teacherAssignmentQuery(int $sessionId, ?int $sessionVacationId): Builder
  {
    return EcapStaffAssignment::query()
      ->where('academic_session_id', $sessionId)
      ->where('role', EcapVacationRole::Teacher->value)
      ->where('is_active', true)
      ->when(
        $sessionVacationId,
        fn ($query) => $query->where(function ($inner) use ($sessionVacationId): void {
          $inner->whereNull('session_vacation_id')
            ->orWhere('session_vacation_id', $sessionVacationId);
        }),
      );
  }

  /**
   * Vérifie le rôle enseignant actif sur la session.
   */
  private function hasTeacherRole(User $user, int $sessionId, ?int $vacationId): bool
  {
    return app(EcapStaffRoleService::class)->hasRole(
      $user,
      EcapVacationRole::Teacher,
      $sessionId,
      $vacationId,
    );
  }

  /**
   * Question en attente pour cet enseignant.
   */
  private function isPendingForStaff(User $staff, VacationQuestion $question): bool
  {
    if ($question->status !== VacationQuestionStatus::Pending) {
      return false;
    }

    return $this->canReply($staff, $question);
  }

  /**
   * Notifie admin et enseignant(s) après 1 h sans réponse.
   */
  private function notifyEscalation(VacationQuestion $question): void
  {
    $moduleName = $question->courseModule?->name ?? 'module';
    $title = 'Question ECAP sans réponse (1 h)';
    $body = sprintf(
      '« %s » (#%s) attend toujours une réponse depuis plus d\'une heure.',
      $question->subject,
      $moduleName,
    );
    $adminUrl = url('/admin/questions-vacation-ecap/'.$question->id.'/edit');

    $this->adminNotificationService->notifyAdmins($title, $body, $adminUrl);

    $recipients = $question->is_addressed_to_all_teachers
      ? $this->teachersForSession($question->academicSession, $question->session_vacation_id)
      : collect([$question->addressedToUser])->filter();

    foreach ($recipients as $teacher) {
      if ($teacher === null) {
        continue;
      }

      $this->notificationService->notifyWithEmail(
        $teacher,
        PortalNotificationType::EcapQuestionEscalation,
        $title,
        $body,
        route('ecap.staff.questions.index', ['module' => $question->course_module_id]),
        'Répondre maintenant',
        ['question_id' => $question->id],
      );
    }
  }

  /**
   * Sérialise un post pour le fil Inertia.
   *
   * @return array<string, mixed>
   */
  private function mapPost(VacationQuestion $question, User $viewer, string $feedBase = '/mon-espace/ecap/questions'): array
  {
    $replies = $this->buildRepliesPayload($question, $viewer);

    $myAnswer = $question->replies
      ->first(fn (VacationQuestionReply $reply) => (int) $reply->user_id === (int) $viewer->id
        && $this->isAnswerReply($reply));

    $askedPresentation = UserPresentation::for($question->askedBy);

    return [
      'id' => $question->id,
      'subject' => $question->subject,
      'body' => $question->body,
      'body_html' => $this->formatBodyWithMentions($question),
      'author_name' => $askedPresentation['name'],
      'author_initials' => $askedPresentation['initials'],
      'author_avatar_url' => $askedPresentation['avatar_url'],
      'module_name' => $question->courseModule?->name,
      'module_tag' => $question->courseModule ? $this->moduleTag($question->courseModule) : null,
      'module_url' => $question->course_module_id
        ? $this->firstChapterUrlForModule((int) $question->course_module_id)
        : null,
      'module_filter_url' => $question->course_module_id
        ? $feedBase.'?module='.$question->course_module_id
        : null,
      'addressee_label' => $this->addresseeLabel($question),
      'visibility_label' => 'Visible par tous les acteurs ECAP de la session',
      'status' => $question->status->label(),
      'status_key' => $question->status->value,
      'is_pending' => $question->status === VacationQuestionStatus::Pending,
      'can_reply' => $this->canReply($viewer, $question) && $myAnswer === null,
      'can_comment' => $this->canCommentOnQuestion($viewer, $question),
      'my_answer_id' => $myAnswer?->id,
      'is_mine' => $question->asked_by_user_id === $viewer->id,
      'created_at' => $question->created_at?->diffForHumans(),
      'created_at_full' => $question->created_at?->format('d/m/Y H:i'),
      'replies' => $replies,
      'reply_count' => $replies->count(),
    ];
  }

  /**
   * Construit la liste des réponses affichées (table + anciennes réponses answer_body).
   *
   * @return SupportCollection<int, array<string, mixed>>
   */
  private function buildRepliesPayload(VacationQuestion $question, User $viewer): SupportCollection
  {
    $replies = $this->sortedReplies($question);

    $payload = $replies
      ->map(fn (VacationQuestionReply $reply) => $this->mapReplyPayload($reply, $viewer))
      ->values();

    if (filled($question->answer_body) && $question->answered_by_user_id !== null) {
      $hasPrimaryAnswerRow = $replies->contains(
        fn (VacationQuestionReply $reply) => (int) $reply->user_id === (int) $question->answered_by_user_id
          && $this->isAnswerReply($reply),
      );

      if (! $hasPrimaryAnswerRow) {
        $legacy = $this->legacyAnswerPayload($question, $viewer);
        $payload = $payload->prepend($legacy)->values();
      }
    }

    return $payload;
  }

  /**
   * Réponses triées : réponses officielles d'abord, puis avis, par date.
   *
   * @return SupportCollection<int, VacationQuestionReply>
   */
  private function sortedReplies(VacationQuestion $question): SupportCollection
  {
    return $question->replies
      ->sortBy([
        fn (VacationQuestionReply $reply) => $this->isAnswerReply($reply) ? 0 : 1,
        fn (VacationQuestionReply $reply) => $reply->created_at?->timestamp ?? 0,
      ])
      ->values();
  }

  /**
   * Indique si une entrée du fil est une réponse officielle.
   */
  private function isAnswerReply(VacationQuestionReply $reply): bool
  {
    $type = $reply->reply_type;

    if ($type instanceof VacationQuestionReplyType) {
      return $type === VacationQuestionReplyType::Answer;
    }

    return ($type ?? VacationQuestionReplyType::Answer->value) === VacationQuestionReplyType::Answer->value;
  }

  /**
   * Sérialise une réponse historique stockée uniquement sur la question (compatibilité).
   *
   * @return array<string, mixed>
   */
  private function legacyAnswerPayload(VacationQuestion $question, User $viewer): array
  {
    $answeredPresentation = UserPresentation::for($question->answeredBy);

    return [
      'id' => 0,
      'author_name' => $answeredPresentation['name'],
      'author_initials' => $answeredPresentation['initials'],
      'author_avatar_url' => $answeredPresentation['avatar_url'],
      'body' => $question->answer_body,
      'reply_type' => VacationQuestionReplyType::Answer->value,
      'reply_type_label' => VacationQuestionReplyType::Answer->label(),
      'is_mine' => (int) $question->answered_by_user_id === (int) $viewer->id,
      'can_edit' => false,
      'created_at' => $question->answered_at?->diffForHumans(),
      'created_at_full' => $question->answered_at?->format('d/m/Y H:i'),
      'edited_at' => null,
      'edited_at_full' => null,
      'version' => 1,
      'likes_count' => 0,
      'liked_by_me' => false,
      'parent_reply_id' => null,
    ];
  }

  /**
   * Sérialise une réponse ou un avis pour le fil.
   *
   * @return array<string, mixed>
   */
  private function mapReplyPayload(VacationQuestionReply $reply, User $viewer): array
  {
    $likesCount = $reply->relationLoaded('likes')
      ? $reply->likes->count()
      : $reply->likes()->count();

    $authorPresentation = UserPresentation::for($reply->author);
    $type = $reply->reply_type instanceof VacationQuestionReplyType
      ? $reply->reply_type
      : VacationQuestionReplyType::from($reply->reply_type ?? VacationQuestionReplyType::Answer->value);

    return [
      'id' => $reply->id,
      'author_name' => $authorPresentation['name'],
      'author_initials' => $authorPresentation['initials'],
      'author_avatar_url' => $authorPresentation['avatar_url'],
      'body' => $reply->body,
      'reply_type' => $type->value,
      'reply_type_label' => $type->label(),
      'is_mine' => (int) $reply->user_id === (int) $viewer->id,
      'can_edit' => $this->canEditReply($viewer, $reply),
      'parent_reply_id' => $reply->parent_reply_id,
      'likes_count' => $likesCount,
      'liked_by_me' => $reply->relationLoaded('likes')
        ? $reply->likes->contains('user_id', $viewer->id)
        : $reply->likes()->where('user_id', $viewer->id)->exists(),
      'created_at' => $reply->created_at?->diffForHumans(),
      'created_at_full' => $reply->created_at?->format('d/m/Y H:i'),
      'edited_at' => $reply->edited_at?->diffForHumans(),
      'edited_at_full' => $reply->edited_at?->format('d/m/Y H:i'),
      'version' => (int) ($reply->version ?? 1),
    ];
  }

  /**
   * Réponse officielle déjà publiée par l'acteur sur cette question.
   */
  private function officialAnswerFromUser(VacationQuestion $question, User $staff): ?VacationQuestionReply
  {
    return $question->replies
      ->first(fn (VacationQuestionReply $reply) => (int) $reply->user_id === (int) $staff->id
        && $this->isAnswerReply($reply));
  }

  /**
   * Libellé du destinataire attendu pour la réponse.
   */
  private function addresseeLabel(VacationQuestion $question): string
  {
    if ($question->is_addressed_to_all_teachers) {
      return '@tous les enseignants (réponse attendue)';
    }

    if ($question->addressedToUser) {
      return '@'.$question->addressedToUser->name.' (réponse attendue)';
    }

    return 'Enseignant ECAP';
  }

  /**
   * Tag # pour un module.
   */
  private function moduleTag(CourseModule $module): string
  {
    return '#'.Str::slug($module->name, '_');
  }

  /**
   * Acteurs ECAP mentionnables (@) sur une session.
   *
   * @return SupportCollection<int, User>
   */
  private function staffMembersForSession(AcademicSession $session, ?int $sessionVacationId = null): SupportCollection
  {
    $roles = [
      EcapVacationRole::Teacher->value,
      EcapVacationRole::Supervisor->value,
      EcapVacationRole::Moderator->value,
    ];

    $userIds = EcapStaffAssignment::query()
      ->where('academic_session_id', $session->id)
      ->whereIn('role', $roles)
      ->where('is_active', true)
      ->when(
        $sessionVacationId,
        fn ($query) => $query->where(function ($inner) use ($sessionVacationId): void {
          $inner->whereNull('session_vacation_id')
            ->orWhere('session_vacation_id', $sessionVacationId);
        }),
      )
      ->pluck('user_id')
      ->unique();

    return User::query()
      ->whereIn('id', $userIds)
      ->orderBy('name')
      ->get();
  }

  /**
   * Entrée catalogue pour un acteur ECAP.
   *
   * @return array<string, mixed>
   */
  private function mapCatalogUser(User $staffMember, int $sessionId, string $feedBasePath): array
  {
    $roles = app(EcapStaffRoleService::class)->rolesForSession($staffMember, $sessionId);
    $primaryRole = $roles[0] ?? EcapVacationRole::Teacher;

    return [
      'id' => $staffMember->id,
      'name' => $staffMember->name,
      'mention' => $this->userMention($staffMember),
      'role' => $primaryRole->value,
      'role_label' => $primaryRole->label(),
      'profile_url' => route('members.show', $staffMember),
      'filter_url' => $feedBasePath.'?addressee='.$staffMember->id,
    ];
  }

  /**
   * URL du premier chapitre publié d'un module ECAP.
   */
  private function firstChapterUrlForModule(int $moduleId): ?string
  {
    $chapter = Chapter::query()
      ->where('course_module_id', $moduleId)
      ->where('is_published', true)
      ->orderBy('sort_order')
      ->first();

    return $chapter ? route('chapter.show', $chapter) : null;
  }

  /**
   * URLs du premier chapitre par module ECAP.
   *
   * @return array<int, string>
   */
  private function firstChapterUrlsByModule(): array
  {
    $moduleIds = $this->ecapCourseModules()->pluck('id');

    if ($moduleIds->isEmpty()) {
      return [];
    }

    $chapters = Chapter::query()
      ->whereIn('course_module_id', $moduleIds)
      ->where('is_published', true)
      ->orderBy('course_module_id')
      ->orderBy('sort_order')
      ->get();

    $urls = [];

    foreach ($chapters as $chapter) {
      $moduleId = (int) $chapter->course_module_id;

      if (! isset($urls[$moduleId])) {
        $urls[$moduleId] = route('chapter.show', $chapter);
      }
    }

    return $urls;
  }

  /**
   * Session ECAP pertinente pour un utilisateur connecté.
   */
  private function resolveViewerSessionId(User $viewer): ?int
  {
    $studentSession = $this->studentSession($viewer);

    if ($studentSession !== null) {
      return (int) $studentSession->id;
    }

    $staffSessionId = EcapStaffAssignment::query()
      ->where('user_id', $viewer->id)
      ->where('is_active', true)
      ->value('academic_session_id');

    return $staffSessionId ? (int) $staffSessionId : null;
  }

  /**
   * Indique si l'utilisateur est acteur ECAP sur la session.
   */
  private function isStaffOnSession(User $user, int $sessionId): bool
  {
    return EcapStaffAssignment::query()
      ->where('user_id', $user->id)
      ->where('academic_session_id', $sessionId)
      ->where('is_active', true)
      ->exists();
  }

  /**
   * Tag # pour un chapitre.
   */
  private function chapterTag(Chapter $chapter): string
  {
    return '#'.Str::slug($chapter->title, '_');
  }

  /**
   * Mention @ pour un utilisateur.
   */
  private function userMention(User $user): string
  {
    $handle = Str::slug($user->name, '_');

    return '@'.$handle;
  }

  /**
   * Met en évidence @ et # dans le corps du message.
   */
  private function formatBodyWithMentions(VacationQuestion $question): string
  {
    $escaped = e($question->body);
    $withTags = preg_replace(
      '/(#\w+)/u',
      '<span class="text-phila-orange font-semibold">$1</span>',
      $escaped,
    );

    return preg_replace(
      '/(@[\wÀ-ÿ_-]+)/u',
      '<span class="text-blue-600 font-semibold">$1</span>',
      $withTags ?? $escaped,
    );
  }

  /**
   * Initiales pour l'avatar.
   */
  private function initials(string $name): string
  {
    $parts = array_filter(explode(' ', trim($name)));

    if (count($parts) === 0) {
      return 'PH';
    }

    if (count($parts) === 1) {
      return strtoupper(substr($parts[0], 0, 2));
    }

    return strtoupper(substr($parts[0], 0, 1).substr(end($parts), 0, 1));
  }
}
