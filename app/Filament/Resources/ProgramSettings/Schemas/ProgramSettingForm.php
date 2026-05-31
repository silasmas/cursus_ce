<?php

namespace App\Filament\Resources\ProgramSettings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formulaire de configuration métier d'un cursus.
 */
class ProgramSettingForm
{
  /**
   * Configure le schéma Filament.
   */
  public static function configure(Schema $schema): Schema
  {
    $help = config('filament_field_help.program_setting');

    return $schema
      ->components([
        Section::make('Cursus concerné')
          ->schema([
            Select::make('program_id')
              ->label('Cursus')
              ->relationship('program', 'name')
              ->required()
              ->searchable()
              ->preload()
              ->helperText($help['program_id']),
          ]),
        Section::make('Règles de parcours')
          ->description('Ces options pilotent le déblocage des chapitres et les prérequis quiz côté fidèle.')
          ->schema([
            Toggle::make('linear_progression')
              ->label('Progression linéaire')
              ->default(true)
              ->inline(false)
              ->helperText($help['linear_progression'])
              ->columnSpan(1),
            Toggle::make('quiz_mandatory')
              ->label('Quiz obligatoires')
              ->default(false)
              ->inline(false)
              ->helperText($help['quiz_mandatory'])
              ->columnSpan(1),
          ])
          ->columns(2),
        Section::make('Paramètres avancés')
          ->schema([
            Textarea::make('settings')
              ->label('Configuration JSON')
              ->columnSpanFull()
              ->helperText($help['settings']),
          ]),
      ]);
  }
}
