<?php

namespace App\Filament\Resources\CourseModules\RelationManagers;

use App\Enums\AssessmentType;
use App\Filament\Concerns\HasRelationManagerHelp;
use App\Filament\Resources\Assessments\AssessmentResource;
use App\Models\Assessment;
use App\Models\CourseModule;
use App\Services\Student\ModuleExitQuizService;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Quiz de fin de module ECAP rattaché à un module de cours.
 */
class ModuleExitQuizRelationManager extends RelationManager
{
  use HasRelationManagerHelp;

  protected static string $relationship = 'moduleExitQuiz';

  protected static ?string $title = 'Quiz de fin de module';

  protected static ?string $modelLabel = 'quiz de fin de module';

  /**
   * Clé d'aide contextuelle.
   */
  protected static function helpKey(): string
  {
    return 'module_exit_quiz';
  }

  /**
   * Formulaire du quiz de fin de module.
   */
  public function form(Schema $schema): Schema
  {
    /** @var CourseModule $module */
    $module = $this->getOwnerRecord();

    return $schema
      ->components([
        Placeholder::make('exit_quiz_rules')
          ->label('Règles du quiz')
          ->content('Questions à choix multiples avec chapitre de révision par question · Seuil configurable · Le module suivant reste verrouillé tant que le quiz n\'est pas réussi.')
          ->columnSpanFull(),
        TextInput::make('title')
          ->label('Titre')
          ->default(fn (): string => 'Quiz fin de module — '.$module->name)
          ->required()
          ->maxLength(200),
        Hidden::make('type')
          ->default(AssessmentType::Quiz->value),
        Hidden::make('is_module_exit_quiz')
          ->default(true),
        Hidden::make('course_module_id')
          ->default(fn (): int => $module->id),
        Hidden::make('program_id')
          ->default(fn (): ?int => $module->course?->program_id),
        Hidden::make('course_id')
          ->default(fn (): ?int => $module->course_id),
        TextInput::make('required_questions')
          ->label('Nombre de questions')
          ->numeric()
          ->default(ModuleExitQuizService::DEFAULT_REQUIRED_QUESTIONS)
          ->minValue(1)
          ->maxValue(100)
          ->required(),
        TextInput::make('passing_score')
          ->label('Seuil de réussite (%)')
          ->numeric()
          ->default(ModuleExitQuizService::PASSING_SCORE)
          ->minValue(1)
          ->maxValue(100)
          ->required(),
        TextInput::make('max_attempts')
          ->label('Tentatives max.')
          ->numeric()
          ->default(3)
          ->minValue(1)
          ->required(),
        Toggle::make('is_published')
          ->label('Publié')
          ->default(false)
          ->helperText('Ne publiez que lorsque toutes les questions sont configurées et que la somme des points vaut 100.'),
      ]);
  }

  /**
   * Affichage du quiz existant.
   */
  public function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('title')
          ->label('Quiz'),
        TextColumn::make('questions_count')
          ->label('Questions')
          ->counts('questions')
          ->formatStateUsing(function ($state, Assessment $record): string {
            $required = app(ModuleExitQuizService::class)->requiredQuestionsFor($record);

            return $state.'/'.$required;
          }),
        TextColumn::make('passing_score')
          ->label('Seuil')
          ->suffix('%'),
        IconColumn::make('is_published')
          ->label('Publié')
          ->boolean(),
      ])
      ->headerActions([
        CreateAction::make()
          ->label('Créer le quiz de fin de module')
          ->visible(fn (): bool => $this->getOwnerRecord()->moduleExitQuiz === null)
          ->mutateFormDataUsing(function (array $data): array {
            /** @var CourseModule $module */
            $module = $this->getOwnerRecord();

            $data['course_module_id'] = $module->id;
            $data['program_id'] = $module->course?->program_id;
            $data['course_id'] = $module->course_id;
            $data['is_module_exit_quiz'] = true;
            $data['type'] = AssessmentType::Quiz->value;
            $data['passing_score'] = $data['passing_score'] ?? ModuleExitQuizService::PASSING_SCORE;
            $data['required_questions'] = $data['required_questions'] ?? ModuleExitQuizService::DEFAULT_REQUIRED_QUESTIONS;

            return $data;
          })
          ->successRedirectUrl(fn (Assessment $record): string => AssessmentResource::getUrl('edit', ['record' => $record])),
      ])
      ->recordActions([
        EditAction::make()
          ->label('Paramètres')
          ->url(fn (Assessment $record): string => AssessmentResource::getUrl('edit', ['record' => $record])),
        DeleteAction::make()->label('Supprimer'),
      ]);
  }
}
