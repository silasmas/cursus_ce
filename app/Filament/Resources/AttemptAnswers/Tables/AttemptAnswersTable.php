<?php

namespace App\Filament\Resources\AttemptAnswers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Tableau des réponses aux tests (admin).
 */
class AttemptAnswersTable
{
  /**
   * Configure la table Filament.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('assessmentAttempt.id')
          ->label('Passation n°')
          ->searchable(),
        TextColumn::make('question.id')
          ->label('Question n°')
          ->searchable(),
        TextColumn::make('questionOption.id')
          ->label('Proposition n°')
          ->searchable(),
        TextColumn::make('file_path')
          ->label('Fichier joint')
          ->searchable(),
        TextColumn::make('points_awarded')
          ->label('Points attribués')
          ->numeric()
          ->sortable(),
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
