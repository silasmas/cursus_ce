<?php

namespace App\Filament\Resources\DeploymentOperations\Tables;

use App\Enums\DeploymentOperationStatus;
use App\Enums\DeploymentOperationType;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Tableau du journal des opérations de maintenance production.
 */
class DeploymentOperationsTable
{
  /**
   * Configure la table Filament.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->defaultSort('started_at', 'desc')
      ->columns([
        TextColumn::make('started_at')
          ->label('Date')
          ->dateTime('d/m/Y H:i:s')
          ->sortable(),
        TextColumn::make('type')
          ->label('Type')
          ->badge()
          ->formatStateUsing(fn (DeploymentOperationType $state): string => $state->label())
          ->color('gray'),
        TextColumn::make('status')
          ->label('Statut')
          ->badge()
          ->formatStateUsing(fn (DeploymentOperationStatus $state): string => $state->label())
          ->color(fn (DeploymentOperationStatus $state): string => $state->color()),
        TextColumn::make('command')
          ->label('Commande')
          ->searchable()
          ->formatStateUsing(function (?string $state, $record): string {
            if ($record->type === DeploymentOperationType::SeederRun) {
              return 'db:seed — '.($record->parameters['label'] ?? $record->parameters['class'] ?? 'seeder');
            }

            return $state ?? '—';
          })
          ->fontFamily('mono')
          ->size('sm'),
        TextColumn::make('exit_code')
          ->label('Code')
          ->alignCenter()
          ->placeholder('—'),
        TextColumn::make('duration')
          ->label('Durée')
          ->state(fn ($record): ?string => $record->durationLabel())
          ->placeholder('—'),
        TextColumn::make('executedBy.name')
          ->label('Par')
          ->placeholder('Système'),
      ])
      ->filters([
        SelectFilter::make('type')
          ->label('Type')
          ->options(DeploymentOperationType::options()),
        SelectFilter::make('status')
          ->label('Statut')
          ->options(DeploymentOperationStatus::options()),
      ])
      ->recordActions([
        ViewAction::make(),
      ])
      ->emptyStateHeading('Aucune opération enregistrée')
      ->emptyStateDescription('Lancez une action ci-dessus pour préparer la production.');
  }
}
