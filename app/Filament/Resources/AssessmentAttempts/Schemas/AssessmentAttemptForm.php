<?php

namespace App\Filament\Resources\AssessmentAttempts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssessmentAttemptForm
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
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('enrollment_id')
                    ->relationship('enrollment', 'id'),
                DateTimePicker::make('started_at')
                    ->required(),
                DateTimePicker::make('submitted_at'),
                TextInput::make('score')
                    ->numeric(),
                Toggle::make('passed'),
                TextInput::make('status')
                    ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}

