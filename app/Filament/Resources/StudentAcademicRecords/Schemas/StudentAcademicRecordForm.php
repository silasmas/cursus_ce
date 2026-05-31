<?php

namespace App\Filament\Resources\StudentAcademicRecords\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentAcademicRecordForm
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
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Textarea::make('summary')
                    ->columnSpanFull(),
                TextInput::make('final_average')
                    ->numeric(),
                DateTimePicker::make('validated_at'),
                    ])
                    ->columns(2),
            ]);
    }
}

