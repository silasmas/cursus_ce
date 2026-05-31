<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Program;
use Illuminate\Database\Seeder;

/**
 * Crée les 5 cursus PHILA-CE et leur contenu de démonstration.
 */
class FormationContentSeeder extends Seeder
{
  /**
   * Exécute le seed des 5 programmes de formation.
   */
  public function run(): void
  {
    $this->seedConnaissezPhila();
    $this->seedMetamorpho();
    $this->seedEcap();
    $this->seedGifted();
    $this->seedEyano();
    $this->seedDemoContent();
  }

  /**
   * Cursus 1 — Connaissez-vous PHILA.
   */
  private function seedConnaissezPhila(): void
  {
    $program = $this->createProgram(
      'connaissez-phila',
      'Connaissez-vous PHILA',
      'Accueillir et introduire le fidèle à l\'église.',
      1,
    );

    $course = $this->createCourse($program, 'decouverte-phila', 'Découverte PHILA', 1);
    $module = $this->createModule($course, 'Bienvenue à PHILA', 1);

    $this->createChapters($course, $module, [
      'Notre vision et mission',
      'Nos valeurs et notre identité',
      'La famille PHILA',
    ]);
  }

  /**
   * Cursus 2 — Métamorpho.
   */
  private function seedMetamorpho(): void
  {
    $program = $this->createProgram(
      'metamorpho',
      'Métamorpho',
      'Accompagnement spirituel personnalisé avec mentor.',
      2,
    );

    $course = $this->createCourse($program, 'metamorpho-parcours', 'Parcours Métamorpho', 1);
    $module = $this->createModule($course, 'Accompagnement', 1);

    $this->createChapters($course, $module, [
      'Introduction au parcours',
      'Rencontre avec votre mentor',
      'Premier rapport de suivi',
    ]);
  }

