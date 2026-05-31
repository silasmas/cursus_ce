<?php

namespace App\Filament\Resources\Enrollments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formulaire d'édition d'une inscription.
 */
class EnrollmentForm
{
  /**
   * Configure le schéma Filament.
   */
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        Section::make('Informations générales')
          ->description('Renseignez les champs ci-dessous.')
          ->schema([
            Select::make('user_id')
              ->relationship('user', 'name')
              ->required(),
            Select::make('program_id')
              ->relationship('program', 'name')
              ->required()
              ->live(),
            Select::make('course_id')
              ->relationship('course', 'name'),
            Select::make('academic_session_id')
              ->relationship('academicSession', 'name'),
            Toggle::make('is_online')
              ->label('ECAP en ligne')
              ->helperText('Désactivé = présentiel (lecture seule sur la plateforme).')
              ->visible(fn ($get): bool => self::isEcapProgram($get('program_id'))),
            TextInput::make('status')
              ->required(),
            DateTimePicker::make('enrolled_at')
              ->required(),
            DateTimePicker::make('completed_at'),
          ])
          ->columns(2),
      ]);
  }

  /**
   * Indique si le programme sélectionné est ECAP.
   */
  private static function isEcapProgram(mixed $programId): bool
  {
    if (! $programId) {
      return false;
    }

    return \App\Models\Program::query()
      ->whereKey($programId)
      ->where('slug', 'ecap')
      ->exists();
  }
}
