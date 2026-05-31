<?php

namespace App\Filament\Resources\Assessments\RelationManagers;

use App\Enums\QuestionType;
use App\Filament\Concerns\HasRelationManagerHelp;
use App\Models\Assessment;
use App\Models\Chapter;
use App\Models\Question;
use App\Services\Student\ModuleExitQuizService;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Questions d'une évaluation (dont quiz M5 avec chapitre de révision).
 */
class QuestionsRelationManager extends RelationManager
{
  use HasRelationManagerHelp;

  private const MAX_POINTS_TOTAL = 100;

  protected static string $relationship = 'questions';

  protected static ?string $title = 'Questions';

  protected static ?string $modelLabel = 'question';

  /**
   * Clé d'aide contextuelle.
   */
  protected static function helpKey(): string
  {
    return 'questions';
  }

  /**
   * Formulaire d'une question.
   */
  public function form(Schema $schema): Schema
  {
    $assessment = $this->getOwnerRecord();
    $moduleId = $assessment->course_module_id;

    return $schema
      ->components([
        Select::make('type')
          ->label('Type')
          ->options([
            QuestionType::Mcq->value => 'QCM',
            QuestionType::Written->value => 'Réponse rédigée',
          ])
          ->default(QuestionType::Mcq->value)
          ->required()
          ->native(false),
        Textarea::make('stem')
          ->label('Énoncé')
          ->required()
          ->columnSpanFull(),
        Select::make('review_chapter_id')
          ->label('Chapitre de révision')
          ->options(fn (): array => $this->reviewChapterOptions($moduleId, $assessment))
          ->searchable()
          ->helperText('En cas de mauvaise réponse, le fidèle est renvoyé vers ce chapitre.')
          ->visible(fn (): bool => (bool) $assessment->is_module_exit_quiz || $assessment->chapter_id !== null),
        TextInput::make('sort_order')
          ->label('Ordre')
          ->required()
          ->numeric()
          ->default(0),
        TextInput::make('points')
          ->label('Points')
          ->required()
          ->numeric()
          ->default(1)
          ->helperText('La somme des points de toutes les questions ne doit pas dépasser '.self::MAX_POINTS_TOTAL.'.'),
        Repeater::make('options')
          ->label('Réponses QCM')
          ->relationship()
          ->schema([
            TextInput::make('label')
              ->label('Réponse')
              ->required(),
            Toggle::make('is_correct')
              ->label('Correcte'),
            TextInput::make('sort_order')
              ->label('Ordre')
              ->numeric()
              ->default(0),
          ])
          ->visible(fn ($get): bool => $get('type') === QuestionType::Mcq->value)
          ->minItems(2)
          ->defaultItems(2)
          ->columnSpanFull(),
      ]);
  }

  /**
   * Liste des questions.
   */
  public function table(Table $table): Table
  {
    $assessment = $this->getOwnerRecord();
    $quota = $this->questionQuota($assessment);

    return $table
      ->defaultSort('sort_order')
      ->columns([
        TextColumn::make('sort_order')
          ->label('#')
          ->sortable(),
        TextColumn::make('stem')
          ->label('Énoncé')
          ->limit(60),
        TextColumn::make('reviewChapter.title')
          ->label('Révision')
          ->placeholder('—')
          ->visible((bool) $assessment->is_module_exit_quiz),
        TextColumn::make('points')
          ->label('Pts'),
      ])
      ->headerActions([
        CreateAction::make()
          ->label('Ajouter une question')
          ->visible(fn (): bool => $this->canAddQuestion())
          ->before(function (array $data, CreateAction $action): void {
            $this->assertQuestionConstraints(null, (float) ($data['points'] ?? 0), $action);
          }),
      ])
      ->recordActions([
        EditAction::make()
          ->before(function (Question $record, array $data, EditAction $action): void {
            $this->assertQuestionConstraints($record, (float) ($data['points'] ?? 0), $action);
          }),
        DeleteAction::make(),
      ]);
  }

  /**
   * Nombre maximal de questions autorisé pour ce quiz.
   */
  private function questionQuota(Assessment $assessment): ?int
  {
    if ($assessment->questionQuota() !== null) {
      return $assessment->questionQuota();
    }

    if ($assessment->is_module_exit_quiz) {
      return app(ModuleExitQuizService::class)->requiredQuestionsFor($assessment);
    }

    return null;
  }

  /**
   * Indique si une nouvelle question peut être ajoutée.
   */
  private function canAddQuestion(): bool
  {
    $assessment = $this->getOwnerRecord();
    $quota = $this->questionQuota($assessment);

    if ($quota === null) {
      return true;
    }

    return $assessment->questions()->count() < $quota;
  }

  /**
   * Somme actuelle des points des questions du quiz.
   */
  private function questionPointsTotal(): float
  {
    return (float) $this->getOwnerRecord()->questions()->sum('points');
  }

  /**
   * Vérifie quota et plafond de points avant enregistrement.
   */
  private function assertQuestionConstraints(?Question $existing, float $newPoints, CreateAction|EditAction $action): void
  {
    $assessment = $this->getOwnerRecord();

    if ($existing === null && ! $this->canAddQuestion()) {
      $quota = $this->questionQuota($assessment);
      Notification::make()
        ->danger()
        ->title('Quota de questions atteint')
        ->body("Ce quiz accepte au maximum {$quota} question(s).")
        ->send();
      $action->halt();
    }

    $total = $this->questionPointsTotal();

    if ($existing !== null) {
      $total -= (float) $existing->points;
    }

    if (($total + $newPoints) > self::MAX_POINTS_TOTAL) {
      Notification::make()
        ->danger()
        ->title('Plafond de points dépassé')
        ->body('La somme des points de toutes les questions ne peut pas dépasser '.self::MAX_POINTS_TOTAL.'.')
        ->send();
      $action->halt();
    }
  }

  /**
   * Chapitres disponibles pour le lien de révision.
   *
   * @return array<int, string>
   */
  private function reviewChapterOptions(?int $moduleId, Assessment $assessment): array
  {
    if ($moduleId !== null) {
      return Chapter::query()
        ->where('course_module_id', $moduleId)
        ->orderBy('sort_order')
        ->pluck('title', 'id')
        ->all();
    }

    if ($assessment->chapter_id !== null) {
      return Chapter::query()
        ->where('id', $assessment->chapter_id)
        ->pluck('title', 'id')
        ->all();
    }

    return [];
  }
}
