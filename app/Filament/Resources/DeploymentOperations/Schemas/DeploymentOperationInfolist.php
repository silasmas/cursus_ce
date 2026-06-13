<?php

namespace App\Filament\Resources\DeploymentOperations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Fiche détail d'une opération de maintenance production.
 */
class DeploymentOperationInfolist
{
  /**
   * Configure le schéma Filament.
   */
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        Section::make('Opération')
          ->schema([
            TextEntry::make('type')
              ->label('Type')
              ->badge()
              ->formatStateUsing(fn ($state) => $state?->label() ?? '—'),
            TextEntry::make('status')
              ->label('Statut')
              ->badge()
              ->formatStateUsing(fn ($state) => $state?->label() ?? '—')
              ->color(fn ($state) => $state?->color() ?? 'gray'),
            TextEntry::make('command')
              ->label('Commande')
              ->fontFamily('mono'),
            TextEntry::make('exit_code')
              ->label('Code de sortie')
              ->placeholder('—'),
            TextEntry::make('executedBy.name')
              ->label('Exécuté par')
              ->placeholder('Système'),
            TextEntry::make('started_at')
              ->label('Début')
              ->dateTime('d/m/Y H:i:s'),
            TextEntry::make('finished_at')
              ->label('Fin')
              ->dateTime('d/m/Y H:i:s')
              ->placeholder('—'),
            TextEntry::make('duration')
              ->label('Durée')
              ->state(fn ($record): ?string => $record->durationLabel())
              ->placeholder('—'),
          ])
          ->columns(2),
        Section::make('Sortie console')
          ->schema([
            TextEntry::make('output')
              ->label('Résultat')
              ->columnSpanFull()
              ->fontFamily('mono')
              ->markdown(false)
              ->prose(false)
              ->copyable(),
          ]),
      ]);
  }
}
