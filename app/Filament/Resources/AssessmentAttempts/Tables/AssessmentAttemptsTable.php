<?php

namespace App\Filament\Resources\AssessmentAttempts\Tables;

use App\Enums\AttemptStatus;
use App\Filament\Resources\AssessmentAttempts\AssessmentAttemptResource;
use App\Services\Student\AssessmentAttemptGradingService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use App\Filament\Tables\Columns\UserTableColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Tableau des passations de tests (admin).
 */
class AssessmentAttemptsTable
{
  /**
   * Configure la table Filament.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'gradedBy', 'gradingLockedBy', 'assessment']))
      ->columns([
        TextColumn::make('assessment.title')
          ->label('Test / évaluation')
          ->searchable(),
        UserTableColumn::make('user', 'Fidèle'),
        TextColumn::make('enrollment.id')
          ->label('Inscription n°')
          ->searchable(),
        TextColumn::make('started_at')
          ->label('Démarré le')
          ->dateTime('d/m/Y H:i')
          ->sortable(),
        TextColumn::make('submitted_at')
          ->label('Soumis le')
          ->dateTime('d/m/Y H:i')
          ->sortable(),
        TextColumn::make('score')
          ->label('Score (%)')
          ->numeric()
          ->sortable(),
        IconColumn::make('passed')
          ->label('Réussi')
          ->boolean(),
        TextColumn::make('status')
          ->label('Statut')
          ->badge()
          ->formatStateUsing(fn (string $state): string => match ($state) {
            AttemptStatus::InProgress->value => 'En cours',
            AttemptStatus::Submitted->value => 'Soumis',
            AttemptStatus::Graded->value => 'Corrigé',
            default => $state,
          })
          ->color(fn (string $state): string => match ($state) {
            AttemptStatus::InProgress->value => 'gray',
            AttemptStatus::Submitted->value => 'warning',
            AttemptStatus::Graded->value => 'success',
            default => 'gray',
          })
          ->searchable(),
        UserTableColumn::make('gradedBy', 'Corrigé par'),
        UserTableColumn::make('gradingLockedBy', 'En correction par')
          ->toggleable(isToggledHiddenByDefault: true),
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
        Action::make('grade')
          ->label('Corriger')
          ->icon('heroicon-o-clipboard-document-check')
          ->color('warning')
          ->url(fn ($record) => AssessmentAttemptResource::getUrl('grade', ['record' => $record]))
          ->visible(fn ($record) => $record->status === AttemptStatus::Submitted->value
            && app(AssessmentAttemptGradingService::class)->hasUngradedWrittenAnswers($record)),
        EditAction::make()->label('Modifier'),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          DeleteBulkAction::make()->label('Supprimer la sélection'),
        ]),
      ]);
  }
}
