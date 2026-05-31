<?php

namespace App\Filament\Resources\ProgramAccesses\Schemas;

use App\Models\ProgramAccess;
use App\Services\ProgramAccess\ProgramAccessStateService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formulaire d'édition d'un accès cursus (état exclusif).
 */
class ProgramAccessForm
{
  /**
   * Configure le schéma Filament.
   */
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        Section::make('Fidèle et cursus')
          ->schema([
            Placeholder::make('user_label')
              ->label('Fidèle')
              ->content(fn (?ProgramAccess $record): string => $record?->user?->name ?? '—'),
            Placeholder::make('program_label')
              ->label('Cursus')
              ->content(fn (?ProgramAccess $record): string => $record?->program?->name ?? '—'),
          ])
          ->columns(2),
        Section::make('État d\'accès')
          ->description('Un seul état actif à la fois. Les interrupteurs de la liste appliquent la même logique.')
          ->schema([
            Select::make('access_status')
              ->label('Statut')
              ->options([
                'pending' => 'En attente',
                'open' => 'Ouvert',
                'needs_admin_validation' => 'À valider (déclaration fidèle)',
                'completed' => 'Acquis',
                'waived' => 'Dispensé',
              ])
              ->required()
              ->native(false)
              ->helperText('Choisissez l\'état effectif pour ce fidèle sur ce cursus.'),
          ]),
      ]);
  }

  /**
   * Code statut courant pour préremplir le formulaire.
   */
  public static function statusCode(ProgramAccess $access): string
  {
    return app(ProgramAccessStateService::class)->legacyCode($access);
  }
}
