<?php

namespace App\Filament\Resources\AcademicSessions\Schemas;

use App\Models\AcademicSession;
use App\Models\Program;
use App\Services\Public\RegistrationAvailabilityService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formulaire d'une session ECAP.
 */
class AcademicSessionForm
{
  /**
   * Configure le schéma Filament.
   */
  public static function configure(Schema $schema): Schema
  {
    $help = config('filament_field_help.academic_session');

    return $schema
      ->components([
        Hidden::make('program_id')
          ->default(fn (): ?int => Program::query()->where('slug', 'ecap')->value('id'))
          ->required(),
        Section::make('Session ECAP')
          ->description('Le code ECAP-XXXXXX est généré automatiquement à la création.')
          ->schema([
            TextInput::make('name')
              ->label('Nom de la session')
              ->placeholder('Ex. Session ECAP 2026')
              ->required()
              ->helperText($help['name']),
            Placeholder::make('code_info')
              ->label('Code session')
              ->content('Un identifiant unique sera généré automatiquement (ex. ECAP-K7M2P9).')
              ->visibleOn('create'),
            TextInput::make('code')
              ->label('Code session')
              ->disabled()
              ->dehydrated(false)
              ->visibleOn('edit'),
            TextInput::make('generation_number')
              ->label('N° ordinal')
              ->disabled()
              ->dehydrated(false)
              ->helperText('Numéro séquentiel pour l\'affichage « nᵉ session » (généré à la création).')
              ->visibleOn('edit'),
            Toggle::make('is_active')
              ->label('Active')
              ->required()
              ->helperText($help['is_active']),
          ])
          ->columns(2),
        Section::make('Calendrier global')
          ->description('Dates de la vacation complète. Ensuite, ouvrez les onglets « Calendrier » et « Périodes pédagogiques » pour planifier modules, activités et fenêtres pédagogiques.')
          ->schema([
            DatePicker::make('starts_on')
              ->label('Début session')
              ->helperText($help['starts_on']),
            DatePicker::make('ends_on')
              ->label('Fin session')
              ->helperText($help['ends_on']),
          ])
          ->columns(2),
        Section::make('Inscriptions publiques')
          ->description('Fenêtre affichée sur la page d\'accueil et le bouton « S\'inscrire ». Cocher « Active » seul ne suffit pas : respectez les dates ci-dessous.')
          ->schema([
            Placeholder::make('registration_portal_status')
              ->label('Statut actuel sur le portail')
              ->content(fn (?AcademicSession $record): string => $record instanceof AcademicSession
                ? app(RegistrationAvailabilityService::class)->adminStatusForSession($record)
                : '—')
              ->visibleOn('edit')
              ->columnSpanFull(),
            DateTimePicker::make('registration_opens_at')
              ->label('Ouverture inscriptions')
              ->helperText($help['registration_opens_at']),
            DateTimePicker::make('registration_closes_at')
              ->label('Clôture inscriptions')
              ->helperText($help['registration_closes_at']),
          ])
          ->columns(2),
        Section::make('Reprendre une session précédente')
          ->description('À la création uniquement : copie calendrier, périodes, vacations, groupes et affectations acteurs.')
          ->schema([
            Select::make('duplicate_from_session_id')
              ->label('Session modèle')
              ->options(fn (): array => AcademicSession::query()
                ->whereHas('program', fn ($q) => $q->where('slug', 'ecap'))
                ->orderByDesc('generation_number')
                ->pluck('name', 'id')
                ->all())
              ->searchable()
              ->preload()
              ->placeholder('Configuration vide')
              ->helperText('Les cours, modules, examens et TP restent dans le programme ECAP ; seule la configuration de session est recopiée.')
              ->dehydrated(false)
              ->visibleOn('create'),
          ])
          ->collapsed()
          ->visibleOn('create')
          ->columnSpanFull(),
      ]);
  }
}
