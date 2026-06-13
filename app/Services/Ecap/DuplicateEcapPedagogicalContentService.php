<?php

namespace App\Services\Ecap;

use App\Enums\AssessmentType;
use App\Models\AcademicSession;
use App\Models\Assessment;
use App\Models\Chapter;
use App\Models\ContentBlock;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\SessionModuleSchedule;
use Illuminate\Support\Str;

/**
 * Clone le contenu pédagogique ECAP (cours, chapitres, quiz, TP) pour une nouvelle session.
 */
class DuplicateEcapPedagogicalContentService
{
  /**
   * Duplique le cours source vers la session cible avec remapping des identifiants.
   *
   * @throws \InvalidArgumentException
   */
  public function duplicateForSession(
    AcademicSession $target,
    AcademicSession $source,
    DuplicateEcapSessionOptions $options,
  ): PedagogicalCloneResult {
    if (! $source->isEcap() || ! $target->isEcap()) {
      throw new \InvalidArgumentException('Seules les sessions ECAP peuvent être dupliquées.');
    }

    if ($source->is($target)) {
      throw new \InvalidArgumentException('Choisissez une session source différente de la session cible.');
    }

    $sourceCourse = $this->resolveCourseForSession($source);

    if ($sourceCourse === null) {
      throw new \InvalidArgumentException('Aucun cours ECAP trouvé pour la session source.');
    }

    if ($target->courses()->exists()) {
      throw new \InvalidArgumentException('Cette session possède déjà un cours cloné.');
    }

    $counts = [
      'courses' => 0,
      'modules' => 0,
      'chapters' => 0,
      'content_blocks' => 0,
      'assessments' => 0,
      'questions' => 0,
      'question_options' => 0,
    ];

    $maps = new PedagogicalCloneMaps();

    $sourceCourse->load([
      'courseModules.chapters.contentBlocks',
      'courseModules.chapters.assessments.questions.options',
      'courseModules.moduleExitQuiz.questions.options',
      'assessments.questions.options',
    ]);

    $newCourse = Course::query()->create([
      'program_id' => $sourceCourse->program_id,
      'academic_session_id' => $target->id,
      'slug' => $this->buildUniqueSlug($sourceCourse, $target),
      'name' => $sourceCourse->name.' — '.$target->name,
      'sort_order' => $sourceCourse->sort_order,
      'is_published' => $options->publishClonedContent && $sourceCourse->is_published,
    ]);
    $counts['courses']++;

    if ($options->coursesAndChapters) {
      foreach ($sourceCourse->courseModules as $module) {
        $newModule = CourseModule::query()->create([
          'course_id' => $newCourse->id,
          'name' => $module->name,
          'sort_order' => $module->sort_order,
        ]);
        $maps->modules[$module->id] = $newModule->id;
        $counts['modules']++;

        foreach ($module->chapters as $chapter) {
          $newChapter = Chapter::query()->create([
            'course_id' => $newCourse->id,
            'course_module_id' => $newModule->id,
            'instructor_user_id' => $chapter->instructor_user_id,
            'title' => $chapter->title,
            'sort_order' => $chapter->sort_order,
            'is_published' => $options->publishClonedContent && $chapter->is_published,
          ]);
          $maps->chapters[$chapter->id] = $newChapter->id;
          $counts['chapters']++;

          foreach ($chapter->contentBlocks as $block) {
            ContentBlock::query()->create([
              'chapter_id' => $newChapter->id,
              'type' => $block->type,
              'sort_order' => $block->sort_order,
              'title' => $block->title,
              'body' => $block->body,
              'media_asset_id' => $block->media_asset_id,
              'url' => $block->url,
              'metadata' => $block->metadata,
            ]);
            $counts['content_blocks']++;
          }
        }
      }
    }

    $assessmentIds = $this->collectAssessmentsToClone($sourceCourse, $options);

    foreach ($assessmentIds as $assessmentId) {
      $assessment = Assessment::query()
        ->with('questions.options')
        ->find($assessmentId);

      if ($assessment === null) {
        continue;
      }

      $newAssessment = Assessment::query()->create([
        'program_id' => $assessment->program_id,
        'course_id' => $newCourse->id,
        'chapter_id' => $maps->chapterId($assessment->chapter_id),
        'course_module_id' => $maps->moduleId($assessment->course_module_id),
        'title' => $assessment->title,
        'type' => $assessment->type,
        'is_module_exit_quiz' => $assessment->is_module_exit_quiz,
        'time_limit_seconds' => $assessment->time_limit_seconds,
        'max_attempts' => $assessment->max_attempts,
        'passing_score' => $assessment->passing_score,
        'required_questions' => $assessment->required_questions,
        'is_published' => $options->publishClonedContent && $assessment->is_published,
      ]);
      $maps->assessments[$assessment->id] = $newAssessment->id;
      $counts['assessments']++;

      foreach ($assessment->questions as $question) {
        $newQuestion = Question::query()->create([
          'assessment_id' => $newAssessment->id,
          'type' => $question->type,
          'stem' => $question->stem,
          'sort_order' => $question->sort_order,
          'points' => $question->points,
          'review_chapter_id' => $maps->chapterId($question->review_chapter_id),
          'metadata' => $question->metadata,
        ]);
        $counts['questions']++;

        foreach ($question->options as $option) {
          QuestionOption::query()->create([
            'question_id' => $newQuestion->id,
            'label' => $option->label,
            'is_correct' => $option->is_correct,
            'sort_order' => $option->sort_order,
          ]);
          $counts['question_options']++;
        }
      }
    }

    return new PedagogicalCloneResult($newCourse, $maps, $counts);
  }

