<?php

namespace App\Filament\Resources\ContentBlockProgress\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContentBlockProgressForm
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
                Select::make('content_block_id')
                    ->relationship('contentBlock', 'title')
                    ->required(),
                DateTimePicker::make('completed_at'),
                    ])
                    ->columns(2),
            ]);
    }
}

