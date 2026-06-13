<?php

namespace App\Services\Ecap;

/**
 * Options de duplication d'une session ECAP vers une autre.
 */
final class DuplicateEcapSessionOptions
{
  /**
   * @param  bool  $configuration  Recopie calendrier, périodes, vacations, groupes et acteurs
   * @param  bool  $coursesAndChapters  Clone cours, modules, chapitres et blocs de contenu
   * @param  bool  $quizzes  Clone quiz chapitre et quiz de fin de module
   * @param  bool  $tp  Clone travaux pratiques (TP)
   * @param  bool  $meditations  Clone modèles de cahier de méditation
   * @param  bool  $publishClonedContent  Publie le contenu cloné (sinon brouillon)
   */
  public function __construct(
    public bool $configuration = true,
    public bool $coursesAndChapters = true,
    public bool $quizzes = true,
    public bool $tp = true,
    public bool $meditations = true,
    public bool $publishClonedContent = false,
  ) {}

  /**
   * Indique si au moins une option pédagogique est activée.
   */
  public function duplicatesPedagogicalContent(): bool
  {
    return $this->coursesAndChapters || $this->quizzes || $this->tp;
  }

  /**
   * @param  array<string, mixed>  $data  État brut du formulaire Filament
   */
  public static function fromFormData(array $data): self
  {
    return new self(
      configuration: (bool) ($data['duplicate_configuration'] ?? true),
      coursesAndChapters: (bool) ($data['duplicate_courses_and_chapters'] ?? true),
      quizzes: (bool) ($data['duplicate_quizzes'] ?? true),
      tp: (bool) ($data['duplicate_tp'] ?? true),
      meditations: (bool) ($data['duplicate_meditations'] ?? true),
      publishClonedContent: (bool) ($data['publish_cloned_content'] ?? false),
    );
  }
}
