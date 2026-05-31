<?php

namespace App\Filament\Resources\Questions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Renseignez les champs ci-dessous.')
                    ->schema([
                Select::make('assessment_id')
                    ->relationship('assessment', 'title')
                    ->required(),
                TextInput::make('type')
                    ->required(),
                Textarea::make('stem')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('points')
                    ->required()
                    ->numeric()
                    ->default(1),
                Textarea::make('metadata')
                    ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}

