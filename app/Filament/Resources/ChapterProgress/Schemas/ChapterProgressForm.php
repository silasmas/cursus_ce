<?php

namespace App\Filament\Resources\ChapterProgress\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ChapterProgressForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Renseignez les champs ci-dessous.')
                    ->schema([
                Select::make('enrollment_id')
                    ->relationship('enrollment', 'id')
                    ->required(),
                Select::make('chapter_id')
                    ->relationship('chapter', 'title')
                    ->required(),
                Select::make('last_content_block_id')
                    ->relationship('lastContentBlock', 'title'),
                DateTimePicker::make('completed_at'),
                    ])
                    ->columns(2),
            ]);
    }
}

