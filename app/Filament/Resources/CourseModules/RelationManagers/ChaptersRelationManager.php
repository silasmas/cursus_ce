<?php

namespace App\Filament\Resources\CourseModules\RelationManagers;

use App\Filament\Concerns\HasRelationManagerHelp;
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
 * Chapitres composant un module de cours.
 */
class ChaptersRelationManager extends RelationManager
{
  use HasRelationManagerHelp;

  protected static string $relationship = 'chapters';

  protected static ?string $title = 'Chapitres';

  protected static ?string $modelLabel = 'chapitre';

  /**
   * Clé d'aide contextuelle.
   */
  protected static function helpKey(): string
  {
    return 'chapters';
  }

  /**
   * Formulaire d'un chapitre.
   */
  public function form(Schema $schema): Schema
  {
    return $schema
      ->components([
        Select::make('course_id')
          ->label('Cours')
          ->relationship('course', 'name')
          ->required(),
        TextInput::make('title')
          ->label('Titre')
          ->required(),
        TextInput::make('sort_order')
          ->label('Ordre')
          ->required()
          ->numeric()
          ->default(0)
          ->helperText(config('filament_field_help.sort_order')),
        Toggle::make('is_published')
          ->label('Publié')
          ->required(),
      ]);
  }

  /**
   * Liste des chapitres du module.
   */
  public function table(Table $table): Table
  {
    return $table
      ->recordTitleAttribute('title')
      ->columns([
        TextColumn::make('title')
          ->label('Chapitre')
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
