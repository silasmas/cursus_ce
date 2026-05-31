<?php

namespace App\Filament\Resources\Programs\Tables;

use App\Services\Program\MergeApollosCeProgramService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Table d'administration des cursus.
 */
class ProgramsTable
{
  /**
   * Configure la table Filament.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('slug')
          ->label('Slug')
          ->searchable(),
        TextColumn::make('name')
          ->label('Cursus')
          ->formatStateUsing(fn (string $state, $record): string => $record->slug === MergeApollosCeProgramService::ECAP_SLUG
            ? 'ECAP'
            : $state)
          ->searchable(),
        TextColumn::make('sort_order')
          ->label('Ordre')
          ->numeric()
          ->sortable(),
        IconColumn::make('is_active')
          ->label('Actif')
          ->boolean(),
        IconColumn::make('is_mandatory')
          ->label('Oblig.')
          ->boolean()
          ->toggleable(),
        IconColumn::make('is_open')
          ->label('Ouvert')
          ->boolean()
          ->toggleable(),
        TextColumn::make('type')
          ->label('Type')
          ->badge()
          ->searchable(),
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
        EditAction::make()->label('Modifier'),
        DeleteAction::make()->label('Supprimer'),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          DeleteBulkAction::make(),
        ]),
      ]);
  }
}
