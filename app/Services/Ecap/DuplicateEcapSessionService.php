<?php

namespace App\Services\Ecap;

use App\Models\AcademicSession;

/**
 * Orchestre la duplication complète d'une session ECAP (pédagogie + configuration).
 */
class DuplicateEcapSessionService
{
  /**
   * @param  DuplicateEcapPedagogicalContentService  $pedagogicalService  Clone cours, quiz et TP
   * @param  DuplicateEcapSessionConfigurationService  $configurationService  Clone calendrier et structure session
   */
  public function __construct(
    private readonly DuplicateEcapPedagogicalContentService $pedagogicalService,
    private readonly DuplicateEcapSessionConfigurationService $configurationService,
  ) {}

  /**
   * Duplique une session ECAP source vers une session cible selon les options.
   *
   * @return array{
   *   pedagogical: array<string, int>|null,
   *   configuration: array<string, int>|null,
   *   course_id: int|null
   * }
   */
  public function duplicate(
    AcademicSession $target,
    AcademicSession $source,
    DuplicateEcapSessionOptions $options,
  ): array {
    $options = $this->normalizeOptions($options);

    $pedagogicalCounts = null;
    $maps = null;
    $courseId = null;

    if ($options->duplicatesPedagogicalContent()) {
      $result = $this->pedagogicalService->duplicateForSession($target, $source, $options);
      $pedagogicalCounts = $result->counts;
      $maps = $result->maps;
      $courseId = $result->course->id;
    }

    $configurationCounts = null;

    if ($options->configuration) {
      $configurationCounts = $this->configurationService->duplicateFromSession(
        $target,
        $source,
        $maps,
        $options,
      );
    }

    return [
      'pedagogical' => $pedagogicalCounts,
      'configuration' => $configurationCounts,
      'course_id' => $courseId,
    ];
  }

  /**
   * Assure la cohérence des options (quiz/TP nécessitent la structure cours).
   */
  private function normalizeOptions(DuplicateEcapSessionOptions $options): DuplicateEcapSessionOptions
  {
    if (($options->quizzes || $options->tp) && ! $options->coursesAndChapters) {
      return new DuplicateEcapSessionOptions(
        configuration: $options->configuration,
        coursesAndChapters: true,
        quizzes: $options->quizzes,
        tp: $options->tp,
        meditations: $options->meditations,
        publishClonedContent: $options->publishClonedContent,
      );
    }

    return $options;
  }

  /**
   * Construit le message de notification après duplication.
   *
   * @param  array{
   *   pedagogical: array<string, int>|null,
   *   configuration: array<string, int>|null,
   *   course_id: int|null
   * }  $result
   */
  public function buildSummaryMessage(AcademicSession $source, array $result): string
  {
    $parts = ['Depuis « '.$source->name.' » :'];

    if ($result['pedagogical'] !== null) {
      $p = $result['pedagogical'];
      $parts[] = ($p['modules'] ?? 0).' module(s), '
        .($p['chapters'] ?? 0).' chapitre(s), '
        .($p['content_blocks'] ?? 0).' bloc(s), '
        .($p['assessments'] ?? 0).' évaluation(s) (quiz/TP).';
    }

    if ($result['configuration'] !== null) {
      $c = $result['configuration'];
      $parts[] = ($c['periods'] ?? 0).' période(s), '
        .($c['schedules'] ?? 0).' entrée(s) calendrier, '
        .($c['staff'] ?? 0).' affectation(s) acteur, '
        .($c['vacations'] ?? 0).' vacation(s), '
        .($c['groups'] ?? 0).' groupe(s).';

      if (($c['meditations'] ?? 0) > 0) {
        $parts[] = ($c['meditations'] ?? 0).' modèle(s) de méditation.';
      }
    }

    if ($result['course_id'] !== null) {
      $parts[] = 'Le contenu cloné est modifiable dans le cursus ECAP (cours lié à cette session).';
    }

    return implode(' ', $parts);
  }
}
