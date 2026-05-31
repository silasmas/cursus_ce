<?php

namespace App\Filament\Resources\MentoringReports\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MentoringReportForm
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
                Select::make('chapter_id')
                    ->relationship('chapter', 'title'),
                TextInput::make('report_kind')
                    ->required(),
                Select::make('author_id')
                    ->relationship('author', 'name')
                    ->required(),
                Textarea::make('body')
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('submitted_at'),
                    ])
                    ->columns(2),
            ]);
    }
}

