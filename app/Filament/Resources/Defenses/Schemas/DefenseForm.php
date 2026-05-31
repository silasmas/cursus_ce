<?php

namespace App\Filament\Resources\Defenses\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DefenseForm
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
                TextInput::make('student_user_id')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('scheduled_at'),
                TextInput::make('mode'),
                TextInput::make('grade')
                    ->numeric(),
                Textarea::make('comments')
                    ->columnSpanFull(),
                Textarea::make('jury_user_ids')
                    ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}

