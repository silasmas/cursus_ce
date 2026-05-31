<?php

namespace App\Filament\Resources\Enrollments\Tables;

use Filament\Actions\BulkActionGroup;
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
          ->searchable(),
        TextColumn::make('course.name')
          ->searchable(),
        TextColumn::make('academicSession.name')
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
          ->searchable(),
        TextColumn::make('enrolled_at')
          ->dateTime()
          ->sortable(),
        TextColumn::make('completed_at')
          ->dateTime()
          ->sortable(),
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
        TernaryFilter::make('is_online')
          ->label('Mode en ligne ECAP'),
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