  /**
   * Cursus 3 — ECAP (École d'Apolos).
   */
  private function seedEcap(): void
  {
    $program = $this->createProgram(
      'ecap',
      'ECAP',
      'Apollos CE — École d\'Apolos : formation biblique structurée (cursus ECAP).',
      3,
    );

    Program::query()
      ->where('slug', 'ecap')
      ->update([
        'name' => 'ECAP',
        'description' => 'Apollos CE — École d\'Apolos : formation biblique structurée (cursus ECAP).',
      ]);

    $session = AcademicSession::query()->firstOrCreate(
      ['code' => '2026-V1'],
      [
        'program_id' => $program->id,
        'name' => 'Session ECAP 2026',
        'generation_number' => 5,
        'starts_on' => now()->startOfYear(),
        'ends_on' => now()->endOfYear(),
        'registration_opens_at' => now()->subWeek(),
        'registration_closes_at' => now()->addMonths(2),
        'is_active' => true,
      ],
    );

    \App\Models\SessionVacation::query()->firstOrCreate(
      [
        'academic_session_id' => $session->id,
        'code' => 'MATIN',
      ],
      [
        'name' => 'Vacation du matin',
        'time_starts' => '08:00:00',
        'time_ends' => '12:00:00',
        'sort_order' => 1,
        'is_active' => true,
      ],
    );

    \App\Models\SessionVacation::query()->firstOrCreate(
      [
        'academic_session_id' => $session->id,
        'code' => 'SOIR',
      ],
      [
        'name' => 'Vacation du soir',
        'time_starts' => '14:00:00',
        'time_ends' => '18:00:00',
        'sort_order' => 2,
        'is_active' => true,
      ],
    );

    $course = $this->createCourse($program, 'fondamentaux-apollos', 'Fondamentaux Apollos', 1);
    $module1 = $this->createModule($course, 'Chap. 1 — Nouvelle naissance', 1);
    $module2 = $this->createModule($course, 'Chap. 2 — L\'Église', 2);
    $module3 = $this->createModule($course, 'Chap. 3 — La méditation', 3);
    $module4 = $this->createModule($course, 'Chap. 4 — Connaître Dieu', 4);

    $this->createChapters($course, $module1, [
      'Introduction à Apollos CE',
      'La nouvelle naissance',
      'La repentance',
    ]);

    $this->createChapters($course, $module2, [
      'L\'Église — fondements',
      'Le baptême',
      'La communion des saints',
    ]);

    $this->createChapters($course, $module3, [
      'La méditation biblique',
      'L\'inspiration des Écritures',
      'Cahier de méditation',
    ]);

    $this->createChapters($course, $module4, [
      'Connaître Dieu',
      'Méditation — Éphésiens (observation)',
      'Méditation — Éphésiens (interprétation & application)',
    ]);

    \App\Models\SessionModuleSchedule::query()->firstOrCreate(
      [
        'academic_session_id' => $session->id,
        'course_module_id' => $module1->id,
        'item_type' => \App\Enums\CalendarItemType::Module,
      ],
      [
        'starts_on' => now()->startOfYear(),
        'ends_on' => now()->startOfYear()->addMonths(3),
        'sort_order' => 1,
      ],
    );

    \App\Models\SessionModuleSchedule::query()->firstOrCreate(
      [
        'academic_session_id' => $session->id,
        'course_module_id' => $module2->id,
        'item_type' => \App\Enums\CalendarItemType::Module,
      ],
      [
        'starts_on' => now()->startOfYear()->addMonths(3)->addDay(),
        'ends_on' => now()->startOfYear()->addMonths(6),
        'sort_order' => 2,
      ],
    );

    \App\Models\SessionModuleSchedule::query()->firstOrCreate(
      [
        'academic_session_id' => $session->id,
        'course_module_id' => $module3->id,
        'item_type' => \App\Enums\CalendarItemType::Module,
      ],
      [
        'starts_on' => now()->startOfYear()->addMonths(4),
        'ends_on' => now()->startOfYear()->addMonths(5),
        'sort_order' => 3,
      ],
    );

    \App\Models\SessionModuleSchedule::query()->firstOrCreate(
      [
        'academic_session_id' => $session->id,
        'course_module_id' => $module4->id,
        'item_type' => \App\Enums\CalendarItemType::Module,
      ],
      [
        'starts_on' => now()->startOfYear()->addMonths(5)->addDay(),
        'ends_on' => now()->startOfYear()->addMonths(6),
        'sort_order' => 4,
      ],
    );

    $coursesPeriod = \App\Models\SessionPeriod::query()->firstOrCreate(
      [
        'academic_session_id' => $session->id,
        'type' => \App\Enums\SessionPeriodType::Courses,
      ],
      [
        'name' => 'Période des cours',
        'starts_on' => now()->startOfYear(),
        'ends_on' => now()->startOfYear()->addMonths(8),
        'sort_order' => 1,
        'is_active' => true,
      ],
    );

    \App\Models\SessionPeriodContent::query()->firstOrCreate(
      [
        'session_period_id' => $coursesPeriod->id,
        'content_type' => \App\Enums\PeriodContentType::CourseModule,
        'content_id' => $module1->id,
      ],
      ['sort_order' => 1],
    );

    \App\Models\SessionPeriodContent::query()->firstOrCreate(
      [
        'session_period_id' => $coursesPeriod->id,
        'content_type' => \App\Enums\PeriodContentType::CourseModule,
        'content_id' => $module2->id,
      ],
      ['sort_order' => 2],
    );

    \App\Models\SessionPeriodContent::query()->firstOrCreate(
      [
        'session_period_id' => $coursesPeriod->id,
        'content_type' => \App\Enums\PeriodContentType::CourseModule,
        'content_id' => $module3->id,
      ],
      ['sort_order' => 3],
    );

    \App\Models\SessionPeriodContent::query()->firstOrCreate(
      [
        'session_period_id' => $coursesPeriod->id,
        'content_type' => \App\Enums\PeriodContentType::CourseModule,
        'content_id' => $module4->id,
      ],
      ['sort_order' => 4],
    );

    $this->seedModuleExitQuiz($program, $course, $module1);
    $this->seedModuleExitQuiz($program, $course, $module2);
    $this->seedModuleExitQuiz($program, $course, $module3);
    $this->seedModuleExitQuiz($program, $course, $module4);
  }

  /**
   * Quiz de fin de module ECAP (M5) : 5 questions, 80 %, chapitres de révision.
   */
  private function seedModuleExitQuiz(\App\Models\Program $program, \App\Models\Course $course, \App\Models\CourseModule $module): void
  {
    $chapters = \App\Models\Chapter::query()
      ->where('course_module_id', $module->id)
      ->orderBy('sort_order')
      ->get();

    if ($chapters->isEmpty()) {
      return;
    }

    $assessment = \App\Models\Assessment::query()->firstOrCreate(
      [
        'course_module_id' => $module->id,
        'is_module_exit_quiz' => true,
      ],
      [
        'program_id' => $program->id,
        'course_id' => $course->id,
        'title' => 'Quiz fin de module — '.$module->name,
        'type' => \App\Enums\AssessmentType::Quiz->value,
        'passing_score' => 80,
        'max_attempts' => 3,
        'is_published' => true,
      ],
    );

    $stems = [
      'Quel est le thème central de ce module ?',
      'Quelle vérité biblique résume ce module ?',
      'Quelle application pratique retenez-vous ?',
      'Quel fondement spirituel est abordé ?',
      'Comment ce module vous aide-t-il à grandir ?',
    ];

    foreach ($stems as $index => $stem) {
      $chapter = $chapters[$index % $chapters->count()];

      $question = \App\Models\Question::query()->firstOrCreate(
        [
          'assessment_id' => $assessment->id,
          'stem' => $stem,
        ],
        [
          'type' => \App\Enums\QuestionType::Mcq->value,
          'sort_order' => $index + 1,
          'points' => 1,
          'review_chapter_id' => $chapter->id,
        ],
      );

      \App\Models\QuestionOption::query()->firstOrCreate(
        ['question_id' => $question->id, 'label' => 'Réponse conforme au module'],
        ['is_correct' => true, 'sort_order' => 1],
      );

      \App\Models\QuestionOption::query()->firstOrCreate(
        ['question_id' => $question->id, 'label' => 'Réponse incorrecte'],
        ['is_correct' => false, 'sort_order' => 2],
      );
    }
  }

