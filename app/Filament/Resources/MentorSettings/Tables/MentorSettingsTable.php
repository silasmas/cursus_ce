<?php

namespace App\Filament\Resources\MentorSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Tableau des paramètres mentorat.
 */
class MentorSettingsTable
{
  /**
   * Configure la table.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('visible_channels')
          ->label('Canaux')
          ->formatStateUsing(fn ($state): string => is_array($state) ? implode(', ', $state) : '-'),
        IconColumn::make('zoom_auto_create_link')
          ->label('Zoom auto')
          ->boolean(),
        IconColumn::make('notify_with_email')
          ->label('Email')
          ->boolean(),
        IconColumn::make('notify_with_sound')
          ->label('Son')
          ->boolean(),
        IconColumn::make('notify_with_blink')
          ->label('Clignotement')
          ->boolean(),
      ])
      ->recordActions([
        EditAction::make()->label('Modifier'),
      ])
      ->toolbarActions([]);
  }
}

