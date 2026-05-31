<?php

namespace App\Filament\Resources\Chapters\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formulaire d'un chapitre de cours.
 */
class ChapterForm
{
  /**
   * Configure le schéma Filament.
   */
  public static function configure(Schema $schema): Schema
  {
    $help = config('filament_field_help.chapter');

    return $schema
      ->components([
        Section::make('Chapitre')
          ->description('Unité de lecture dans un module. Ajoutez le contenu via l\'onglet « Contenu du chapitre » après enregistrement.')
          ->schema([
            Select::make('course_id')
              ->label('Cours')
              ->relationship('course', 'name')
              ->required()
              ->searchable()
              ->preload()
              ->helperText($help['course_id']),
            Select::make('course_module_id')
              ->label('Module')
              ->relationship('courseModule', 'name')
              ->searchable()
              ->preload()
              ->helperText($help['course_module_id']),
            TextInput::make('title')
              ->label('Titre')
              ->required()
              ->helperText($help['title']),
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
