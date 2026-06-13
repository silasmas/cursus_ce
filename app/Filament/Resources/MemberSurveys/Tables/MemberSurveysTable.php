<?php

namespace App\Filament\Resources\MemberSurveys\Tables;

use App\Filament\Tables\Columns\UserTableColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Tableau des réponses au sondage fidèle.
 */
class MemberSurveysTable
{
  /**
   * Configure la table Filament.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->defaultSort('submitted_at', 'desc')
      ->columns([
        UserTableColumn::make('user')
          ->label('Fidèle'),
        TextColumn::make('satisfaction')
          ->label('Satisfaction')
          ->formatStateUsing(fn (?int $state): string => $state ? "{$state}/5" : '—')
          ->sortable(),
        TextColumn::make('nps_score')
          ->label('NPS')
          ->formatStateUsing(fn (?int $state): string => $state !== null ? "{$state}/10" : '—')
          ->sortable(),
        TextColumn::make('weeks_since_enrollment')
          ->label('Semaines')
          ->suffix(' sem.')
          ->sortable(),
        TextColumn::make('comment')
          ->label('Commentaire')
          ->limit(60)
          ->wrap()
          ->toggleable(),
        TextColumn::make('submitted_at')
          ->label('Envoyé le')
          ->dateTime('d/m/Y H:i')
          ->sortable(),
      ]);
  }
}
