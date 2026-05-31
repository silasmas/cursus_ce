<?php

namespace App\Filament\Resources\EcapStaffAssignments\Tables;

use App\Enums\EcapVacationRole;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use App\Filament\Tables\Columns\UserTableColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Table des acteurs de vacation ECAP.
 */
class EcapStaffAssignmentsTable
{
  /**
   * Configure la table Filament.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('academicSession.name')
          ->label('Session ECAP')
          ->searchable()
          ->sortable(),
        TextColumn::make('sessionVacation.name')
          ->label('Vacation')
          ->placeholder('Toute la session')
          ->searchable(),
        TextColumn::make('courseModule.name')
          ->label('Module')
          ->placeholder('—')
          ->toggleable(),
        UserTableColumn::make('user'),
        TextColumn::make('role')
          ->label('Rôle')
          ->formatStateUsing(fn (EcapVacationRole|string $state): string => $state instanceof EcapVacationRole
            ? $state->label()
            : EcapVacationRole::from($state)->label())
          ->badge(),
        IconColumn::make('is_active')
          ->label('Actif')
          ->boolean(),
        TextColumn::make('updated_at')
          ->label('Modifié le')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->defaultSort('academicSession.name')
      ->filters([
        SelectFilter::make('role')
          ->label('Rôle')
          ->options(EcapVacationRole::options()),
        TernaryFilter::make('is_active')
          ->label('Actif'),
      ])
      ->recordActions([
        EditAction::make(),
        DeleteAction::make()
          ->successNotification(
            Notification::make()
              ->title('Affectation supprimée')
              ->success()
              ->body('Le rôle ECAP a été retiré pour cet utilisateur.'),
          ),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          DeleteBulkAction::make()
            ->successNotification(
              Notification::make()
                ->title('Affectations supprimées')
                ->success()
                ->body('Les rôles ECAP sélectionnés ont été retirés.'),
            ),
        ]),
      ]);
  }
}
