<?php

namespace App\Filament\Resources\LearningGroupMembers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formulaire d'un membre de groupe de vacation.
 */
class LearningGroupMemberForm
{
  /**
   * Configure le schéma Filament.
   */
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        Section::make('Informations générales')
          ->description('Affectation d\'un fidèle à un groupe de vacation ECAP.')
          ->schema([
            Select::make('learning_group_id')
              ->label('Groupe de vacation')
              ->relationship('learningGroup', 'name')
              ->required(),
            Select::make('user_id')
              ->label('Fidèle')
              ->relationship('user', 'name')
              ->searchable()
              ->required(),
            Select::make('group_role')
              ->label('Rôle dans le groupe')
              ->options([
                'membre' => 'Membre',
                'leader' => 'Responsable',
              ])
              ->default('membre')
              ->required()
              ->native(false),
          ])
          ->columns(2),
      ]);
  }
}
