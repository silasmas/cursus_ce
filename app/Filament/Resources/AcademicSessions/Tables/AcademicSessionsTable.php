<?php

namespace App\Filament\Resources\AcademicSessions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Liste des sessions ECAP.
 */
class AcademicSessionsTable
{
  /**
   * Configure la table Filament.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('name')
          ->label('Session')
          ->searchable(),
        TextColumn::make('code')
          ->label('Code')
          ->searchable(),
        TextColumn::make('generation_number')
          ->label('N°')
          ->sortable(),
        TextColumn::make('starts_on')
          ->label('Début')
          ->date('d/m/Y')
          ->sortable(),
        TextColumn::make('ends_on')
          ->label('Fin')
          ->date('d/m/Y')
          ->sortable(),
        TextColumn::make('registration_opens_at')
          ->label('Ouverture inscriptions')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->toggleable(),
        TextColumn::make('registration_closes_at')
          ->label('Clôture inscriptions')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->toggleable(),
        IconColumn::make('is_registration_open')
          ->label('Inscriptions ouvertes')
          ->boolean()
          ->state(fn ($record): bool => $record->isRegistrationOpen()),
        TextColumn::make('module_schedules_count')
          ->label('Entrées calendrier')
          ->counts('moduleSchedules')
          ->badge()
          ->color('warning')
          ->tooltip('Nombre de modules/activités planifiés — cliquez Modifier pour le calendrier'),
        IconColumn::make('is_active')
          ->label('Active')
          ->boolean(),
        TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        //
      ])
      ->recordActions([
        EditAction::make()->label('Modifier / calendrier'),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          DeleteBulkAction::make(),
        ]),
      ]);
  }
}
