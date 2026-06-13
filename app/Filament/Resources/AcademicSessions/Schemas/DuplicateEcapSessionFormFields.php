<?php

namespace App\Filament\Resources\AcademicSessions\Schemas;

use App\Models\AcademicSession;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;

/**
 * Champs Filament partagés pour la duplication d'une session ECAP.
 */
class DuplicateEcapSessionFormFields
{
  /**
   * Liste des sessions ECAP disponibles comme modèle.
   *
   * @return array<int, string>
   */
  public static function sessionOptions(?AcademicSession $exclude = null): array
  {
    return AcademicSession::query()
      ->whereHas('program', fn ($query) => $query->where('slug', 'ecap'))
      ->when($exclude !== null, fn ($query) => $query->whereKeyNot($exclude->id))
      ->orderByDesc('generation_number')
      ->pluck('name', 'id')
      ->all();
  }

  /**
   * Sélecteur de session modèle.
   */
  public static function sourceSessionSelect(
    string $name = 'duplicate_from_session_id',
    ?AcademicSession $exclude = null,
    bool $dehydrated = false,
  ): Select {
    return Select::make($name)
      ->label('Session modèle')
      ->options(fn (): array => self::sessionOptions($exclude))
      ->searchable()
      ->preload()
      ->placeholder('Choisir une session')
      ->live()
      ->helperText('Recopie le contenu et/ou la configuration depuis une génération précédente.')
      ->dehydrated($dehydrated);
  }

  /**
   * Options détaillées de duplication (visibles si une session modèle est choisie).
   *
   * @return array<int, Toggle|Checkbox>
   */
  public static function duplicationOptions(string $sourceField = 'duplicate_from_session_id', bool $dehydrated = false): array
  {
    $visible = fn (Get $get): bool => filled($get($sourceField));

    return [
      Toggle::make('duplicate_configuration')
        ->label('Configuration de session')
        ->helperText('Calendrier, périodes pédagogiques, vacations, groupes et affectations acteurs.')
        ->default(true)
        ->visible($visible)
        ->dehydrated($dehydrated),
      Toggle::make('duplicate_courses_and_chapters')
        ->label('Cours, modules et chapitres')
        ->helperText('Structure pédagogique et blocs de contenu (texte, vidéo, etc.).')
        ->default(true)
        ->visible($visible)
        ->dehydrated($dehydrated),
      Toggle::make('duplicate_quizzes')
        ->label('Quiz ECAP')
        ->helperText('Quiz de chapitre et quiz de fin de module.')
        ->default(true)
        ->visible($visible)
        ->dehydrated($dehydrated),
      Toggle::make('duplicate_tp')
        ->label('Travaux pratiques (TP)')
        ->default(true)
        ->visible($visible)
        ->dehydrated($dehydrated),
      Toggle::make('duplicate_meditations')
        ->label('Cahiers de méditation')
        ->default(true)
        ->visible($visible)
        ->dehydrated($dehydrated),
      Checkbox::make('publish_cloned_content')
        ->label('Publier immédiatement le contenu cloné')
        ->helperText('Décoché par défaut : le contenu reste en brouillon pour révision avant mise en ligne.')
        ->default(false)
        ->visible($visible)
        ->dehydrated($dehydrated),
    ];
  }
}
