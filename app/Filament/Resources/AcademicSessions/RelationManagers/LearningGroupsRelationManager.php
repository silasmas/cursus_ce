<?php

namespace App\Filament\Resources\AcademicSessions\RelationManagers;

use App\Filament\Concerns\HasRelationManagerHelp;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Groupes de vacation d'une génération ECAP.
 */
class LearningGroupsRelationManager extends RelationManager
{
  use HasRelationManagerHelp;

  protected static string $relationship = 'learningGroups';

  protected static ?string $title = 'Groupes de vacation';

  protected static ?string $modelLabel = 'groupe de vacation';

  /**
   * Clé d'aide contextuelle.
   */
  protected static function helpKey(): string
  {
    return 'learning_groups';
  }

  /**
   * Formulaire d'un groupe de vacation.
   */
  public function form(Schema $schema): Schema
  {
    $help = config('filament_field_help.learning_group');
    $sortHelp = config('filament_field_help.sort_order');

    return $schema
      ->components([
        TextInput::make('name')
          ->label('Nom du groupe')
          ->required()
          ->helperText($help['name']),
        TextInput::make('sort_order')
          ->label('Ordre')
          ->required()
          ->numeric()
          ->default(0)
          ->helperText($sortHelp),
      ]);
  }

  /**
   * Liste des groupes de vacation.
   */
  public function table(Table $table): Table
  {
    return $table
      ->recordTitleAttribute('name')
      ->columns([
        TextColumn::make('name')
          ->label('Groupe')
          ->searchable(),
        TextColumn::make('sort_order')
          ->label('Ordre')
          ->numeric()
          ->sortable(),
      ])
      ->headerActions([
        CreateAction::make()->label('Ajouter un groupe'),
      ])
      ->recordActions([
        EditAction::make(),
        DeleteAction::make(),
      ]);
  }
}
