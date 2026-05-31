<?php

namespace App\Filament\Resources\MentoringDecisions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MentoringDecisionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Renseignez les champs ci-dessous.')
                    ->schema([
                Select::make('mentor_assignment_id')
                    ->relationship('mentorAssignment', 'id')
                    ->required(),
                TextInput::make('decided_by_user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('decision')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                DateTimePicker::make('decided_at')
                    ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}

