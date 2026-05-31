<?php

namespace App\Filament\Resources\LearningGroups\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formulaire d'un groupe de vacation ECAP.
 */
class LearningGroupForm
{
  /**
   * Configure le schéma Filament.
   */
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        Section::make('Informations générales')
          ->description('Petite communauté d\'étude rattachée à une session ECAP.')
          ->schema([
            Select::make('academic_session_id')
              ->label('Session ECAP')
              ->relationship('academicSession', 'name')
              ->required(),
            TextInput::make('name')
              ->label('Nom du groupe')
              ->required(),
            TextInput::make('sort_order')
              ->label('Ordre d\'affichage')
              ->required()
              ->numeric()
              ->default(0),
          ])
          ->columns(2),
      ]);
  }
}
