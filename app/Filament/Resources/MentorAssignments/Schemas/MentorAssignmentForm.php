<?php

namespace App\Filament\Resources\MentorAssignments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MentorAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Renseignez les champs ci-dessous.')
                    ->schema([
                Select::make('mentor_id')
                    ->relationship('mentor', 'name')
                    ->required(),
                Select::make('mentee_id')
                    ->relationship('mentee', 'name')
                    ->required(),
                Select::make('program_id')
                    ->relationship('program', 'name')
                    ->required(),
                Select::make('enrollment_id')
                    ->relationship('enrollment', 'id'),
                TextInput::make('assigned_by_user_id')
                    ->numeric(),
                TextInput::make('assignment_mode')
                    ->required(),
                TextInput::make('status')
                    ->required(),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('ended_at'),
                    ])
                    ->columns(2),
            ]);
    }
}

