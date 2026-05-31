<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Chapitres rattachés directement à un cours.
 */
class ChaptersRelationManager extends RelationManager
{
  protected static string $relationship = 'chapters';

  protected static ?string $title = 'Chapitres';

  protected static ?string $modelLabel = 'chapitre';

  /**
   * Formulaire d'un chapitre.
   */
  public function form(Schema $schema): Schema
  {
    $help = config('filament_field_help.chapter');

    return $schema
      ->components([
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
          ->helperText($help['is_published']),
      ]);
  }

  /**
   * Liste des chapitres du cours.
   */
  public function table(Table $table): Table
  {
    return $table
      ->recordTitleAttribute('title')
      ->columns([
        TextColumn::make('courseModule.name')
          ->label('Module')
          ->searchable(),
        TextColumn::make('title')
          ->label('Titre')
          ->searchable(),
        TextColumn::make('sort_order')
          ->label('Ordre')
          ->numeric()
          ->sortable(),
        IconColumn::make('is_published')
          ->label('Publié')
          ->boolean(),
      ])
      ->headerActions([
        CreateAction::make()->label('Ajouter un chapitre'),
      ])
      ->recordActions([
        EditAction::make()->label('Modifier'),
        DeleteAction::make()->label('Supprimer'),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          DeleteBulkAction::make()->label('Supprimer la sélection'),
        ]),
      ]);
  }
}
