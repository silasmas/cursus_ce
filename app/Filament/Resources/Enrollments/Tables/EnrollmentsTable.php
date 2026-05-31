<?php

namespace App\Filament\Resources\Enrollments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Filament\Tables\Columns\UserTableColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

/**
 * Table des inscriptions avec switch mode en ligne ECAP.
 */
class EnrollmentsTable
{
  /**
   * Configure la table Filament.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->columns([
        UserTableColumn::make('user', 'Fidèle'),
        TextColumn::make('program.name')
          ->label('Cursus')
          ->searchable(),
        TextColumn::make('course.name')
          ->label('Cours')
          ->searchable(),
        TextColumn::make('academicSession.name')
          ->label('Session ECAP')
          ->searchable(),
        ToggleColumn::make('is_online')
          ->label('ECAP en ligne')
          ->disabled(fn ($record): bool => $record->program?->slug !== 'ecap')
          ->afterStateUpdated(function ($record, bool $state): void {
            $record->update([
              'is_online' => $state,
              'online_mode_updated_at' => now(),
              'online_mode_updated_by_user_id' => Auth::id(),
            ]);
          }),
        TextColumn::make('status')
          ->label('Statut')
          ->badge()
          ->formatStateUsing(fn (string $state): string => match ($state) {
            'active' => 'Actif',
            'completed' => 'Terminé',
            'suspended' => 'Suspendu',
            'cancelled' => 'Annulé',
            default => $state,
          })
          ->color(fn (string $state): string => match ($state) {
            'active' => 'success',
            'completed' => 'info',
            'suspended' => 'warning',
            'cancelled' => 'danger',
            default => 'gray',
          })
          ->searchable(),
        TextColumn::make('enrolled_at')
          ->label('Inscrit le')
          ->dateTime('d/m/Y H:i')
          ->sortable(),
        TextColumn::make('completed_at')
          ->label('Terminé le')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->toggleable(),
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
        TernaryFilter::make('is_online')
          ->label('Mode en ligne ECAP'),
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
