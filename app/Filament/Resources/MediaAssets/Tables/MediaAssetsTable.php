<?php

namespace App\Filament\Resources\MediaAssets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use TinusG\FilamentHoverImageColumn\HoverImageColumn;

/**
 * Tableau de la bibliothèque média.
 */
class MediaAssetsTable
{
  /**
   * Configure la table Filament.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->columns([
        HoverImageColumn::make('preview')
          ->label('Aperçu')
          ->getStateUsing(fn ($record) => $record->previewUrl())
          ->previewSize(320)
          ->circular()
          ->defaultImageUrl(null)
          ->toggleable(),
        TextColumn::make('path')
          ->label('Chemin')
          ->searchable(),
        TextColumn::make('disk')
          ->label('Disque')
          ->searchable(),
        TextColumn::make('mime_type')
          ->label('Type MIME')
          ->searchable(),
        TextColumn::make('size_bytes')
          ->label('Taille (octets)')
          ->numeric()
          ->sortable(),
        TextColumn::make('duration_seconds')
          ->label('Durée (s)')
          ->numeric()
          ->sortable(),
        TextColumn::make('transcode_status')
          ->label('Transcodage')
          ->searchable(),
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
