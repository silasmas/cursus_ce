<?php

namespace App\Filament\Resources\Enrollments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
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
          ->description('Inscription d\'un fidèle à un cursus ou à une session ECAP.')
          ->schema([
            Select::make('user_id')
              ->label('Fidèle')
              ->relationship('user', 'name')
              ->searchable()
              ->required(),
            Select::make('program_id')
              ->label('Cursus')
              ->relationship('program', 'name')
              ->required()
              ->live(),
            Select::make('course_id')
              ->label('Cours')
              ->relationship('course', 'name'),
            Select::make('academic_session_id')
              ->label('Session ECAP')
              ->relationship('academicSession', 'name'),
            Toggle::make('is_online')
              ->label('ECAP en ligne')
              ->helperText('Désactivé = présentiel (lecture seule sur la plateforme).')
              ->visible(fn ($get): bool => self::isEcapProgram($get('program_id'))),
            Select::make('status')
              ->label('Statut')
              ->options([
                'active' => 'Actif',
                'completed' => 'Terminé',
                'suspended' => 'Suspendu',
                'cancelled' => 'Annulé',
              ])
              ->required()
              ->native(false),
            DateTimePicker::make('enrolled_at')
              ->label('Inscrit le')
              ->required(),
            DateTimePicker::make('completed_at')
              ->label('Terminé le'),
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
