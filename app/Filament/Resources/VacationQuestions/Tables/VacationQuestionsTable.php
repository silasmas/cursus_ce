<?php

namespace App\Filament\Resources\VacationQuestions\Tables;

use App\Enums\EcapVacationRole;
use App\Enums\VacationQuestionStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Filament\Tables\Columns\UserTableColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Table des questions vacation ECAP.
 */
class VacationQuestionsTable
{
  /**
   * Configure la table Filament.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('subject')
          ->label('Objet')
          ->searchable()
          ->limit(50),
        UserTableColumn::make('askedBy', 'Auteur'),
        TextColumn::make('courseModule.name')
          ->label('Module')
          ->toggleable(),
        TextColumn::make('addressee')
          ->label('Réponse attendue de')
          ->state(function ($record): string {
            if ($record->is_addressed_to_all_teachers) {
              return '@tous';
            }

            return $record->addressedToUser?->name ?? '—';
          }),
        TextColumn::make('status')
          ->label('Statut')
          ->formatStateUsing(fn (VacationQuestionStatus|string $state): string => $state instanceof VacationQuestionStatus
            ? $state->label()
            : VacationQuestionStatus::from($state)->label())
          ->badge()
          ->color(fn (VacationQuestionStatus|string $state): string => match ($state instanceof VacationQuestionStatus ? $state : VacationQuestionStatus::from($state)) {
            VacationQuestionStatus::Pending => 'warning',
            VacationQuestionStatus::Answered => 'success',
            VacationQuestionStatus::Closed => 'gray',
          }),
        TextColumn::make('academicSession.name')
          ->label('Session')
          ->toggleable(),
        TextColumn::make('created_at')
          ->label('Reçue le')
          ->dateTime('d/m/Y H:i')
          ->sortable(),
      ])
      ->defaultSort('created_at', 'desc')
      ->filters([
        SelectFilter::make('status')
          ->label('Statut')
          ->options(VacationQuestionStatus::options()),
        SelectFilter::make('addressed_to_role')
          ->label('Destinataire')
          ->options(EcapVacationRole::options()),
      ])
      ->recordActions([
        EditAction::make(),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          DeleteBulkAction::make(),
        ]),
      ]);
  }
}
