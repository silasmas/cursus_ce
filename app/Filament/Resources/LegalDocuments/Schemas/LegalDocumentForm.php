<?php

namespace App\Filament\Resources\LegalDocuments\Schemas;

use App\Services\Legal\LegalDocumentService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formulaire Filament pour un document légal versionné.
 */
class LegalDocumentForm
{
  /**
   * Configure le schéma du formulaire.
   */
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        Section::make('Document')
          ->description('Le PDF actif est imposé à l\'acceptation lors de l\'inscription si « Obligatoire à l\'inscription » est coché.')
          ->schema([
            TextInput::make('slug')
              ->label('Identifiant technique')
              ->default(LegalDocumentService::ECAP_ROI_SLUG)
              ->required()
              ->maxLength(120)
              ->unique(ignoreRecord: true),
            TextInput::make('title')
              ->label('Titre affiché')
              ->required()
              ->maxLength(255)
              ->columnSpanFull(),
            Textarea::make('summary')
              ->label('Résumé (optionnel)')
              ->rows(3)
              ->columnSpanFull(),
            TextInput::make('version')
              ->label('Version')
              ->default('2024-1')
              ->required()
              ->maxLength(32),
            FileUpload::make('file_path')
              ->label('Fichier PDF')
              ->disk('public')
              ->directory('legal-documents')
              ->acceptedFileTypes(['application/pdf'])
              ->required()
              ->downloadable()
              ->openable()
              ->columnSpanFull(),
            Toggle::make('is_active')
              ->label('Version active')
              ->default(true),
            Toggle::make('required_at_registration')
              ->label('Obligatoire à l\'inscription')
              ->default(true),
            DateTimePicker::make('published_at')
              ->label('Date de publication')
              ->default(now()),
          ])
          ->columns(2),
      ]);
  }
}
