<?php

namespace App\Filament\Resources\PrayerSessions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PrayerSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Renseignez les champs ci-dessous.')
                    ->schema([
                Select::make('mentor_assignment_id')
                    ->relationship('mentorAssignment', 'id'),
                Select::make('learning_group_id')
                    ->relationship('learningGroup', 'name'),
                TextInput::make('title'),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('ends_at'),
                TextInput::make('meeting_url')
                    ->url(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}

