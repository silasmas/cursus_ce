<?php

namespace App\Filament\Resources\Programs\Schemas;

use App\Services\Program\MergeApollosCeProgramService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

/**
 * Formulaire d'administration d'un cursus.
 */
class ProgramForm
{
  /**
   * Configure le schéma Filament.
   */
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        Section::make('Informations générales')
          ->description('Le cursus ECAP utilise le slug « ecap ». Un seul enregistrement ECAP doit exister.')
          ->schema([
            TextInput::make('slug')
              ->label('Slug')
              ->required()
              ->maxLength(50)
              ->unique(ignoreRecord: true)
              ->disabled(fn (?string $state): bool => $state === MergeApollosCeProgramService::ECAP_SLUG)
              ->dehydrated()
              ->rule(Rule::notIn([MergeApollosCeProgramService::LEGACY_APOLLOS_SLUG])),
            TextInput::make('name')
              ->label('Nom affiché')
              ->required()
              ->helperText(fn (?string $state, $get): ?string => $get('slug') === MergeApollosCeProgramService::ECAP_SLUG
                ? 'Recommandé : afficher « ECAP » comme nom public.'
                : null),
            Textarea::make('description')
              ->label('Description')
              ->columnSpanFull(),
            TextInput::make('sort_order')
              ->label('Ordre')
              ->required()
              ->numeric()
              ->default(0),
            Toggle::make('is_active')
              ->label('Actif')
              ->required(),
            TextInput::make('type')
              ->label('Type')
              ->required()
              ->default('cursus'),
          ])
          ->columns(2),
        Section::make('Règles d\'accès')
          ->description('Contrôle l\'accès au cursus côté espace membre.')
          ->schema([
            Toggle::make('is_mandatory')
              ->label('Obligatoire')
              ->default(false),
            Toggle::make('is_open')
              ->label('Ouvert')
              ->default(true),
            Toggle::make('optional_at_registration')
              ->label('Optionnel à l\'inscription')
              ->default(true),
            DateTimePicker::make('scheduled_open_at')
              ->label('Ouverture programmée')
              ->seconds(false),
          ])
          ->columns(2),
      ]);
  }
}
