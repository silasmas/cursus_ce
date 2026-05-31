<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formulaire de création / édition d'un cours.
 */
class CourseForm
{
  /**
   * Configure le schéma Filament.
   */
  public static function configure(Schema $schema): Schema
  {
    $help = config('filament_field_help.course');

    return $schema
      ->components([
        Section::make('Informations générales')
          ->description('Un cours appartient à un programme et contient des modules.')
          ->schema([
            Select::make('program_id')
              ->label('Cursus')
              ->relationship('program', 'name')
              ->required()
              ->searchable()
              ->preload()
              ->helperText($help['program_id']),
            TextInput::make('name')
              ->label('Nom')
              ->required()
              ->helperText($help['name']),
            TextInput::make('slug')
              ->label('Identifiant URL')
              ->required()
              ->helperText($help['slug']),
            TextInput::make('sort_order')
              ->label('Ordre')
              ->required()
              ->numeric()
              ->default(0)
              ->helperText($help['sort_order']),
            Toggle::make('is_published')
              ->label('Publié')
              ->required()
              ->default(false)
              ->helperText($help['is_published']),
          ])
          ->columns(2),
      ]);
  }
}
