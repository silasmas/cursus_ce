<?php

namespace App\Filament\Resources\LegalDocuments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Table Filament des documents légaux.
 */
class LegalDocumentsTable
{
  /**
   * Configure la table.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->defaultSort('published_at', 'desc')
      ->columns([
        TextColumn::make('title')
          ->label('Titre')
          ->searchable(),
        TextColumn::make('version')
          ->label('Version')
          ->badge(),
        IconColumn::make('is_active')
          ->label('Active')
          ->boolean(),
        IconColumn::make('required_at_registration')
          ->label('Inscription')
          ->boolean(),
        TextColumn::make('published_at')
          ->label('Publié le')
          ->dateTime('d/m/Y H:i')
          ->sortable(),
        TextColumn::make('updated_at')
          ->label('Modifié')
          ->dateTime('d/m/Y H:i')
          ->toggleable(isToggledHiddenByDefault: true),
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
