<?php

namespace App\Filament\Resources\VacationQuestions\Schemas;

use App\Enums\EcapVacationRole;
use App\Enums\VacationQuestionStatus;
use App\Filament\Support\AiWriterField;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

/**
 * Formulaire d'une question vacation ECAP.
 */
class VacationQuestionForm
{
  /**
   * Configure le schéma Filament.
   */
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        Section::make('Question')
          ->schema([
            Select::make('academic_session_id')
              ->label('Session ECAP')
              ->relationship(
                'academicSession',
                'name',
                fn ($query) => $query->whereHas('program', fn ($program) => $program->where('slug', 'ecap')),
              )
              ->required()
              ->searchable()
              ->preload()
              ->live(),
            Select::make('session_vacation_id')
              ->label('Vacation')
              ->relationship(
                'sessionVacation',
                'name',
                fn ($query, $get) => $query
                  ->when($get('academic_session_id'), fn ($inner, $sessionId) => $inner->where('academic_session_id', $sessionId)),
              )
              ->searchable()
              ->preload(),
            Select::make('asked_by_user_id')
              ->label('Auteur (fidèle)')
              ->relationship('askedBy', 'name')
              ->required()
              ->searchable()
              ->preload(),
            Select::make('course_module_id')
              ->label('Module de cours ECAP')
              ->relationship(
                'courseModule',
                'name',
                fn ($query) => $query->whereHas('course.program', fn ($program) => $program->where('slug', 'ecap')),
              )
              ->searchable()
              ->preload()
              ->required(),
            Toggle::make('is_addressed_to_all_teachers')
              ->label('@tous les enseignants')
              ->helperText('Si activé, tous les enseignants de la session peuvent répondre.'),
            Select::make('addressed_to_user_id')
              ->label('Enseignant (@mention)')
              ->relationship('addressedToUser', 'name')
              ->searchable()
              ->preload()
              ->visible(fn ($get) => ! $get('is_addressed_to_all_teachers')),
            Select::make('addressed_to_role')
              ->label('Rôle (legacy)')
              ->options(EcapVacationRole::options())
              ->default(EcapVacationRole::Teacher->value),
            TextInput::make('subject')
              ->label('Objet')
              ->maxLength(200)
              ->columnSpanFull(),
            Textarea::make('body')
              ->label('Question')
              ->required()
              ->rows(4)
              ->columnSpanFull(),
            Select::make('status')
              ->label('Statut')
              ->options(VacationQuestionStatus::options())
              ->default(VacationQuestionStatus::Pending->value)
              ->required(),
          ])
          ->columns(2),
        Section::make('Réponse')
          ->description('Renseignez la réponse de l\'acteur de vacation.')
          ->schema([
            Textarea::make('answer_body')
              ->label('Réponse')
              ->rows(5)
              ->columnSpanFull()
              ->hintAction(AiWriterField::vacationAnswer()),
            Select::make('answered_by_user_id')
              ->label('Répondu par')
              ->relationship('answeredBy', 'name')
              ->searchable()
              ->preload()
              ->default(fn (): ?int => Auth::id()),
            DateTimePicker::make('answered_at')
              ->label('Date de réponse'),
          ])
          ->columns(2),
      ]);
  }
}
