<?php

namespace App\Filament\Resources\Assessments\Tables;

use App\Enums\AssessmentType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Tableau des tests et évaluations (admin).
 */
class AssessmentsTable
{
  /**
   * Configure la table Filament.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('program.name')
          ->label('Cursus')
          ->searchable(),
        TextColumn::make('course.name')
          ->label('Cours')
          ->searchable(),
        TextColumn::make('chapter.title')
          ->label('Chapitre')
          ->searchable(),
        TextColumn::make('title')
          ->label('Titre')
          ->searchable(),
        TextColumn::make('type')
          ->label('Type')
          ->formatStateUsing(fn (string $state): string => match ($state) {
            AssessmentType::Quiz->value => 'Quiz',
            AssessmentType::Tp->value => 'Travail pratique',
            AssessmentType::Exam->value => 'Examen',
            default => $state,
          })
          ->searchable(),
        TextColumn::make('time_limit_seconds')
          ->label('Limite (secondes)')
          ->numeric()
          ->sortable(),
        TextColumn::make('max_attempts')
          ->label('Tentatives max.')
          ->numeric()
          ->sortable(),
        TextColumn::make('passing_score')
          ->label('Seuil (%)')
          ->numeric()
          ->sortable(),
        IconColumn::make('is_published')
          ->label('Publié')
          ->boolean(),
        TextColumn::make('created_at')
          ->label('Créé le')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')
          ->label('Modifié le')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        //
      ])
      ->recordActions([
        EditAction::make()->label('Modifier'),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          DeleteBulkAction::make()->label('Supprimer la sélection'),
        ]),
      ]);
  }
}
