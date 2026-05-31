<?php

namespace App\Filament\Resources\EcapStaffAssignments\Schemas;

use App\Enums\EcapVacationRole;
use App\Models\CourseModule;
use App\Models\User;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formulaire d'affectation d'un acteur de vacation ECAP.
 */
class EcapStaffAssignmentForm
{
  /**
   * Configure le schéma Filament.
   */
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        Section::make('Affectation')
          ->description('Liez un ou plusieurs utilisateurs à un rôle ECAP pour une session, une vacation ou un module.')
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
              ->label('Vacation présentiel')
              ->relationship(
                'sessionVacation',
                'name',
                fn ($query, $get) => $query
                  ->when($get('academic_session_id'), fn ($inner, $sessionId) => $inner->where('academic_session_id', $sessionId))
                  ->where('is_active', true),
              )
              ->searchable()
              ->preload()
              ->helperText('Laissez vide pour un rôle couvrant toute la session.'),
            Select::make('course_module_ids')
              ->label('Modules de cours')
              ->options(fn (): array => CourseModule::query()
                ->whereHas('course.program', fn ($program) => $program->where('slug', 'ecap'))
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all())
              ->multiple()
              ->required()
              ->searchable()
              ->preload()
              ->visibleOn('create')
              ->hiddenOn('edit')
              ->visible(fn ($get): bool => in_array($get('role'), [
                EcapVacationRole::Teacher->value,
                EcapVacationRole::Supervisor->value,
              ], true))
              ->helperText('Sélectionnez un ou plusieurs modules : une affectation est créée par module (un enseignant et un superviseur par module).'),
            Select::make('course_module_id')
              ->label('Module de cours')
              ->relationship(
                'courseModule',
                'name',
                fn ($query) => $query->whereHas('course.program', fn ($program) => $program->where('slug', 'ecap')),
              )
              ->searchable()
              ->preload()
              ->visibleOn('edit')
              ->hiddenOn('create')
              ->visible(fn ($get): bool => in_array($get('role'), [
                EcapVacationRole::Teacher->value,
                EcapVacationRole::Supervisor->value,
              ], true))
              ->helperText('Module concerné par cette affectation.'),
            Select::make('user_ids')
              ->label('Utilisateurs')
              ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all())
              ->multiple()
              ->required()
              ->searchable()
              ->visibleOn('create')
              ->helperText('Vous pouvez sélectionner plusieurs personnes en une seule fois.'),
            Select::make('user_id')
              ->label('Utilisateur')
              ->relationship('user', 'name')
              ->required()
              ->searchable()
              ->preload()
              ->visibleOn('edit'),
            Select::make('role')
              ->label('Rôle vacation')
              ->options(EcapVacationRole::options())
              ->required()
              ->live(),
            Placeholder::make('role_hint')
              ->label('Mission du rôle')
              ->content(fn ($get): string => filled($get('role'))
                ? EcapVacationRole::from($get('role'))->description()
                : 'Choisissez un rôle pour voir sa description.')
              ->columnSpanFull(),
            Placeholder::make('role_rules')
              ->label('Règles de cumul')
              ->content('Maximum 2 rôles actifs par session. Enseignant / inspecteur incompatible avec superviseur / modérateur. Un enseignant ou superviseur peut couvrir plusieurs modules (une ligne par module).')
              ->columnSpanFull(),
            Toggle::make('is_active')
              ->label('Actif')
              ->default(true),
            Textarea::make('notes')
              ->label('Notes internes')
              ->rows(3)
              ->columnSpanFull(),
          ])
          ->columns(2),
      ]);
  }
}
