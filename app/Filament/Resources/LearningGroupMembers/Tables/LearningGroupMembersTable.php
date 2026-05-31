<?php

namespace App\Filament\Resources\LearningGroupMembers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Filament\Tables\Columns\UserTableColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Tableau des membres de groupes de vacation (admin).
 */
class LearningGroupMembersTable
{
  /**
   * Configure la table Filament.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('learningGroup.name')
          ->label('Groupe de vacation')
          ->searchable(),
        TextColumn::make('learningGroup.academicSession.name')
          ->label('Session ECAP')
          ->searchable(),
        UserTableColumn::make('user', 'Fidèle'),
        TextColumn::make('group_role')
          ->label('Rôle dans le groupe')
          ->formatStateUsing(fn (string $state): string => match ($state) {
            'membre' => 'Membre',
            'leader' => 'Responsable',
            'member' => 'Membre',
            default => $state,
          })
          ->searchable(),
        TextColumn::make('created_at')
          ->label('Ajouté le')
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
