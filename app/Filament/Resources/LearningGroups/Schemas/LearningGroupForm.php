<?php

namespace App\Filament\Resources\LearningGroups\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LearningGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Renseignez les champs ci-dessous.')
                    ->schema([
                Select::make('academic_session_id')
                    ->relationship('academicSession', 'name')
                    ->required(),
                TextInput::make('name')
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

