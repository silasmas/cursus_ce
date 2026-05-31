<?php

namespace App\Filament\Resources\QuestionOptions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuestionOptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Renseignez les champs ci-dessous.')
                    ->schema([
                Select::make('question_id')
                    ->relationship('question', 'id')
                    ->required(),
                TextInput::make('label')
                    ->required(),
                Toggle::make('is_correct')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                    ])
                    ->columns(2),
            ]);
    }
}