  /**
   * Cursus 4 — École des dons (Gifted).
   */
  private function seedGifted(): void
  {
    $program = $this->createProgram(
      'gifted',
      'École des dons — Gifted',
      'Identification et orientation selon les dons spirituels.',
      4,
    );

    $course = $this->createCourse($program, 'decouverte-dons', 'Découverte des dons', 1);
    $module = $this->createModule($course, 'Évaluation des dons', 1);

    $this->createChapters($course, $module, [
      'Comprendre les dons spirituels',
      'Test d\'évaluation',
      'Orientation et appel',
    ]);
  }

  /**
   * Cursus 5 — Eyano (École de prière).
   */
  private function seedEyano(): void
  {
    $program = $this->createProgram(
      'eyano',
      'Eyano — École de prière',
      'Formation pratique à la prière avec encadrement mentor.',
      5,
    );

    $course = $this->createCourse($program, 'ecole-priere', 'École de prière', 1);
    $module = $this->createModule($course, 'Fondements de la prière', 1);

    $this->createChapters($course, $module, [
      'Les bases de la prière',
      'Sessions de prière guidées',
      'Test final Eyano',
    ]);
  }

  /**
   * Crée ou met à jour un programme.
   */
  private function createProgram(string $slug, string $name, string $description, int $order): Program
  {
    return Program::query()->updateOrCreate(
      ['slug' => $slug],
      [
        'name' => $name,
        'description' => $description,
        'sort_order' => $order,
        'is_active' => true,
        'type' => 'cursus',
      ],
    );
  }

  /**
   * Crée ou met à jour un cours.
   */
  private function createCourse(Program $program, string $slug, string $name, int $order): Course
  {
    return Course::query()->firstOrCreate(
      ['program_id' => $program->id, 'slug' => $slug],
      [
        'name' => $name,
        'sort_order' => $order,
        'is_published' => true,
      ],
    );
  }

  /**
   * Crée ou met à jour un module de cours.
   */
  private function createModule(Course $course, string $name, int $order): CourseModule
  {
    return CourseModule::query()->firstOrCreate(
      ['course_id' => $course->id, 'name' => $name],
      ['sort_order' => $order],
    );
  }

  /**
   * Crée les chapitres d'un module.
   *
   * @param  array<int, string>  $titles
   */
  private function createChapters(Course $course, CourseModule $module, array $titles): void
  {
    foreach ($titles as $index => $title) {
      Chapter::query()->firstOrCreate(
        [
          'course_id' => $course->id,
          'course_module_id' => $module->id,
          'title' => $title,
        ],
        [
          'sort_order' => $index + 1,
          'is_published' => true,
        ],
      );
    }
  }

  /**
   * Alimente texte + vidéo YouTube pour tous les chapitres publiés.
   */
  private function seedDemoContent(): void
  {
    app(\App\Services\Content\ChapterYouTubeContentService::class)->seedAllPublishedChapters();
  }

  /**
   * @deprecated Conservé pour référence — remplacé par ChapterYouTubeContentService.
   */
  private function seedDemoContentLegacy(): void
  {
    $chapter = Chapter::query()
      ->whereHas('course.program', fn ($q) => $q->where('slug', 'connaissez-phila'))
      ->where('title', 'Notre vision et mission')
      ->first();

    if (! $chapter) {
      return;
    }

    \App\Models\ContentBlock::query()->firstOrCreate(
      ['chapter_id' => $chapter->id, 'type' => 'text', 'sort_order' => 1],
      [
        'title' => 'Bienvenue à PHILA',
        'body' => "La Cité d'Exaucement est une église ambassadrice de Christ.\n\nNotre mission : exposer chaque personne à la Parole de Dieu pour que la vie de Christ se manifeste en elle.",
      ],
    );

    \App\Models\ContentBlock::query()->firstOrCreate(
      ['chapter_id' => $chapter->id, 'type' => 'video', 'sort_order' => 2],
      [
        'title' => 'Message de bienvenue',
        'url' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
      ],
    );
  }
}
