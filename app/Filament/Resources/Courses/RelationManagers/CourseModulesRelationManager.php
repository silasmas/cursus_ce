<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Modules rattachés à un cours.
 */
class CourseModulesRelationManager extends RelationManager
{
  protected static string $relationship = 'courseModules';

  protected static ?string $title = 'Modules de cours';

  protected static ?string $modelLabel = 'module de cours';

  /**
   * Formulaire d'un module.
   */
  public function form(Schema $schema): Schema
  {
    return $schema
      ->components([
        TextInput::make('name')
          ->label('Nom du module')
          ->required(),
        TextInput::make('sort_order')
          ->label('Ordre')
          ->required()
          ->numeric()
          ->default(0)
          ->helperText(config('filament_field_help.sort_order')),
      ]);
  }

  /**
   * Liste des modules du cours.
   */
  public function table(Table $table): Table
  {
    return $table
      ->recordTitleAttribute('name')
      ->columns([
        TextColumn::make('name')
          ->label('Module')
          ->searchable(),
        TextColumn::make('sort_order')
          ->label('Ordre')
          ->numeric()
          ->sortable(),
        TextColumn::make('created_at')
          ->label('Créé le')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->headerActions([
        CreateAction::make()->label('Ajouter un module'),
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
