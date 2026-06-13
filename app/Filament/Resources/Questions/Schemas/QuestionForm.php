<?php

namespace App\Filament\Resources\Questions\Schemas;

use App\Enums\QuestionType;
use App\Filament\Support\AiWriterField;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formulaire d'une question de test (QCM ou rédigée).
 */
class QuestionForm
{
  /**
   * Configure le schéma Filament.
   */
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        Section::make('Informations générales')
          ->description('Question rattachée à un test ou une évaluation.')
          ->schema([
            Select::make('assessment_id')
              ->label('Test / évaluation')
              ->relationship('assessment', 'title')
              ->required(),
            Select::make('type')
              ->label('Type')
              ->options(QuestionType::options())
              ->required()
              ->native(false),
            Textarea::make('stem')
              ->label('Énoncé')
              ->required()
              ->columnSpanFull()
              ->hintAction(AiWriterField::questionStem()),
            TextInput::make('sort_order')
              ->label('Ordre')
              ->required()
              ->numeric()
              ->default(0),
            TextInput::make('points')
              ->label('Points')
              ->required()
              ->numeric()
              ->default(1),
            Textarea::make('metadata')
              ->label('Métadonnées (JSON)')
              ->columnSpanFull(),
          ])
          ->columns(2),
      ]);
  }
}
