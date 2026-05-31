<?php

namespace App\Filament\Resources\ProgramAccesses\Tables;

use App\Models\ProgramAccess;
use App\Services\ProgramAccess\ProgramAccessStateService;
use App\Filament\Tables\Columns\UserTableColumn;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Table des accès cursus avec switches booléens.
 */
class ProgramAccessesTable
{
  /**
   * Configure la table Filament.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->defaultSort('updated_at', 'desc')
      ->columns([
        UserTableColumn::make('user'),
        TextColumn::make('program.name')
          ->label('Cursus')
          ->searchable(),
        TextColumn::make('status_label')
          ->label('Statut')
          ->badge()
          ->state(fn (ProgramAccess $record): string => app(ProgramAccessStateService::class)->label($record))
          ->color(fn (ProgramAccess $record): string => match (true) {
            $record->is_waived, $record->is_completed => 'success',
            $record->needs_admin_validation => 'warning',
            $record->is_open => 'info',
            default => 'gray',
          }),
        ToggleColumn::make('is_pending')
          ->label('En attente')
          ->tooltip('Cursus verrouillé dans Mon espace — en attente d\'ouverture ou de traitement.')
          ->afterStateUpdated(function (ProgramAccess $record, bool $state): void {
            if ($state) {
              app(ProgramAccessStateService::class)->setPending($record);
            }
          }),
        ToggleColumn::make('is_open')
          ->label('Ouvert')
          ->tooltip('Accès actif : le fidèle peut suivre le parcours en ligne.')
          ->afterStateUpdated(function (ProgramAccess $record, bool $state): void {
            if ($state) {
              app(ProgramAccessStateService::class)->setOpen($record, 'admin_validated');
            } else {
              app(ProgramAccessStateService::class)->setPending($record);
            }
          }),
        ToggleColumn::make('needs_admin_validation')
          ->label('À valider')
          ->tooltip('Le fidèle a déclaré avoir déjà suivi ce cursus — validation admin requise.')
          ->afterStateUpdated(function (ProgramAccess $record, bool $state): void {
            if ($state) {
              app(ProgramAccessStateService::class)->setNeedsAdminValidation($record, 'admin_validated');
            } else {
              app(ProgramAccessStateService::class)->setPending($record);
            }
          }),
        ToggleColumn::make('is_completed')
          ->label('Acquis')
          ->tooltip('Cursus terminé ou validé par l\'administration.')
          ->afterStateUpdated(function (ProgramAccess $record, bool $state): void {
            if ($state) {
              app(ProgramAccessStateService::class)->setCompleted($record);
            } else {
              app(ProgramAccessStateService::class)->setPending($record);
            }
          }),
        ToggleColumn::make('is_waived')
          ->label('Dispensé')
          ->tooltip('Dispense administrative — le fidèle n\'a pas à refaire ce cursus.')
          ->afterStateUpdated(function (ProgramAccess $record, bool $state): void {
            if ($state) {
              app(ProgramAccessStateService::class)->setWaived($record);
            } else {
              app(ProgramAccessStateService::class)->setPending($record);
            }
          }),
        TextColumn::make('source')
          ->label('Source')
          ->badge()
          ->toggleable(isToggledHiddenByDefault: true),
        UserTableColumn::make('validatedBy', 'Validé par')
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('validated_at')
          ->label('Validé le')
          ->dateTime('d/m/Y H:i')
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')
          ->label('Modifié')
          ->dateTime('d/m/Y H:i')
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        TernaryFilter::make('is_open')
          ->label('Ouvert'),
        TernaryFilter::make('needs_admin_validation')
          ->label('À valider'),
        TernaryFilter::make('is_completed')
          ->label('Acquis'),
        SelectFilter::make('source')
          ->label('Source')
          ->options([
            'registration' => 'Inscription',
            'session_enrollment' => 'Inscription session',
            'admin_validated' => 'Validation admin',
          ]),
      ])
      ->recordActions([
        EditAction::make()->label('Modifier'),
        DeleteAction::make()->label('Supprimer'),
      ]);
  }
}
