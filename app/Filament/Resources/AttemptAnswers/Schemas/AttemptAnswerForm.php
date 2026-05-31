<?php

namespace App\Filament\Resources\AttemptAnswers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AttemptAnswerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Renseignez les champs ci-dessous.')
                    ->schema([
                Select::make('assessment_attempt_id')
                    ->relationship('assessmentAttempt', 'id')
                    ->required(),
                Select::make('question_id')
                    ->relationship('question', 'id')
                    ->required(),
                Textarea::make('answer_text')
                    ->columnSpanFull(),
                Select::make('question_option_id')
                    ->relationship('questionOption', 'id'),
                TextInput::make('file_path'),
                TextInput::make('points_awarded')
                    ->numeric(),
                Textarea::make('grader_feedback')
                    ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}

