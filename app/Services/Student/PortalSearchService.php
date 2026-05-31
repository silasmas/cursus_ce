<?php

namespace App\Services\Student;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\EcapStaffAssignment;
use App\Models\Program;
use App\Models\User;
use App\Models\VacationQuestion;
use App\Models\VacationQuestionReply;
use App\Services\Ecap\EcapPrivateChatService;
use App\Services\Ecap\VacationQuestionService;
use Illuminate\Support\Str;

/**
 * Recherche instantanée dans le portail (cursus, fil ECAP, fidèles pour acteurs).
 */
class PortalSearchService
{
  /**
   * @param  CursusProgressService  $cursusProgressService  Parcours des 5 cursus
   * @param  EcapPrivateChatService  $ecapPrivateChatService  Contacts ECAP
   * @param  VacationQuestionService  $vacationQuestionService  Fil questions ECAP
   */
  public function __construct(
    private readonly CursusProgressService $cursusProgressService,
    private readonly EcapPrivateChatService $ecapPrivateChatService,
    private readonly VacationQuestionService $vacationQuestionService,
  ) {}

  /**
   * Recherche selon le contexte de la page.
   *
   * @return array{results: array<int, array<string, mixed>>}
   */
  public function search(User $user, string $query, string $context = 'global', int $limit = 15): array
  {
    $needle = mb_strtolower(trim($query));

    if (mb_strlen($needle) < 2) {
      return ['results' => []];
    }

    $results = match ($context) {
      'ecap_questions' => $this->searchEcapQuestions($user, $needle, $limit),
      'ecap_staff' => $this->searchForEcapStaff($user, $needle, $limit),
      default => $this->searchGlobal($user, $needle, $limit),
    };

    return ['results' => $results];
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function searchGlobal(User $user, string $needle, int $limit): array
  {
    $results = [];

    foreach (config('cursus.modules', []) as $definition) {
      if ($this->matches($needle, $definition['name'] ?? '', $definition['subtitle'] ?? '')) {
        $results[] = [
          'type' => 'cursus',
          'type_label' => 'Cursus',
          'label' => $definition['name'],
          'subtitle' => $definition['subtitle'] ?? '',
          'url' => '/mon-espace?cursus='.($definition['slug'] ?? ''),
        ];
      }
    }

    $cursus = $this->cursusProgressService->forUser($user);

    foreach ($cursus['modules'] as $module) {
      $programName = $module['name'] ?? '';
      $programSlug = $module['slug'] ?? '';

      if ($this->matches($needle, $programName, $module['subtitle'] ?? '')) {
        $results[] = [
          'type' => 'cursus',
          'type_label' => 'Cursus',
          'label' => $programName,
          'subtitle' => 'Progression : '.($module['stats']['progress'] ?? 0).'%',
          'url' => '/mon-espace?cursus='.$programSlug,
        ];
      }

      foreach ($module['steps'] ?? [] as $step) {
        if (! $this->matches($needle, $step['title'] ?? '', $step['module'] ?? '', $step['course'] ?? '')) {
          continue;
        }

        $results[] = [
          'type' => 'chapitre',
          'type_label' => 'Chapitre',
          'label' => $step['title'],
          'subtitle' => trim(($step['module'] ?? '').' · '.($step['course'] ?? '')),
          'url' => '/mon-espace/cours/'.$step['id'],
        ];

        if (! empty($step['instructor']['name']) && $this->matches($needle, $step['instructor']['name'])) {
          $results[] = [
            'type' => 'prof',
            'type_label' => 'Professeur',
            'label' => $step['instructor']['name'],
            'subtitle' => 'Chapitre : '.$step['title'],
            'url' => '/mon-espace/cours/'.$step['id'],
          ];
        }
      }

      foreach ($module['content_modules'] ?? [] as $courseModule) {
        if (! $this->matches($needle, $courseModule['name'] ?? '')) {
          continue;
        }

        $moduleId = $courseModule['course_module_id'] ?? null;

        $results[] = [
          'type' => 'module',
          'type_label' => 'Module',
          'label' => $courseModule['name'],
          'subtitle' => $programName,
          'url' => $programSlug === 'ecap' && $moduleId
            ? '/mon-espace/ecap/questions?module='.$moduleId
            : '/mon-espace?cursus='.$programSlug,
        ];
      }
    }

    $this->appendCourses($needle, $results);
    $this->appendEcapActorsForMentee($user, $needle, $results);

    return $this->uniqueResults($results, $limit);
  }

  /**
   * Recherche dans le fil Q&R ECAP (fidèle).
   *
   * @return array<int, array<string, mixed>>
   */
  private function searchEcapQuestions(User $user, string $needle, int $limit): array
  {
    $session = $this->vacationQuestionService->studentSession($user);

    if ($session === null) {
      return [];
    }

    $results = [];
    $like = '%'.$needle.'%';

    VacationQuestion::query()
      ->where('academic_session_id', $session->id)
      ->whereRaw('LOWER(body) LIKE ?', [$like])
      ->with('askedBy')
      ->latest()
      ->limit(8)
      ->get()
      ->each(function (VacationQuestion $question) use (&$results): void {
        $results[] = [
          'type' => 'question',
          'type_label' => 'Question',
          'label' => Str::limit($question->body, 72),
          'subtitle' => $question->askedBy?->name ?? 'Fidèle',
          'url' => '/mon-espace/ecap/questions?question='.$question->id,
        ];
      });

    $teachers = $this->vacationQuestionService->teachersForSession($session);

    foreach ($teachers as $teacher) {
      if (! $this->matches($needle, $teacher->name, $teacher->email)) {
        continue;
      }

      $replyCount = VacationQuestionReply::query()
        ->where('author_id', $teacher->id)
        ->whereHas('question', fn ($query) => $query->where('academic_session_id', $session->id))
        ->count();

      $results[] = [
        'type' => 'prof',
        'type_label' => 'Enseignant',
        'label' => $teacher->name,
        'subtitle' => $replyCount > 0
          ? "{$replyCount} réponse(s) dans le fil"
          : 'Voir ses réponses',
        'url' => '/mon-espace/ecap/questions?addressee='.$teacher->id,
      ];
    }

    VacationQuestionReply::query()
      ->whereHas('question', fn ($query) => $query->where('academic_session_id', $session->id))
      ->whereRaw('LOWER(body) LIKE ?', [$like])
      ->with(['author', 'question'])
      ->latest()
      ->limit(6)
      ->get()
      ->each(function (VacationQuestionReply $reply) use (&$results): void {
        $results[] = [
          'type' => 'reponse',
          'type_label' => 'Réponse',
          'label' => Str::limit($reply->body, 60),
          'subtitle' => ($reply->author?->name ?? 'Acteur').' · '.Str::limit($reply->question?->body ?? '', 40),
          'url' => '/mon-espace/ecap/questions?question='.$reply->vacation_question_id,
        ];
      });

    return $this->uniqueResults($results, $limit);
  }

  /**
   * Recherche réservée aux acteurs ECAP (fidèles par nom, e-mail, téléphone).
   *
   * @return array<int, array<string, mixed>>
   */
  private function searchForEcapStaff(User $user, string $needle, int $limit): array
  {
    if (! $this->isEcapStaff($user)) {
      return [];
    }

    $results = [];
    $session = $this->ecapPrivateChatService->sessionForChatUser($user);

    if ($session === null) {
      return [];
    }

    $like = '%'.$needle.'%';

    User::query()
      ->with('profile')
      ->whereHas('profile', fn ($query) => $query->where('academic_session_id', $session->id))
      ->where(function ($query) use ($like): void {
        $query->whereRaw('LOWER(name) LIKE ?', [$like])
          ->orWhereRaw('LOWER(email) LIKE ?', [$like])
          ->orWhereHas('profile', fn ($inner) => $inner->where('phone', 'like', $like));
      })
      ->orderBy('name')
      ->limit(12)
      ->get()
      ->each(function (User $mentee) use (&$results): void {
        $phone = $mentee->profile?->phone;

        $results[] = [
          'type' => 'fidele',
          'type_label' => 'Fidèle',
          'label' => $mentee->name,
          'subtitle' => trim($mentee->email.($phone ? " · {$phone}" : '')),
          'url' => '/ecap/acteurs/messages?peer='.$mentee->id,
        ];
      });

    VacationQuestion::query()
      ->where('academic_session_id', $session->id)
      ->whereRaw('LOWER(body) LIKE ?', [$like])
      ->with('askedBy')
      ->latest()
      ->limit(5)
      ->get()
      ->each(function (VacationQuestion $question) use (&$results): void {
        $results[] = [
          'type' => 'question',
          'type_label' => 'Question',
          'label' => Str::limit($question->body, 72),
          'subtitle' => $question->askedBy?->name ?? 'Fidèle',
          'url' => '/ecap/acteurs/questions?author='.$question->asked_by_user_id,
        ];
      });

    return $this->uniqueResults($results, $limit);
  }

  /**
   * @param  array<int, array<string, mixed>>  $results
   */
  private function appendCourses(string $needle, array &$results): void
  {
    $programIds = Program::query()
      ->where('is_active', true)
      ->pluck('id');

    $courses = Course::query()
      ->with('program')
      ->whereIn('program_id', $programIds)
      ->where('is_published', true)
      ->where(function ($query) use ($needle) {
        $query->whereRaw('LOWER(name) LIKE ?', ['%'.$needle.'%'])
          ->orWhereRaw('LOWER(slug) LIKE ?', ['%'.$needle.'%']);
      })
      ->limit(8)
      ->get();

    foreach ($courses as $course) {
      $chapterId = Chapter::query()
        ->where('course_id', $course->id)
        ->where('is_published', true)
        ->orderBy('sort_order')
        ->value('id');

      $results[] = [
        'type' => 'cours',
        'type_label' => 'Cours',
        'label' => $course->name,
        'subtitle' => $course->program?->name ?? 'Formation',
        'url' => $chapterId ? '/mon-espace/cours/'.$chapterId : '/mon-espace?cursus='.($course->program?->slug ?? 'ecap'),
      ];
    }
  }

  /**
   * Superviseurs / modérateurs contactables (pas de recherche d'autres fidèles).
   *
   * @param  array<int, array<string, mixed>>  $results
   */
  private function appendEcapActorsForMentee(User $user, string $needle, array &$results): void
  {
    if ($this->isEcapStaff($user)) {
      return;
    }

    $session = $this->ecapPrivateChatService->sessionForChatUser($user);

    if ($session === null) {
      return;
    }

    foreach ($this->ecapPrivateChatService->contactsForUser($user, $session->id) as $contact) {
      if (! $this->matches($needle, $contact['name'] ?? '', $contact['role'] ?? '')) {
        continue;
      }

      $roleKey = Str::lower($contact['role'] ?? '');
      $type = str_contains($roleKey, 'superviseur') ? 'superviseur' : 'moderateur';

      $results[] = [
        'type' => $type,
        'type_label' => $contact['role'],
        'label' => $contact['name'],
        'subtitle' => 'Message privé ECAP',
        'url' => '/mon-espace/ecap/messages?peer='.$contact['id'],
      ];
    }
  }

  /**
   * @param  array<int, array<string, mixed>>  $results
   * @return array<int, array<string, mixed>>
   */
  private function uniqueResults(array $results, int $limit): array
  {
    return collect($results)
      ->unique(fn (array $row) => $row['type'].'|'.$row['url'].'|'.$row['label'])
      ->take($limit)
      ->values()
      ->all();
  }

  private function isEcapStaff(User $user): bool
  {
    return EcapStaffAssignment::query()
      ->where('user_id', $user->id)
      ->where('is_active', true)
      ->exists();
  }

  private function matches(string $needle, string ...$haystacks): bool
  {
    foreach ($haystacks as $haystack) {
      if ($haystack !== '' && str_contains(mb_strtolower($haystack), $needle)) {
        return true;
      }
    }

    return false;
  }
}
