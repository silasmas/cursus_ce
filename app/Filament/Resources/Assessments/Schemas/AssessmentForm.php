<?php

namespace App\Filament\Resources\Assessments\Schemas;

use App\Enums\AssessmentType;
use App\Services\Student\ModuleExitQuizService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formulaire d'une évaluation (quiz, TP, examen, quiz M5).
 */
class AssessmentForm
{
  /**
   * Configure le schéma Filament.
   */
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        Section::make('Informations générales')
          ->description('Quiz de chapitre, quiz de fin de module ECAP, TP ou examen de session.')
          ->schema([
            Select::make('program_id')
              ->label('Cursus')
              ->relationship('program', 'name')
              ->searchable(),
            Select::make('course_id')
              ->label('Cours')
              ->relationship('course', 'name')
              ->searchable(),
            Select::make('chapter_id')
              ->label('Chapitre (quiz de chapitre)')
              ->relationship('chapter', 'title')
              ->searchable(),
            Select::make('course_module_id')
              ->label('Module (quiz de fin)')
              ->relationship('courseModule', 'name')
              ->searchable()
              ->visible(fn ($get) => (bool) $get('is_module_exit_quiz')),
            TextInput::make('title')
              ->label('Titre')
              ->required(),
            Select::make('type')
              ->label('Type')
              ->options(collect(AssessmentType::cases())->mapWithKeys(
                fn (AssessmentType $type) => [$type->value => match ($type) {
                  AssessmentType::Quiz => 'Quiz',
                  AssessmentType::Tp => 'Travail pratique',
                  AssessmentType::Exam => 'Examen',
                }],
              )->all())
              ->required()
              ->native(false),
            Toggle::make('is_module_exit_quiz')
              ->label('Quiz de fin de module ECAP')
              ->helperText('Obligatoire pour débloquer le module suivant. Configurez le nombre de questions et le seuil ci-dessous.'),
            TextInput::make('required_questions')
              ->label('Nombre de questions')
              ->numeric()
              ->minValue(1)
              ->maxValue(100)
              ->default(ModuleExitQuizService::DEFAULT_REQUIRED_QUESTIONS)
              ->helperText('Nombre de questions attendues pour ce quiz (défaut M5 : 5).')
              ->visible(fn ($get): bool => in_array($get('type'), [AssessmentType::Quiz->value, null], true)),
            TextInput::make('time_limit_seconds')
              ->label('Limite de temps (secondes)')
              ->numeric(),
            TextInput::make('max_attempts')
              ->label('Tentatives max.')
              ->required()
              ->numeric()
              ->default(3),
            TextInput::make('passing_score')
              ->label('Seuil de réussite (%)')
              ->numeric()
              ->default(ModuleExitQuizService::PASSING_SCORE),
            Toggle::make('is_published')
              ->label('Publié')
              ->required(),
          ])
          ->columns(2),
      ]);
  }
}