  /**
   * Détermine le cours ECAP associé à une session (clone dédié ou cours historique).
   */
  public function resolveCourseForSession(AcademicSession $session): ?Course
  {
    $sessionCourse = $session->courses()->first();

    if ($sessionCourse !== null) {
      return $sessionCourse;
    }

    $enrollmentCourseId = Enrollment::query()
      ->where('academic_session_id', $session->id)
      ->whereNotNull('course_id')
      ->value('course_id');

    if ($enrollmentCourseId !== null) {
      return Course::query()->find($enrollmentCourseId);
    }

    $moduleId = SessionModuleSchedule::query()
      ->where('academic_session_id', $session->id)
      ->whereNotNull('course_module_id')
      ->value('course_module_id');

    if ($moduleId !== null) {
      return CourseModule::query()->find($moduleId)?->course;
    }

    return Course::query()
      ->where('program_id', $session->program_id)
      ->whereNull('academic_session_id')
      ->orderByDesc('is_published')
      ->orderBy('sort_order')
      ->first();
  }

  /**
   * @return array<int, int> Identifiants uniques d'évaluations à cloner
   */
  private function collectAssessmentsToClone(Course $sourceCourse, DuplicateEcapSessionOptions $options): array
  {
    $ids = [];

    foreach ($sourceCourse->assessments as $assessment) {
      if ($this->shouldCloneAssessment($assessment, $options)) {
        $ids[$assessment->id] = $assessment->id;
      }
    }

    return array_values($ids);
  }

  /**
   * Indique si une évaluation doit être recopiée selon les options choisies.
   */
  private function shouldCloneAssessment(Assessment $assessment, DuplicateEcapSessionOptions $options): bool
  {
    if ($assessment->is_module_exit_quiz) {
      return $options->quizzes;
    }

    return match ($assessment->type) {
      AssessmentType::Quiz, AssessmentType::Exam => $options->quizzes,
      AssessmentType::Tp => $options->tp,
      default => false,
    };
  }

  /**
   * Génère un slug unique pour le cours cloné.
   */
  private function buildUniqueSlug(Course $sourceCourse, AcademicSession $target): string
  {
    $base = Str::slug($sourceCourse->slug.'-gen-'.($target->generation_number ?? $target->id));
    $slug = $base;
    $suffix = 1;

    while (
      Course::query()
        ->where('program_id', $sourceCourse->program_id)
        ->where('slug', $slug)
        ->exists()
    ) {
      $slug = $base.'-'.$suffix;
      $suffix++;
    }

    return $slug;
  }
}
