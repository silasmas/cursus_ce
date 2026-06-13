<?php

namespace App\Filament\Resources\LoginEvents\Tables;

use App\Filament\Tables\Columns\UserTableColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Tableau des connexions enregistrées (lecture seule).
 */
class LoginEventsTable
{
  /**
   * Configure la table Filament.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->defaultSort('logged_in_at', 'desc')
      ->columns([
        UserTableColumn::make('user')
          ->label('Utilisateur'),
        TextColumn::make('guard')
          ->label('Espace')
          ->badge()
          ->formatStateUsing(fn (string $state): string => match ($state) {
            'member' => 'Portail fidèle',
            'admin' => 'Administration',
            default => $state,
          })
          ->color(fn (string $state): string => $state === 'admin' ? 'warning' : 'info'),
        TextColumn::make('device_type')
          ->label('Appareil')
          ->badge()
          ->formatStateUsing(fn (string $state): string => match ($state) {
            'mobile' => 'Mobile',
            'tablet' => 'Tablette',
            'desktop' => 'Ordinateur',
            default => 'Inconnu',
          })
          ->color(fn (string $state): string => match ($state) {
            'mobile' => 'success',
            'tablet' => 'warning',
            'desktop' => 'info',
            default => 'gray',
          }),
        TextColumn::make('platform')
          ->label('Système')
          ->toggleable(),
        TextColumn::make('browser')
          ->label('Navigateur')
          ->toggleable(),
        TextColumn::make('ip_address')
          ->label('IP')
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('logged_in_at')
          ->label('Connexion')
          ->dateTime('d/m/Y H:i')
          ->sortable(),
      ])
      ->filters([
        SelectFilter::make('guard')
          ->label('Espace')
          ->options([
            'member' => 'Portail fidèle',
            'admin' => 'Administration',
          ]),
        SelectFilter::make('device_type')
          ->label('Appareil')
          ->options([
            'mobile' => 'Mobile',
            'tablet' => 'Tablette',
            'desktop' => 'Ordinateur',
            'unknown' => 'Inconnu',
          ]),
      ])
      ->recordActions([])
      ->toolbarActions([]);
  }
}
