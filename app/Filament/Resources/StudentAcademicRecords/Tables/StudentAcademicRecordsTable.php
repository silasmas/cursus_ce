<?php

namespace App\Filament\Resources\StudentAcademicRecords\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Filament\Tables\Columns\UserTableColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Tableau des dossiers académiques ECAP (admin).
 */
class StudentAcademicRecordsTable
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
          ->searchable(),
        UserTableColumn::make('user', 'Fidèle'),
        TextColumn::make('final_average')
          ->label('Moyenne finale')
          ->numeric(decimalPlaces: 2)
          ->sortable()
          ->placeholder('—'),
        TextColumn::make('validated_at')
          ->label('Validé le')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->placeholder('—'),
        TextColumn::make('created_at')
          ->label('Ouvert le')
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
        DeleteAction::make()->label('Supprimer'),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          DeleteBulkAction::make()->label('Supprimer la sélection'),
        ]),
      ]);
  }
}
