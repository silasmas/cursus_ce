<?php

namespace Database\Seeders;

use App\Enums\AssessmentType;
use App\Enums\QuestionType;
use App\Models\Assessment;
use App\Models\Chapter;
use App\Models\MentorAssignment;
use App\Models\MentorProfile;
use App\Models\Program;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Données de démonstration : tests, TP et mentor Métamorpho.
 */
class PortalDemoSeeder extends Seeder
{
  /**
   * Crée quiz, TP et assignation mentor pour tester le portail.
   */
  public function run(): void
  {
    $this->seedPhilaQuiz();
    $this->seedEcapTp();
    $this->seedMetamorphoMentor();
    $this->seedInstructors();
  }

  /**
   * Quiz QCM + question rédigée sur le 1er chapitre PHILA.
   */
  private function seedPhilaQuiz(): void
  {
    $chapter = Chapter::query()
      ->whereHas('course.program', fn ($q) => $q->where('slug', 'connaissez-phila'))
      ->where('title', 'Notre vision et mission')
      ->first();

    if (! $chapter) {
      return;
    }

    $program = $chapter->course->program;

    $assessment = Assessment::query()->firstOrCreate(
      ['chapter_id' => $chapter->id, 'title' => 'Quiz — Vision PHILA', 'type' => AssessmentType::Quiz->value],
      [
        'program_id' => $program->id,
        'course_id' => $chapter->course_id,
        'passing_score' => 60,
        'max_attempts' => 3,
        'is_published' => true,
      ],
    );

    $mcq = Question::query()->firstOrCreate(
      ['assessment_id' => $assessment->id, 'stem' => 'Quelle est la mission de PHILA ?', 'type' => QuestionType::Mcq->value],
      ['sort_order' => 1, 'points' => 1],
    );

    QuestionOption::query()->firstOrCreate(
      ['question_id' => $mcq->id, 'label' => 'Exposer chaque personne à la Parole de Dieu'],
      ['is_correct' => true, 'sort_order' => 1],
    );

    QuestionOption::query()->firstOrCreate(
      ['question_id' => $mcq->id, 'label' => 'Organiser des événements sportifs'],
      ['is_correct' => false, 'sort_order' => 2],
    );

    Question::query()->firstOrCreate(
      ['assessment_id' => $assessment->id, 'stem' => 'En une phrase, que signifie « Cité d\'Exaucement » pour vous ?', 'type' => QuestionType::Written->value],
      ['sort_order' => 2, 'points' => 1],
    );
  }

  /**
   * TP sur le 1er chapitre ECAP.
   */
  private function seedEcapTp(): void
  {
    $chapter = Chapter::query()
      ->whereHas('course.program', fn ($q) => $q->where('slug', 'ecap'))
      ->where('title', 'Introduction à Apollos CE')
      ->first();

    if (! $chapter) {
      return;
    }

    Assessment::query()->firstOrCreate(
      ['chapter_id' => $chapter->id, 'title' => 'TP — Synthèse Apollos', 'type' => AssessmentType::Tp->value],
      [
        'program_id' => $chapter->course->program_id,
        'course_id' => $chapter->course_id,
        'passing_score' => 50,
        'max_attempts' => 1,
        'is_published' => true,
      ],
    );
  }

  /**
   * Compte mentor de démo + profil pour Métamorpho.
   */
  private function seedMetamorphoMentor(): void
  {
    $program = Program::query()->where('slug', 'metamorpho')->first();

    if (! $program) {
      return;
    }

    $mentorEmail = 'silasmas@outlook.fr';

    $mentorUser = User::query()->firstOrCreate(
      ['email' => $mentorEmail],
      ['name' => 'Silas Mas — Mentor', 'password' => 'password'],
    );

    MentorProfile::query()->updateOrCreate(
      ['user_id' => $mentorUser->id],
      [
        'max_mentees' => 10,
        'is_accepting_assignments' => true,
        'bio' => 'Serviteur de Dieu passionné par l\'accompagnement spirituel des jeunes fidèles dans leur croissance en Christ.',
        'phone' => '+243900000001',
        'whatsapp' => '243900000001',
        'notes' => 'Mentor Métamorpho',
      ],
    );

    MentorAssignment::query()
      ->where('mentor_id', '!=', $mentorUser->id)
      ->whereHas('mentor', fn ($q) => $q->where('email', 'mentor@example.com'))
      ->update(['mentor_id' => $mentorUser->id]);

    $mentee = User::query()->whereIn('email', ['admin@example.com', 'silasjmas@gmail.com'])->first();

    if (! $mentee) {
      return;
    }

    MentorAssignment::query()->firstOrCreate(
      [
        'mentor_id' => $mentorUser->id,
        'mentee_id' => $mentee->id,
        'program_id' => $program->id,
      ],
      [
        'assignment_mode' => 'manual',
        'status' => 'active',
        'started_at' => now()->subWeeks(2),
      ],
    );
  }

  /**
   * Assigne un enseignant aux chapitres de démonstration.
   */
  private function seedInstructors(): void
  {
    $instructor = User::query()->where('email', 'silasmas@outlook.fr')->first()
      ?? User::query()->where('email', 'admin@example.com')->first();

    if (! $instructor) {
      return;
    }

    Chapter::query()
      ->whereHas('course.program', fn ($q) => $q->whereIn('slug', ['connaissez-phila', 'ecap', 'metamorpho']))
      ->update(['instructor_user_id' => $instructor->id]);

    $metamorphoChapter = Chapter::query()
      ->whereHas('course.program', fn ($q) => $q->where('slug', 'metamorpho'))
      ->where('title', 'Premier rapport de suivi')
      ->first();

    if ($metamorphoChapter) {
      Assessment::query()->firstOrCreate(
        ['chapter_id' => $metamorphoChapter->id, 'title' => 'TP — Rapport Métamorpho', 'type' => AssessmentType::Tp->value],
        [
          'program_id' => $metamorphoChapter->course->program_id,
          'course_id' => $metamorphoChapter->course_id,
          'passing_score' => 50,
          'max_attempts' => 1,
          'is_published' => true,
        ],
      );
    }
  }
}
