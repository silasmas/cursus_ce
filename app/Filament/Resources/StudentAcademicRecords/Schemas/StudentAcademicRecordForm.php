<?php

namespace App\Filament\Resources\StudentAcademicRecords\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formulaire d'un dossier académique ECAP.
 */
class StudentAcademicRecordForm
{
  /**
   * Configure le schéma Filament.
   */
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        Section::make('Informations générales')
          ->description('Synthèse du parcours ECAP du fidèle pour une session.')
          ->schema([
            Select::make('academic_session_id')
              ->label('Session ECAP')
              ->relationship('academicSession', 'name')
              ->required(),
            Select::make('user_id')
              ->label('Fidèle')
              ->relationship('user', 'name')
              ->searchable()
              ->required(),
            Textarea::make('summary')
              ->label('Synthèse / remarques')
              ->columnSpanFull(),
            TextInput::make('final_average')
              ->label('Moyenne finale')
              ->numeric(),
            DateTimePicker::make('validated_at')
              ->label('Validé le'),
          ])
          ->columns(2),
      ]);
  }
}
